<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Expired Reminder')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;600;700&display=swap" rel="stylesheet"/>

    @stack('styles')
</head>
<body class="bg-surface min-h-screen flex items-center justify-center px-margin-mobile font-sans antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
