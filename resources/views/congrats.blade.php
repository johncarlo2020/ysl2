<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>YSL</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>

<body class="welcome main main-bg">
    <style>
        @font-face {
            font-family: 'Singulier';
            src: url('../fonts/Singulier-Regular.woff2') format('woff2'),
                url('../fonts/Singulier-Regular.woff') format('woff'),
                url('../fonts/Singulier-Regular.otf') format('opentype');
            font-style: normal;
        }

        body {
            font-family: "Singulier", sans-serif;
            font-size: 16px;
            padding: 0;
            margin: 0;
            height: 100%;
            overflow: hidden;
            padding-bottom: calc(2rem + env(safe-area-inset-bottom));
        }
        .main-bg {
            background-image: url("{{ asset('images/new/Background.webp') }}");
        }

        p {
            margin: 0;
        }

        .main-logo {
            width: 34%;
            height: auto;
        }

        .logo {
            height: 10vh !important;
        }

        .tag-line {
            font-size: 16px;
            line-height: 20px;
            font-weight: 400;
            color: #3c727a;
            letter-spacing: 1px;
        }

        .congrats {
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: center;
            justify-content: center;
            align-items: center;
        }

        .white {
            color: black;
        }

        .visit {
            align-self: flex-end;
        }
         .station-born {
            font-family: "Singulier-Bold";
            font-size: 5vw;
            letter-spacing: 1px;
            padding: 0;
            margin: 0;
            margin-bottom: 20vh;
            color: #000;
        }
        .branding .logo{
                width: 60vw;
        }
    </style>
    <div class="congrats">
        <div class="branding-container">@include('components.branding')</div>
        <h1 class="station-born ">UNLEASH YOUR INNER LIGHTS</h1>

        <p>Visit</p>
        <div class="branding">
            <a href="https://www.yslbeauty.com.my/en_MY/eid-2025.html">
                <img class="logo" src="{{ asset('images/logo-dark.webp') }}" alt="">

            </a>
        </div>
        <a class="white" href="https://www.yslbeauty.com.my/en_MY/eid-2025.html">for more information</a>
    </div>
</body>

</html>
