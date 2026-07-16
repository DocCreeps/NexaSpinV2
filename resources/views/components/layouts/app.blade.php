<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>NexaSpin — Tirages au Sort </title>
    <meta name="description" content="Créez des tirages au sort gratuits et instantanés. Utilisez notre roue de la décision classique, par élimination ou tirage pondéré pour animer vos jeux et concours.">
    <meta name="keywords" content="tirage au sort, roue de la decision, choix aleatoire, nexaspin">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="NexaSpin — Tirages au Sort ">
    <meta property="og:description" content="Prenez des décisions ou lancez des concours facilement grâce à nos différents modes de tirages au sort interactifs et gratuits.">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="NexaSpin — Tirages au Sort ">
    <meta name="twitter:description" content="Prenez des décisions ou lancez des concours facilement grâce à nos différents modes de tirages au sort interactifs et gratuits.">
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
