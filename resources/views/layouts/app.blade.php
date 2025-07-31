<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WeFashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="font-playfair antialiased">
    

    {{-- Navbar dipanggil di sini --}}
    @include('components.navbar')

    {{-- Konten utama tiap halaman --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer dipanggil di sini --}}
    @include('components.footer')

</body>
</html>
