<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Redirect extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $table = 'redirect';

    public $fillable = [
        'alias',
        'code',
        'redirect_url',
        'query_params',
        'status_id'
    ];

    protected $casts = [
        'alias' => 'string',
        'code' => 'string',
        'redirect_url' => 'string',
        'query_params' => 'string'
    ];

    public static array $rules = [
        'alias' => 'nullable|string|max:100',
        'redirect_url' => 'required|string|max:65535',
    ];

    public function stats(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Status::class, 'status_id');
    }

    public function redirectLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RedirectLog::class, 'redirect_id');
    }
}
