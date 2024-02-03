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

    public function buscarPorCodigo(string $code)
    {
        $hashId = new Hashids(config('hashid.key'), config('hashid.length'));

        $id = $hashId->decode($code);

        $query = $this->model->newQuery();

        if(empty($id) || empty($id[0])) {
            return null;
        }

        return $query->findOrFail($id[0]);
    }

    public function create(array $input) : Redirect {
        $model = $this->model->newInstance($input);

        $url = filter_var($input['redirect_url'], FILTER_SANITIZE_URL);
        $url = parse_url($url);

        $input = [
            'redirect_url' => $url['scheme'] . "://" . $url['host'] . (!empty($url['path']) ? $url['path'] : ''),
            'query_params' => !empty($url['query']) ? $url['query'] : '',
            'ativo' => 1,
        ];

        $model->fill($input);

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

        if($url['host'] == (config('app.url') . ":" . config('app.app_port') ) || strpos($url['host'], config('app.url')) !== false) {

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

    public function montarUrlFinal(Redirect $redirect, array $queryParams): string {

        $url = $redirect->redirect_url;
        $queriesParamsRequestTratadas = [];
        $queriesParamsRedirect = [];

        foreach($queryParams as $key => $value) {
            if(!empty($value)) {
                $queriesParamsRequestTratadas[$key] = $value;
            }
        }

        if(!empty($redirect->query_params)) {
            $query = explode('&', $redirect->query_params);

            foreach($query as $q) {
                $param = explode('=', $q);

                if(!empty($param[1])) {
                    if(!empty($queriesParamsRequestTratadas[$param[0]])) {
                        continue;
                    }

                    $queriesParamsRedirect[$param[0]] = $param[1];
                }
            }
        }

        if(!empty($queriesParamsRedirect)) {
            $url .= '?' . http_build_query($queriesParamsRedirect);

            if(!empty($queriesParamsRequestTratadas)) {
                $url .= '&' . http_build_query($queriesParamsRequestTratadas);
            }
        }
        elseif(!empty($queriesParamsRequestTratadas)) {
            $url .= '?' . http_build_query($queriesParamsRequestTratadas);
        }

        return $url;
    }

    // Private methods
    private function gerarHashCode(Redirect $redirect): string {

        $code = new Hashids(config('hashid.key'), config('hashid.length'));

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
