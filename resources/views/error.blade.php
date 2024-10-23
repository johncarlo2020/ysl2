<x-app-layout>
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .logo {
            width: 100% !important;
            height: 12vh;
            -o-object-fit: contain;
            object-fit: contain;
        }

        .button-error {
            background-color: #D1A14A !important;
            color: white !important;
            border: none;
            padding: 10px 50px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            letter-spacing: 1px;
            font-weight: 600;
            display: block;
        }
    </style>
    <div id="error-page" class="error-page main main-bg safari-padding">
        <div class="mb-3 branding-container">
            @include('components.branding')
        </div>
        <div class="container mb-5">
            <p>
                Please click the button below to join the journey
            </p>
        </div>
        <div class="container">
            <a class="button-error" href="https://event-sg-ysl-dpcis.cprv.io/?trans_source=950 "> Join
                Now</a>
        </div>
    </div>
    </div>
</x-app-layout>