<?php

namespace App\Mail;

use App\Models\DeployLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeployCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeployLog $deployLog
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->deployLog->status === 'success' ? '成功' : '失敗';
        $projectName = $this->deployLog->project->name;
        
        return new Envelope(
            subject: "【{$projectName}】本番反映が{$status}しました",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deploy-completed',
            with: [
                'deployLog' => $this->deployLog,
                'project' => $this->deployLog->project,
            ],
        );
    }
}
