<x-app-layout>
    <style>
        .rollet-page {
            background-image: url("{{ asset('images/YSL-BG.webp') }}");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;
            transition: 0.9ms;
        }

        .rollet-page #start,
        .rollet-page #continue {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #D1A14A;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 20px;
            border-radius: 10px;
            cursor: pointer;
            width: 72%;
            height: 90px;
            border-radius: 8px !important;
            font-size: 29px;
            font-weight: 500;
        }

        .roulette-container {
            transform: rotate(180deg);

        }

        .roulette-container::before {
            content: "";
            background-image: url("{{ asset('images/rouletArrow.webp') }}");
            width: 5vw;
            height: 149px;
            background-size: contain;
            background-repeat: no-repeat;
            position: absolute;
            z-index: 1;
            bottom: -92px;
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

        .not-selected {
            box-shadow: 0 0 0 2px #d9d1c1b3, 0 0 0 -4px #ffd700;
            border-bottom-color: #AFAFAF99 !important;
        }

        .not-selected::before {
            color: #CFCBC5 !important;
        }

        .roulette::before {
            content: "";
            width: 60px;
            height: 60px;
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
            transition: 0.9s;
            border: 0 solid transparent;
            position: absolute;
            transform-origin: top center;
            top: 50%;
            outline: solid 2px transparent;
            outline-offset: -2px;
            box-shadow: 0 0 0 2px #e1be79, 0 0 0 -4px #ffd700;
        }

        .option::before {
            z-index: 9999 !important;
            position: absolute;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
            margin-bottom: 20px;
            font-size: 70px;
            color: #FFF6E5;
            left: 12px;
            width: 0vw;
            top: 185px;
            rotate: 180deg;
        }

        .option-10::before {
            left: -31px;
            width: 4vw;
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

        .roulette {
            background: radial-gradient(50% 50% at 50% 50%, #F3F0F0 0%, #D1A14A 100%);

        }

        .wrapper {

            display: flex;
            justify-content: center;
            align-items: center;
            width: 900px;
            height: 900px;
            margin: 0 auto;
            border-radius: 50%;
            padding: 10px;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: 50px;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: linear-gradient(45.48deg, #FFFB90 0.65%, #FBE978 14.13%, #F8DC65 23.76%, #E6C758 26.65%, #C49F40 33.39%, #AC832F 39.17%, #9E7225 43.99%, #996C22 47.84%, #9D7125 50.73%, #A98030 54.59%, #BD9A42 58.44%, #D9BE5A 62.29%, #FBE878 67.11%, #FFFFAA 74.81%, #FBE878 80.59%, #A4631B 96.97%);
        }

        .bottom-message {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: #fff;
            font-size: 30px;
            font-weight: 500;
            z-index: 999;

        }

        .bottom-message h1 {
            font-size: 60px;
            margin-bottom: 10px;
            color: #D4A859;
            font-weight: 700;
        }

        .bottom-message h2 {
            font-size: 50px;
            letter-spacing: 2px;
            color:white;
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
            border-radius: 50%;
            margin: 0 auto;
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

        #number {
            background-image: url("{{ asset('images/YSL-Gift.webp') }}");
        }

        #congrats {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 999;
            width: 100%;
        }

        #congrats h1 {
            font-size: 60px;
            margin-bottom: 10px;
            color: #D4A859;
            font-weight: 900;
            letter-spacing: 2px;
        }

        #congrats h2 {
            font-size: 50px;
            letter-spacing: 2px;
            color: #000;
            font-weight: 400;
        }

        #number {
            width: 20vh;
            height: 20vh;
            object-fit: contain;
            background-size: cover;
            margin: 0 auto;
            margin-bottom: 50px;
            position: relative;
        }

        p#winNum {
            font-size: 70px;
            color: #000;
            text-align: center;
            z-index: 9999;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
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
        <div id="rollet" class="wrapper d-none">
            <div class="roulette-container">
                <div class="roulette"></div>
            </div>
        </div>

        <div id="congrats" class="congrats-element d-none">
            <div id="number">
                <p id="winNum"></p>
            </div>
            <h1>CONGRATULATION</h1>
            <h2>YOU HAVE UNLOCKED A GIFT</h2>
        </div>

        <div id="qrImg" class="qr-image d-none">

        </div>

        <button id="start" class="btn discover-btn">START</button>
        <button id="continue" class="btn discover-btn  d-none">DONE</button>
        <button id="end" class="btn discover-btn rounded-pill d-none" onclick="refresh()">END</button>

        <div id="bottomMessage" class="bottom-message d-none">
            <h1>READY</h1>
            <h2>SPIN THE WHEEL</h2>
        </div>
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



        $(document).ready(function() {
            //add event listener to start button
            $('#start').click(function() {
                $('#start').addClass('d-none');
                $('#kity').addClass('d-none');
                $('#spin').removeClass('d-none');
                $('#rollet').removeClass('d-none');
                $('.rollet-page').css('background-image', 'url("{{ asset('images/YSL-BG-clear.webp') }}")');
                $('#bottomMessage').removeClass('d-none');
            });

            //add event listener to continue button
            $('#continue').click(function() {
                $('#continue').addClass('d-none');
                $('#qrImg').removeClass('d-none');
                // $('#end').removeClass('d-none');
            });

            var rouletteSize = 870;
            var numberOfSlots = {{ count($products) }};
            console.log(numberOfSlots); // Get the number of products dynamically
            var slotAngle = 360 / numberOfSlots;
            var degrees = (180 - slotAngle) / 2;
            var slotHeight = Math.tan(degrees * Math.PI / 180) * (rouletteSize / 2);

            var colors = ['#A6D7C0', '#FFF8AD', '#D7C0EF', '#F2A6A6', '#C0D9EF', '#FFC0CB',
                '#A6F0EF'
            ]; // Define the 7 colors // Define the two colors
            var products = @json($products); // Get products as a JavaScript array


            var totalProbability = products.reduce(function(total, product) {
                return total + product.percentage;
            }, 0); // Should be 100%

            var images = [];

            $(".roulette").css({
                'width': rouletteSize + 'px',
                'height': rouletteSize + 'px' // Set the width and height of the roulette
            });

            $('head').append('<style id="afterNumber"></style>');
            products.forEach(function(product, i) {
                var productNumber = i + 1;

                $(".roulette").append('<div class="option option-' + productNumber + '"></div>');
                var classSelector = '.option-' + productNumber;

                $(classSelector).css({
                    'transform': 'rotate(' + slotAngle * productNumber + 'deg)',
                    'border-bottom-color': 'transparent', // Alternate between the two colors
                });

                $('#afterNumber').append('.option-' + productNumber +
                    '::before {content: "' + productNumber +
                    '"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 16px; z-index: 10000;}'
                );

                $('#afterNumber').append('.option-' + productNumber +
                    '::after {content: ""; z-index: 9999 !important; background-image: url("' +
                    images[i] +
                    '");}'); // Use the product's image URL
            });

            $(".option").css({
                'border-bottom-width': slotHeight + 'px',
                'border-right-width': (rouletteSize / 2) + 'px',
                'border-left-width': (rouletteSize / 2) + 'px',
                'width': (rouletteSize / 2) + 'px',
                'height': (rouletteSize / 2) + 'px'
            });


            let isClicked = false;
            function spinRoulette() {
                // Step 1: Define tier percentages (fixed)
                var tierPercentages = {
                    'rare': 10,      // 10%
                    'medium': 30,    // 30%
                    'common': 60     // 60%
                };

                // Step 2: Select tier based on tier percentage
                var tierRandom = Math.random() * 100;
                var selectedTier;

                if (tierRandom < tierPercentages.rare) {
                    selectedTier = 'rare';
                } else if (tierRandom < tierPercentages.rare + tierPercentages.medium) {
                    selectedTier = 'medium';
                } else {
                    selectedTier = 'common';
                }

                console.log('Selected tier: ' + selectedTier + ' (random: ' + tierRandom + ')');

                // Step 3: Filter products by selected tier that have available quantity
                var tierProducts = products.filter(function(product) {
                    return product.tier === selectedTier && product.available > 0;
                });

                // If no products available in selected tier, try other tiers
                if (tierProducts.length === 0) {
                    console.log('No products available in ' + selectedTier + ' tier, trying other tiers');

                    var tierOrder = ['common', 'medium', 'rare'];
                    for (var i = 0; i < tierOrder.length; i++) {
                        if (tierOrder[i] !== selectedTier) {
                            tierProducts = products.filter(function(product) {
                                return product.tier === tierOrder[i] && product.available > 0;
                            });

                            if (tierProducts.length > 0) {
                                selectedTier = tierOrder[i];
                                console.log('Found products in ' + selectedTier + ' tier');
                                break;
                            }
                        }
                    }
                }

                // Step 4: Select product with highest available quantity in the tier
                var selectedProduct = tierProducts.reduce(function(max, product) {
                    return product.available > max.available ? product : max;
                }, tierProducts[0]);

                console.log('Selected product: ' + selectedProduct.name + ' with ' + selectedProduct.available + ' available');

                // Find the index (1-based) in the original products array
                var num = products.findIndex(function(p) {
                    return p.id === selectedProduct.id;
                }) + 1;

                return num;
            }
            // Optionally, you can trigger a real roulette spin to show animation
            $('.roulette').before().click(function() {
                if (isClicked) return;
                isClicked = true;
                var num = spinRoulette();
                selectedProduct = num; // Store the selected product number
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

                $('.roulette').removeAttr('id').attr('id', numID);

                $('#continue').attr('onClick', 'proceed(' + num + ')');

                document.getElementById(numID).addEventListener('animationend', function() {
                    $('#spin').addClass('d-none');
                    $('.option').addClass('not-selected');
                    $('.option-' + num).removeClass('not-selected');
                    $('#bottomMessage').addClass('d-none');

                    setTimeout(() => {
                        done();
                        $('#continue').removeClass('d-none');

                    }, 3000);
                });
            });
        });

        var selectedProduct = 0;

        function proceed(num) {
            console.log(num);
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('stock') }}', // Using Laravel's route() helper function
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the headers
                },
                data: {
                    id: num,
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {}
            });

        }

        function done() {
            $('#congrats').removeClass('d-none');

            $('#rollet').addClass('d-none');

            $('#winNum').text(selectedProduct);

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
                spread: 100,
                startVelocity: 60,
                origin: {
                    y: 0.6
                },
                colors: ['#FFD700', '#FFDF00', '#FFC700'], // Different shades of gold
                shapes: ['square'], // Ribbon-like shapes
                scalar: 3
            });

            // Optional: Remove the canvas after a short delay
            setTimeout(() => {
                document.body.removeChild(confettiCanvas);
            }, 5000);
        }
    </script>
</x-app-layout>
