<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ env("APP_NAME") }}</title>

        @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
            crossorigin="anonymous"
        />
    </head>

    <body class="main admin-login">
        <div class="contaniner wrapper d-flex justify-container-center p-2">
            <div class="branding-container">
                @include('components.branding')
            </div>
            <div
                class="p-4 mx-auto shadow-sm form-container"
            >
                <h2>Sign in to Admin</h2>
                <form
                    method="POST"
                    id="loginForm"
                    action="{{ route('authenticateAdmin') }}"
                >
                    @csrf
                    <div class="mb-4">
                        <label for="exampleInputEmail1" class="form-label"
                            >Email</label
                        >
                        <input
                            placeholder="Enter your email"
                            type="email"
                            name="email"
                            class="form-control"
                            id="exampleInputEmail1"
                            aria-describedby="emailHelp"
                        />
                    </div>
                    <div class="mb-4">
                        <label for="exampleInputPassword1" class="form-label"
                            >Password</label
                        >
                        <input
                            placeholder="Enter your password"
                            type="password"
                            name="password"
                            class="form-control"
                            id="exampleInputPassword1"
                        />
                    </div>
{{--
                    <div class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember" />
                        <label for="remember"> </label>
                        <p>Remember me</p>
                    </div> --}}
                    <button type="submit" class="btn button">Login</button>
                    <p class="mt-3">Dont have an account? <a href="">Create One</a></p>
                </form>
            </div>
            <p class="copy-text">YSL.com <br> <span>®️ ALL RIGHTS RESERVED BY YSL.
                POWERED BY WOWSOME 2024</span></p>
        </div>
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"
        ></script>
        <script>
            document.addEventListener("DOMContentLoaded", (event) => {
                const emailField =
                    document.getElementById("exampleInputEmail1");
                const passwordField = document.getElementById(
                    "exampleInputPassword1"
                );
                const rememberCheckbox = document.getElementById("remember");

                if (getCookie("email") && getCookie("password")) {
                    emailField.value = getCookie("email");
                    passwordField.value = getCookie("password");
                    rememberCheckbox.checked = true;
                }

                document
                    .getElementById("loginForm")
                    .addEventListener("submit", function (event) {
                        if (rememberCheckbox.checked) {
                            setCookie("email", emailField.value, 30);
                            setCookie("password", passwordField.value, 30);
                        } else {
                            setCookie("email", "", 0);
                            setCookie("password", "", 0);
                        }
                    });

                function setCookie(name, value, days) {
                    const date = new Date();
                    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
                    const expires = "expires=" + date.toUTCString();
                    document.cookie =
                        name + "=" + value + ";" + expires + ";path=/";
                }

                function getCookie(name) {
                    const nameEQ = name + "=";
                    const ca = document.cookie.split(";");
                    for (let i = 0; i < ca.length; i++) {
                        let c = ca[i];
                        while (c.charAt(0) == " ") c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) == 0)
                            return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                }
            });
        </script>
    </body>
</html>
