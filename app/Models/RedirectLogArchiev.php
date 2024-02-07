<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedirectLogArchiev extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $table = 'redirect_log_archiev';

    public $fillable = [
        'redirect_id',
        'ip_request',
        'user_agent_request',
        'header_referer_request',
        'query_param_request',
        'access_at'
    ];

    protected $casts = [
        'ip_request' => 'string',
        'user_agent_request' => 'string',
        'header_referer_request' => 'string',
        'query_param_request' => 'string',
        'access_at' => 'datetime'
    ];

    public static array $rules = [
        'redirect_id' => 'required',
        'ip_request' => 'required|string|max:15',
        'user_agent_request' => 'required|string|max:65535',
        'header_referer_request' => 'required|string|max:65535',
        'query_param_request' => 'required|string|max:65535',
        'access_at' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function redirect(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Redirect::class, 'redirect_id');
    }
}
