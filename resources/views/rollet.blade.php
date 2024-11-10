<x-app-layout>
    <style>
        .rollet-page {
            background-image: url("{{ asset('images/wheel-bg.png') }}");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;


        }

        .roulette-container {
            transform: rotate(180deg);
        }

        .roulette-container::before {
            content: "";
            background-image: url("{{ asset('images/rouletArrow.png') }}");
            width: 5vw;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            position: absolute;
            z-index: 1;
            bottom: -20px;
            left: 50%;
            transform: translate(-50%) rotate(180deg);
            pointer-events: none;
        }

        .roulette {
            border-radius: 50%;
            position: relative;
            overflow: hidden;

            -webkit-animation-timing-function: cubic-bezier(0, 0.4, 0.4, 1.04);
            animation-timing-function: cubic-bezier(0, 0.4, 0.4, 1.04);
            -webkit-animation-duration: 5.8s;
            animation-duration: 5.8s;
            -webkit-animation-fill-mode: forwards;
            animation-fill-mode: forwards;
            -webkit-animation-iteration-count: 1;
            animation-iteration-count: 1;
        }

        .roulette::before {
            content: "";
            width: 30px;
            height: 30px;
            background-color: #fff;
            /* outline: 5px solid #ABE0F9; */
            position: absolute;
            z-index: 2;
            border-radius: 360px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
        }

        .option {
            border: 0 solid transparent;
            position: absolute;
            transform-origin: top center;
            top: 50%;
        }

        .option::before {
            z-index: 10;
            position: absolute;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
            margin-bottom: 20px;
            content: '';
            left: -25px;
            width: 6vw;
            height: 36vw;
        }

        /* .option-1::before {
            left: -13px;
            width: 4vw;
        }

        .option-3::before {
            left: -13px;
            width: 4vw;
        }


        .option-4::before {
            left: -45px;
            width: 11vw;
        } */

        .centered-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            top: 0;
            left: -20px;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 550px;
            height: 550px;
            margin: 0 auto;
            background-color: #ABE0F9;
            border-radius: 50%;
            padding: 10px;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .dot {
            position: absolute;
            width: 12px;
            height: 12px;
            background-color: #fff;
            border-radius: 50%;
        }

        .dot:nth-child(1) {
            transform: rotate(0deg) translate(143px);
        }

        .dot:nth-child(2) {
            transform: rotate(45deg) translate(143px);
        }

        .dot:nth-child(3) {
            transform: rotate(90deg) translate(143px);
        }

        .dot:nth-child(4) {
            transform: rotate(135deg) translate(143px);
        }

        .dot:nth-child(5) {
            transform: rotate(180deg) translate(143px);
        }

        .dot:nth-child(6) {
            transform: rotate(225deg) translate(143px);
        }

        .dot:nth-child(7) {
            transform: rotate(270deg) translate(143px);
        }

        .dot:nth-child(8) {
            transform: rotate(315deg) translate(143px);
        }

        .roulette {
            margin: 0 auto;
            border: 4px solid #FFF8AD;
        }

        .option-image-container {
            width: 20%;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .option-image-container .option-image {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
    </style>
    <!-- Modal -->
    <div class="modal fade rollet-modal" id="scanCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center content">
                        <div class="image-check">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="text-content">
                            <img class="check" id="badge" src="">
                            <p class="message">
                                Are you sure to redeem this gift under Wowsome Malaysia?
                            </p>
                        </div>
                        <div class="rolet-btn-container ">
                            <button type="button" class="btn btn-primary bypassLogo rounded rounded-pill w-75"
                                id="confirmYes">Yes</button>
                            <button type="button" class="btn btn-secondary rounded rounded-pill w-75"
                                data-dismiss="modal">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rolet rollet-page">
        <div class="ipad-branding">
            <img src="{{ asset('images/ipad-logo.png') }}" alt="ipad">
            <img src="{{ asset('images/hello-kity-ipad.png') }}" onclick="logo()" alt="branding">
        </div>
        <img id="kity" class="hello-kity-ipad" src="{{ asset('images/hellokity.png') }}" alt="branding">

        <div id="rollet" class="wrapper d-none">
            @for ($i = 0; $i < 8; $i++) <div class="dot">
        </div>
        @endfor
        <div class="roulette-container">
            <div class="roulette"></div>
        </div>
    </div>

    <div id="congrats" class="congrats-element d-none">
        <img class="backdrop" src="{{ asset('images/circle.png') }}" alt="congrats">
        <img class="price" src="" alt="congrats">
    </div>

    <div id="qrImg" class="qr-image d-none">

    </div>

    <button id="start" class="btn discover-btn rounded-pill">START</button>
    <button id="continue" class="btn discover-btn rounded-pill d-none">CONTINUE</button>
    <button id="end" class="btn discover-btn rounded-pill d-none" onclick="refresh()">END</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <!-- Ensure Bootstrap JS is included -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

    <script>
        function refresh() {
            location.reload();
        }

        function logo() {
            $('#scanCompleteModal').modal('show');

        }



        $(document).ready(function () {


            //add event listener to start button
            $('#start').click(function () {
                $('#start').addClass('d-none');
                $('#kity').addClass('d-none');
                $('#spin').removeClass('d-none');
                $('#rollet').removeClass('d-none');
            });

            //add event listener to continue button
            $('#continue').click(function () {
                $('#congrats').addClass('d-none');
                $('#continue').addClass('d-none');
                $('#qrImg').removeClass('d-none');
                $('#end').removeClass('d-none');
            });

            var rouletteSize = 500
            var numberOfSlots = {{ count($products)
        }};
        console.log(numberOfSlots); // Get the number of products dynamically
        var slotAngle = 360 / numberOfSlots;
        var degrees = (180 - slotAngle) / 2;
        var slotHeight = Math.tan(degrees * Math.PI / 180) * (rouletteSize / 2);

        var colors = ['#A6D7C0', '#FFF8AD', '#D7C0EF', '#F2A6A6', '#C0D9EF', '#FFC0CB', '#A6F0EF']; // Define the 7 colors // Define the two colors
        var products = @json($products); // Get products as a JavaScript array


        var totalProbability = products.reduce(function (total, product) {
            return total + product.percentage;
        }, 0); // Should be 100%

        var images = [];

        for (var i = 1; i <= numberOfSlots; i++) {
            images.push('{{ asset('images') }}/Product' + i + '.webp');
        }

        $(".roulette").css({
            'width': rouletteSize + 'px',
            'height': rouletteSize + 'px'
        });

        $('head').append('<style id="afterNumber"></style>');

        products.forEach(function (product, i) {
            var productNumber = i + 1;

            $(".roulette").append('<div class="option option-' + productNumber + '"></div>');
            var classSelector = '.option-' + productNumber;

            $(classSelector).css({
                'transform': 'rotate(' + slotAngle * productNumber + 'deg)',
                'border-bottom-color': '#D1A14A', // Alternate between the two colors
            });

            $('#afterNumber').append('.option-' + productNumber +
                '::before {content: ""; z-index: 9999 !important; background-image: url("' +
                images[i] +
                '");}'); // Use the product's image URL
        });

        $(".option").css({
            'border-bottom-width': slotHeight + 'px',
            'border-right-width': (rouletteSize / 2) + 'px',
            'border-left-width': (rouletteSize / 2) + 'px'
        });

        function spinRoulette() {
            var num;
            var random = Math.random() * totalProbability; // Get a random number between 0 and 100
            var cumulativeProbability = 0;

            // Find the selected product based on its probability range
            for (var i = 0; i < products.length; i++) {
                cumulativeProbability += products[i].percentage;
                if (random <= cumulativeProbability) {
                    num = i + 1; // Product number is 1-based
                    break;
                }
            }
            return num; // Return the selected product number
        }
        // Optionally, you can trigger a real roulette spin to show animation
        $('.roulette').before().click(function () {
            var num = spinRoulette();
            var numID = 'number-' + num;

            $('#rouletteAnimation').remove();
            $('head').append('<style id="rouletteAnimation">' +
                '#number-' + num + ' { ' +
                'animation-name: number-' + num + '; ' +
                'animation-duration: 3s; ' +
                'animation-timing-function: cubic-bezier(0.1, 0.7, 0.1, 1); ' +
                '} ' +
                '@keyframes number-' + num + ' {' +
                'from { transform: rotate(0); } ' +
                'to { transform: rotate(' + (360 * (numberOfSlots - 1) - slotAngle * num) +
                'deg); }' +
                '}' +
                '</style>'
            );
            var newNum = num + 4;

            $('.price').attr('src', '{{ asset('images') }}' + '/Product' + num + '.webp');
            let qrUrl = `{{ url('error') }}?product=${newNum}&station=4`;


            console.log(num);

            $('.bypassLogo').attr('onClick', 'bypass(' + newNum + ')');

            $('.roulette').removeAttr('id').attr('id', numID);

            document.getElementById(numID).addEventListener('animationend', function () {
                $('#spin').addClass('d-none');
                $('#rollet').addClass('d-none');
                $('#congrats').removeClass('d-none');
                $('#continue').removeClass('d-none');
                const confettiCanvas = document.createElement('canvas');
                confettiCanvas.style.position = 'fixed';
                confettiCanvas.style.top = 0;
                confettiCanvas.style.left = 0;
                confettiCanvas.style.width = '100%';
                confettiCanvas.style.height = '100%';
                confettiCanvas.style.pointerEvents = 'none';
                confettiCanvas.style.zIndex = 9999;
                document.body.appendChild(confettiCanvas);

                // Trigger confetti using the new canvas
                const myConfetti = confetti.create(confettiCanvas, {
                    resize: true,
                    useWorker: true
                });

                myConfetti({
                    particleCount: 100,
                    spread: 70,
                    origin: {
                        y: 0.6
                    }
                });

                // Optional: Remove the canvas after a short delay
                setTimeout(() => {
                    document.body.removeChild(confettiCanvas);
                }, 5000);
            });
        });
        });
    </script>
</x-app-layout>