 <x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
    <style>
        .welcome-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px 30px;
            max-width: 340px;
            width: 90%;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-title {
            font-size: 24px;
            font-weight: 600;
            color: #000;
            text-align: center;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .welcome-text {
            font-size: 14px;
            color: #333;
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 20px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            font-size: 15px;
            background-color: #fff;
            color: #333;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #999;
        }

        /* intl-tel-input styling */
        .iti {
            width: 100%;
            margin-bottom: 20px;
        }

        .iti__flag-container {
            border-right: 1px solid #d1d1d1;
        }

        #number {
            width: 100%;
            padding: 14px 16px 14px 52px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            font-size: 15px;
            background-color: #fff;
            color: #333;
            box-sizing: border-box;
        }

        #number:focus {
            outline: none;
            border-color: #999;
        }

        .button-submit {
            background-color: #000000 !important;
            color: #fff !important;
            border: none;
            padding: 14px 30px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            letter-spacing: 0.5px;
            display: block;
            width: 100%;
            font-weight: 500;
        }

        .button-submit:hover {
            background-color: #1a1a1a !important;
        }

        .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: -15px;
            margin-bottom: 15px;
            text-align: left;
        }

        #page-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.75);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #page-loading-overlay .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #d1d1d1;
            border-top-color: #000;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <div id="page-loading-overlay"><div class="spinner"></div></div>
    <div id="stationPage" class="station-page main main-bg safari-padding">
        <div class="mb-3 branding-container">
            @include('components.branding')
        </div>
        <div id="mainContent" class="text-center text-content">
            <div class="content">
                <div class="welcome-card">
                    <h1 class="welcome-title">WELCOME!</h1>
                    <p class="welcome-text">Please fill in your mobile number below.</p>
                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf
                        <input id="number" type="phone" class="input-text form-control w-100 @error('number') is-invalid @enderror"
                            name="number" value="{{ old('number') }}" required autocomplete="number" autofocus disabled />

                        <input type="hidden" id="dialCode" name="dial_code" value="">
                        <input type="hidden" id="countryIso" name="country_iso" value="">
                        <input type="hidden" id="code" name="code" value="">

                        <span id="error-msg" class="error-message d-none"></span>
                        <span id="valid-msg" class="d-none" style="color: green; font-size: 12px;">✓ Valid number</span>

                        @if($errors->has('code'))
                            <div class="error-message">{{ $errors->first('code') }}</div>
                        @endif

                        <input class="d-none" type="hidden" name="password" value="password" />

                        <button class="button-submit" id="submitButton" type="button" disabled>Start Your Journey Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>

    <script>
        window.addEventListener("load", function() {
            document.getElementById("page-loading-overlay").style.display = "none";
            document.getElementById("number").disabled = false;
            document.getElementById("submitButton").disabled = false;
        });

        document.addEventListener("DOMContentLoaded", function() {

               const form = document.querySelector("#form");
            const input = document.querySelector("#number");
            const errorMsg = document.querySelector("#error-msg");
            const validMsg = document.querySelector("#valid-msg");
            const dialInput = document.querySelector("#dialCode");
            const isoInput = document.querySelector("#countryIso");
            const errorMap = [
                "Invalid number",
                "Invalid country code",
                "Too short",
                "Too long",
                "Invalid number",
            ];
            const submitButton = document.querySelector("#submitButton");
            const iti = window.intlTelInput(input, {
                initialCountry: "sg",
                preferredCountries: ["sg"],
                hiddenInput: "country",
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
            });

            const reset = () => {
                input.classList.remove("error");
                errorMsg.innerHTML = "";
                errorMsg.classList.add("d-none");
                validMsg.classList.add("d-none");
            };

            const showError = (msg) => {
                input.classList.add("error");
                errorMsg.innerHTML = msg;
                errorMsg.classList.remove("d-none");
            };

            input.addEventListener("keyup", function () {
                reset();
                if (!input.value.trim()) {
                    showError("Required");
                    submitButton.disabled = true;
                } else if (iti.isValidNumber()) {
                    validMsg.classList.remove("d-none");
                    submitButton.disabled = false;
                } else {
                    const errorCode = iti.getValidationError();
                    const msg = errorMap[errorCode] || "Invalid number";
                    showError(msg);
                    submitButton.disabled = true;
                }
            });

            // Function to update dial code in the div
            function updateCountryData() {
                const countryData = iti.getSelectedCountryData();
                dialInput.value = "+" + countryData.dialCode;  // e.g., +1
                isoInput.value = countryData.iso2;             // e.g., us, my, ca
                console.log("Dial code:", dialInput.value, "ISO:", isoInput.value);
                submitButton.disabled = false;
            }

            // Set initial default dial code on page load
            updateCountryData();

            // Update dial code whenever country changes
            input.addEventListener("countrychange", updateCountryData);

            input.addEventListener("keypress", function (e) {
                const char = String.fromCharCode(e.which);
                if (!/[0-9+]/.test(char)) {
                    e.preventDefault();
                }
            });

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Check if there's an 'id' parameter in the URL for backward compatibility
            const urlParams = new URLSearchParams(window.location.search);
            const urlId = urlParams.get("id");

            if (urlId) {
                // If ID is in URL, auto-fill and submit (old behavior)
                $("#code").val(urlId);
                processRegistration(urlId);
            }

            // Handle form submission
            $("#submitButton").click(function(e) {
                e.preventDefault();

                if (!iti.isValidNumber()) {
                    alert("Please enter a valid phone number");
                    return;
                }

                const fullNumber = iti.getNumber();
                $("#code").val(fullNumber);

                processRegistration(fullNumber);
            });

            function processRegistration(code) {
                $.ajax({
                    url: '{{ route('checkExisting') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: {
                        code: code,
                    },
                    success: function(response) {
                        if (response == 1) {
                            $("#registerForm").attr("action", "{{ route('login') }}");
                            $("#registerForm").submit();
                        } else {
                            $("#registerForm").submit();
                        }
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred. Please try again.");
                        console.error(error);
                    }
                });
            }
        });
    </script>
</x-app-layout>
