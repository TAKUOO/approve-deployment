<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>本番反映完了通知</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-success {
            background: #10b981;
            color: white;
        }
        .status-failed {
            background: #ef4444;
            color: white;
        }
        .info-box {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>本番反映完了通知</h1>
    </div>
    
    <div class="content">
        <p>こんにちは、</p>
        
        <p><strong>{{ $project->name }}</strong> の本番反映が完了しました。</p>
        
        <div class="status-badge {{ $deployLog->status === 'success' ? 'status-success' : 'status-failed' }}">
            {{ $deployLog->status === 'success' ? '✅ 成功' : '❌ 失敗' }}
        </div>
        
        <div class="info-box">
            <p><strong>プロジェクト名:</strong> {{ $project->name }}</p>
            <p><strong>開始時刻:</strong> {{ $deployLog->started_at->format('Y年m月d日 H:i') }}</p>
            @if($deployLog->finished_at)
                <p><strong>完了時刻:</strong> {{ $deployLog->finished_at->format('Y年m月d日 H:i') }}</p>
                <p><strong>所要時間:</strong> {{ $deployLog->started_at->diffForHumans($deployLog->finished_at, true) }}</p>
            @endif
            @if($deployLog->approvalMessage)
                <p><strong>承認メッセージ:</strong></p>
                <p style="white-space: pre-wrap; background: #f3f4f6; padding: 10px; border-radius: 4px;">{{ $deployLog->approvalMessage->message }}</p>
            @endif
        </div>
        
        <div class="footer">
            <p>このメールは AutoRelease から自動送信されました。</p>
            <p>ご不明な点がございましたら、お問い合わせください。</p>
        </div>
    </div>
</body>
</html>
