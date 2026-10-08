<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Admin sign in · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.svg') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap" rel="stylesheet" />
    <link href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />
    <style>
        body { margin: 0; background: #f8f9fa; color: #344767; font-family: 'Open Sans', sans-serif; }
        .admin-signin { min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; position: relative; isolation: isolate; }
        .admin-signin::before { content: ''; position: absolute; inset: 0 0 auto; height: 36%; min-height: 220px; background: #5e72e4; z-index: -1; }
        .signin-card { width: 100%; max-width: 440px; padding: 36px; background: #fff; border: 1px solid #edf0f5; border-radius: 20px; box-shadow: 0 16px 48px #34476714; }
        .signin-logo { display: block; max-width: 150px; height: 54px; object-fit: contain; margin: 0 auto 28px; }
        .signin-heading { text-align: center; margin-bottom: 28px; }
        .signin-heading h1 { font-size: 24px; font-weight: 700; margin: 0 0 10px; color: #344767; }
        .signin-heading p { font-size: 13px; line-height: 1.6; color: #8392ab; margin: 0; }
        .signin-field { margin-bottom: 20px; }
        .signin-field label { display: block; margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #344767; }
        .signin-field .form-control { border: 1px solid #dfe5ee; border-radius: 10px; padding: 13px 14px; background: #f8fafc; color: #344767; font-size: 14px; }
        .signin-field .form-control:focus { border-color: #5e72e4; box-shadow: 0 0 0 3px #5e72e415; background: #fff; }
        .signin-field .form-control::placeholder { color: #8392ab; }
        .signin-submit { width: 100%; margin: 8px 0 0; padding: 14px 18px; border: 0; border-radius: 10px; background: #5e72e4; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .2s; }
        .signin-submit:hover { background: #4c60d2; }
        .signin-submit:focus-visible { outline: 3px solid #b6bfff; outline-offset: 3px; }
        .signin-error { padding: 12px 14px; margin-bottom: 22px; border: 1px solid #f5c9cc; border-radius: 10px; background: #fff1f2; color: #b42332; font-size: 13px; line-height: 1.6; }
        .signin-footer { margin: 24px 0 0; text-align: center; color: #8392ab; font-size: 11px; line-height: 1.8; }
        @media (max-width: 480px) { .admin-signin { padding: 28px 16px; } .signin-card { padding: 28px 24px; } }
    </style>
</head>
<body>
    <main class="admin-signin">
        <section class="signin-card" aria-labelledby="signin-title">
            <img class="signin-logo" src="{{ asset('images/logo2.png') }}" alt="YSL" />
            <div class="signin-heading">
                <h1 id="signin-title">Admin sign in</h1>
                <p>Welcome back. Sign in to manage users and station activity.</p>
            </div>
            @if ($errors->any())
                <div class="signin-error" id="signin-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" id="loginForm" action="{{ route('authenticateAdmin') }}">
                @csrf
                <div class="signin-field">
                    <label for="email">Email address</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}"
                        placeholder="Enter your email" required autocomplete="username" autofocus
                        @error('email') aria-invalid="true" aria-describedby="signin-error" @enderror />
                </div>
                <div class="signin-field">
                    <label for="password">Password</label>
                    <input id="password" class="form-control" type="password" name="password"
                        placeholder="Enter your password" required autocomplete="current-password"
                        @error('password') aria-invalid="true" aria-describedby="signin-error" @enderror />
                </div>
                <button type="submit" class="signin-submit">Sign in</button>
            </form>
        </section>
        <footer class="signin-footer">&copy; {{ now()->year }} YSL. All rights reserved.<br>Powered by Wowsome</footer>
    </main>
</body>
</html>
