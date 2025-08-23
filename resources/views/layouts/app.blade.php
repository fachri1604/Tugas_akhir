<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WeFashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite('resources/css/app.css')
</head>

{{-- Jadikan halaman full-height & kolom --}}
<body class="min-h-screen flex flex-col font-playfair antialiased">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Konten utama mendorong footer ke bawah --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer (jangan fixed/absolute) --}}
    <footer class="mt-auto">
        @include('components.footer')
    </footer>

</body>
</html>
