<?php

namespace App\Repositories;

use App\Models\RedirectLog;
use App\Repositories\BaseRepository;

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
}
