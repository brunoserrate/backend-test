<?php

namespace App\Repositories;

use App\Models\Redirect;
use App\Repositories\BaseRepository;

class RedirectRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'alias',
        'code',
        'redirect_url',
        'query_params',
        'stats_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Redirect::class;
    }
}
