<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeployLog extends Model
{
    protected $fillable = [
        'project_id',
        'status',
        'github_run_id',
        'started_at',
        'finished_at',
        'raw_log',
        'approval_message_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvalMessage(): BelongsTo
    {
        return $this->belongsTo(ApprovalMessage::class);
    }
}
