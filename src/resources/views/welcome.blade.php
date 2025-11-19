<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'CMS Đoàn Hội') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            overflow-x: hidden;
        }

        .banner-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        .banner-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .login-button-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .login-button {
            display: inline-block;
            padding: 16px 48px;
            background-color: #F53003;
            color: white;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .login-button:hover {
            background-color: #d42800;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
        }

        @media (max-width: 768px) {
            .login-button {
                padding: 14px 36px;
                font-size: 16px;
            }
        }
    </style>
    @endif
</head>

<body>
    <div class="banner-container">
        <img src="{{ asset('banner.jpg') }}" alt="Banner" class="banner-image">
        <div class="login-button-container">
            <a href="{{ url('/admin/login') }}" class="login-button">
                Đăng nhập
            </a>
        </div>
    </div>
</body>

</html>