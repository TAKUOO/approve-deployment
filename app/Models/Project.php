<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'staging_url',
        'production_url',
        'approve_token',
        'github_owner',
        'github_repo',
        'github_workflow_id',
        'github_branch',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deployLogs(): HasMany
    {
        return $this->hasMany(DeployLog::class);
    }
}
