<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

use App\Models\Redirect;
use App\Models\RedirectLog;
use App\Repositories\BaseRepository;

use Hashids\Hashids;

class RedirectRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'alias',
        'code',
        'redirect_url',
        'query_params',
        'status_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Redirect::class;
    }

    public function all(array $search = [], int $skip = null, int $limit = null, array $columns = ['*']): Collection
    {
        $query = $this->allQuery($search, $skip, $limit);

        $query->select(
            'redirect.alias',
            'redirect.code',
            'redirect.redirect_url',
            'redirect.query_params',
            'redirect.status_id',
            'status.name as status',
            'redirect.created_at',
            'redirect.updated_at'
        )
        ->addSelect([
            'last_access' => RedirectLog::select('created_at')
                ->whereColumn('redirect_id', 'redirect.id')
                ->orderBy('created_at', 'desc')
                ->limit(1)
        ])
        ->leftJoin('status', 'status.id', '=', 'redirect.status_id');

        return $query->get();
    }
    public function create(array $input) : Redirect {
        $model = $this->model->newInstance($input);

        $model->save();

        $model->code = $this->gerarHashCode($model);

        $model->save();

        unset($model->id);

        return $model;
    }

    public function update(array $input, int $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $input = [
            'redirect_url' => $input['redirect_url'],
            'ativo' => $input['ativo']
        ];

        $model->fill($input);

        $model->save();

        return $model;
    }

    public function verificarUrl(string $url): array {

        $url = filter_var($url, FILTER_SANITIZE_URL);

        if(filter_var($url, FILTER_VALIDATE_URL) === false) {
            return [
                'success' => false,
                'message' => 'URL inválida',
                'code' => 422
            ];
        }

        $url = parse_url($url);

        if(empty($url['host']) || !$this->verificarDNS($url['host'])) {
            return [
                'success' => false,
                'message' => 'DNS inválido. Host não localizado',
                'code' => 422
            ];
        }

        if($url['host'] == (config('app.app_url') . ":" . config('app.app_port') ) || strpos($url['host'], config('app.app_port')) !== false) {

            return [
                'success' => false,
                'message' => 'URL inválida. Mesma URL da aplicação',
                'code' => 422
            ];
        }

        if(strpos($url['host'], 'localhost') !== false) {
            return [
                'success' => false,
                'message' => 'URL inválida. Localhost não permitido',
                'code' => 422
            ];
        }

        if(empty($url['scheme']) || $url['scheme'] != 'https') {
            return [
                'success' => false,
                'message' => 'URL inválida. HTTPS obrigatorio',
                'code' => 422
            ];
        }

        $urlFull = $url['scheme'] . "://" . $url['host'] . (!empty($url['path']) ? $url['path'] : '') . (!empty($url['query']) ? '?' . $url['query'] : '');

        if($this->verificaStatusUrl($urlFull) ) {
            return [
                'success' => false,
                'message' => 'URL inválida. Status diferente de 200/201',
                'code' => 422
            ];
        }

        if(!empty($url['query'])) {
            $query = explode('&', $url['query']);

            foreach($query as $q) {
                $param = explode('=', $q);

                if(empty($param[1])) {
                    return [
                        'success' => false,
                        'message' => 'URL inválida. Query param com chave vazia',
                        'code' => 422
                    ];
                }
            }
        }

        return [
            'success' => true
        ];
    }

    // Private methods
    private function gerarHashCode(Redirect $redirect): string {

        $code = new Hashids(env('HASH_ID_KEY'), 6);

        return $code->encode($redirect->id);
    }

    private function verificarDNS(string $host): bool {

        $dns = str_replace('www.', '', $host);

        if(checkdnsrr($dns)) {
            return true;
        }

        return false;
    }

    private function verificaStatusUrl(string $url): bool {

        $header = get_headers($url);

        $status = explode(' ', $header[0]);

        $status = (int) $status[1];

        if($status != 200 && $status != 201) {
            return true;
        }

        return false;
    }
}
