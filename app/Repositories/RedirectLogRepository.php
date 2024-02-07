<?php

namespace App\Repositories;

use App\Models\RedirectLog;
use App\Repositories\BaseRepository;

use App\Models\RedirectLogArchiev;

class RedirectLogRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'redirect_id',
        'ip_request',
        'user_agent_request',
        'header_referer_request',
        'query_param_request',
        'access_at'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return RedirectLog::class;
    }

    public function tratarRequest($request)
    {
        $clientIp = request()->getClientIp();
        $userAgent = request()->header('User-Agent');
        $referer = request()->header('referer');
        $queryParams = request()->query();

        return [
            'clientIp' => $clientIp,
            'userAgent' => $userAgent,
            'referer' => $referer,
            'queryParams' => $queryParams
        ];
    }

    public function gerarEstaticas($id){
        $logs = $this->model->where('redirect_id', $id)->get();

        $totalAcessos = $logs->count();
        $totalAcessosUnicos = $logs->unique('ip_request')->count();

        $topReferrers = $logs->groupBy('header_referer_request')->map(function($item, $key){
            return [
                'referer' => $key,
                'total' => $item->count()
            ];
        })->sortByDesc('total')->values()->take(10);

        $acessadosUltimosDias = $logs->filter(function($item){
            return $item->access_at->diffInDays(now()) < 10;
        })->groupBy(function($item){
            return $item->access_at->format('Y-m-d');
        })->map(function($item, $key){
            return [
                'date' => $key,
                'total' => $item->count(),
                'unique' => $item->unique('ip_request')->count()
            ];
        })->sortByDesc('total')->values()->take(-10);

        return [
            'total_acessos' => $totalAcessos,
            'total_acessos_unicos' => $totalAcessosUnicos,
            'top_referrers' => $topReferrers,
            'acessados_ultimos_dias' => $acessadosUltimosDias
        ];
    }

    /**
     * Gera estatísticas utilizando logs arquivados
     *
     * @param $id
     * @return mixed
     */
    public function gerarEstatiscasCompletas($id){

        $logs = $this->model->where('redirect_id', $id)->get();
        $logsArchiev = RedirectLogArchiev::where('redirect_id', $id)->get();

        $logs = $logs->merge($logsArchiev);

        $totalAcessos = $logs->count();
        $totalAcessosUnicos = $logs->unique('ip_request')->count();

        $topReferrers = $logs->groupBy('header_referer_request')->map(function($item, $key){
            return [
                'referer' => $key,
                'total' => $item->count()
            ];
        })->sortByDesc('total')->values()->take(10);

        $acessadosUltimosDias = $logs->filter(function($item){
            return $item->access_at->diffInDays(now()) < 10;
        })->groupBy(function($item){
            return $item->access_at->format('Y-m-d');
        })->map(function($item, $key){
            return [
                'date' => $key,
                'total' => $item->count(),
                'unique' => $item->unique('ip_request')->count()
            ];
        })->sortByDesc('total')->values()->take(-10);

        return [
            'total_acessos' => $totalAcessos,
            'total_acessos_unicos' => $totalAcessosUnicos,
            'top_referrers' => $topReferrers,
            'acessados_ultimos_dias' => $acessadosUltimosDias
        ];
    }
}
