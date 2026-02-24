<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalComment extends Model
{
    protected $fillable = [
        'approval_message_id',
        'project_id',
        'created_by_user_id',
        'author_name',
        'body',
        'x_ratio',
        'y_ratio',
        'w_ratio',
        'h_ratio',
        'mentions',
    ];

    protected $casts = [
        'mentions' => 'array',
    ];

    public function approvalMessage(): BelongsTo
    {
        return $this->belongsTo(ApprovalMessage::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
