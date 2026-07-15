<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexaSpin — Tirage au sort</title>
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
