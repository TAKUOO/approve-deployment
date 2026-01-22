<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Default OGP Meta Tags -->
        @php
            $appUrl = config('app.url', 'https://autorelease.matsui-dev.net');
            $ogImage = $appUrl . '/images/ogp.jpg';
            $ogTitle = 'WEBデザイナー向けリリース自動化システム - AutoRelease';
            $ogDescription = 'クライアントがテスト環境を確認し、承認ボタンを押すだけで本番環境へ自動的にアップされます。GitHub Actionsと連携した承認→自動デプロイシステム。';
        @endphp
        <meta name="description" content="{{ $ogDescription }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $appUrl }}" />
        <meta property="og:title" content="{{ $ogTitle }}" />
        <meta property="og:description" content="{{ $ogDescription }}" />
        <meta property="og:image" content="{{ $ogImage }}" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:site_name" content="AutoRelease" />
        <meta property="og:locale" content="ja_JP" />
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $ogTitle }}" />
        <meta name="twitter:description" content="{{ $ogDescription }}" />
        <meta name="twitter:image" content="{{ $ogImage }}" />

        <!-- JSON-LD構造化データ -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "AutoRelease",
            "applicationCategory": "DeveloperApplication",
            "operatingSystem": "Web",
            "description": "{{ $ogDescription }}",
            "url": "{{ $appUrl }}",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "JPY",
                "availability": "https://schema.org/InStock"
            }
        }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <!-- フォントの非ブロッキング読み込み（パフォーマンス最適化） -->
        <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet"></noscript>

        <!-- クリティカルCSS（WelcomeページのAbove the fold部分のみ） -->
        @if(request()->is('/'))
        <style>
            /* Above the fold部分のクリティカルCSS（FOUC防止） */
            body{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;margin:0;line-height:1.5;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
            .min-h-screen{min-height:100vh}
            .overflow-hidden{overflow:hidden}
            .relative{position:relative}
            .absolute{position:absolute}
            .inset-0{top:0;right:0;bottom:0;left:0}
            .flex{display:flex}
            .flex-col{flex-direction:column}
            .z-10{z-index:10}
            .bg-gradient-to-br{background-image:linear-gradient(to bottom right,var(--tw-gradient-stops))}
            .from-slate-100{--tw-gradient-from:#f1f5f9;--tw-gradient-to:rgba(241,245,249,0);--tw-gradient-stops:var(--tw-gradient-from),var(--tw-gradient-to)}
            .via-slate-200{--tw-gradient-to:rgba(226,232,240,0);--tw-gradient-stops:var(--tw-gradient-from),#e2e8f0,var(--tw-gradient-to)}
            .to-indigo-200\/50{--tw-gradient-to:rgba(199,210,254,.5)}
            .text-slate-700{color:#334155}
            .pointer-events-none{pointer-events:none}
            .px-4{padding-left:1rem;padding-right:1rem}
            .py-12{padding-top:3rem;padding-bottom:3rem}
            .mx-auto{margin-left:auto;margin-right:auto}
            .max-w-6xl{max-width:72rem}
            .gap-8{gap:2rem}
            .items-center{align-items:center}
            .text-center{text-align:center}
            .text-3xl{font-size:1.875rem;line-height:2.25rem}
            .font-semibold{font-weight:600}
            .text-slate-600{color:#475569}
            .bg-indigo-600{background-color:#4f46e5}
            .text-white{color:#fff}
            .rounded-2xl{border-radius:1rem}
            .px-6{padding-left:1.5rem;padding-right:1.5rem}
            .py-3{padding-top:.75rem;padding-bottom:.75rem}
            .w-full{width:100%}
            .object-contain{object-fit:contain}
            .h-auto{height:auto}
        </style>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
