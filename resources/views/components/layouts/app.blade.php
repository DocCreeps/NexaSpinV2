<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>NexaSpin — Tirage au sort</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">

    <main class="py-10">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
