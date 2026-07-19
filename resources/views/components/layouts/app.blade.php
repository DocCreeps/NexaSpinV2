@props([
'title' => 'NexaSpin — Tirages au Sort & Roues de la Décision Aléatoires',
'metaDescription' => 'Créez des tirages au sort gratuits et instantanés. Utilisez notre roue de la décision classique, par élimination ou tirage pondéré pour animer vos jeux et concours.',
])

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="roue de la fortune, tirage au sort, roue de la decision, choix aleatoire, generateur de nom, nexaspin">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ asset('images/og-card.png') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

</head>
<body class="min-h-full bg-slate-50 text-gray-900 antialiased">

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
