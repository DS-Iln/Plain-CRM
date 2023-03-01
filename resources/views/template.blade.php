<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title')</title>
    {{-- Reset.css --}}
    <link rel="stylesheet" href="/public/assets/css/reset.css">
    {{-- Bootstrap styles --}}
    <link rel="stylesheet" href="/public/assets/css/bootstrap.min.css">
    {{-- Frappe-Datatable styles --}}
    <link rel="stylesheet" href="/public/assets/css/frappe-datatable.min.css">
    {{-- Custom css --}}
    <link rel="stylesheet" href="/public/assets/css/app.css">
</head>
<body>
    <div class="wrapper d-flex flex-column">
        <header class="header bg-dark">
            <div class="container py-3 d-flex justify-content-center">
                <a href="{{ route('main.index') }}" class="btn btn-light me-3">
                    На главную
                </a>
                <a href="{{ route('admin.index') }}" class="btn btn-light">
                    Админ-панель
                </a>
            </div>
        </header>
        <main class="main flex-grow-1 flex-shrink-1 py-4 d-flex flex-column">
            @yield('content')
        </main>
        <footer class="footer flex-grow-0 flex-shrink-0">

        </footer>
    </div>
    @yield('service-container')

    {{-- Bootstrap script --}}
    <script src="/public/assets/js/bootstrap.bundle.min.js" defer></script>
    {{-- FontAwesome icons --}}
    <script src="https://kit.fontawesome.com/9a8ad40ab5.js" crossorigin="anonymous" defer></script>
    @yield('scripts')
</body>
</html>
