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
        @endphp
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $appUrl }}" />
        <meta property="og:title" content="{{ config('app.name', 'AutoRelease') }}" />
        <meta property="og:image" content="{{ $ogImage }}" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:site_name" content="AutoRelease" />
        <meta property="og:locale" content="ja_JP" />
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:image" content="{{ $ogImage }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
