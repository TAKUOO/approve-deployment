<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScreenshotController extends Controller
{
    /**
     * 指定URLのスクリーンショット画像を取得して返す
     * PageShot API を使用（APIキー不要・無料）
     */
    public function __invoke(Request $request)
    {
        $url = $request->query('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        $apiUrl = 'https://pageshot.site/v1/screenshot?'
            . 'url=' . urlencode($url)
            . '&width=400'
            . '&height=300'
            . '&format=webp';

        try {
            $response = Http::timeout(20)->get($apiUrl);
            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type', 'image/webp'))
                    ->header('Cache-Control', 'public, max-age=3600');
            }
        } catch (\Throwable) {
            // fallthrough to 404
        }

        abort(404, 'Screenshot not available');
    }
}
