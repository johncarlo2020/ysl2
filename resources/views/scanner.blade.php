@extends('layouts.admin') @section('content')
<div class="mt-4 row justify-content-center">
    <div class="mb-4 col-lg-8 mb-lg-0">
        <div class="card text-center">
            <div class="p-3 pb-0 card-header">
                <h6 class="mb-2">Barcode Scanner</h6>
            </div>
            <div id="scanner" style="
                    width: 100%;
                    height: 300px;
                    border: 2px dashed #007bff;
                    position: relative;
                    overflow: hidden;
                ">
                <video id="video" style="
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        top: 0;
                        left: 0;
                    "></video>
            </div>
            <div class="mt-3">
                <h4>Scanned Code:</h4>
                <div id="scanned-result" class="font-weight-bold"></div>
            </div>

            <div class="mt-3">
                <label for="manual-code" class="form-label">Manual Code Entry:</label>
                <input type="text" id="manual-code" class="form-control" placeholder="Enter code here"
                    style="max-width: 300px; margin: 0 auto" />
                <button id="add-code" class="btn btn-primary mt-2" style="max-width: 150px">
                    Add Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center">
                <div id="qr-code" style="width: 256px; height: 256px"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QuaggaJS Library -->
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize QuaggaJS
        Quagga.init(
            {
                inputStream: {
                    type: "LiveStream",
                    target: document.querySelector("#scanner"),
                    constraints: {
                        width: { min: 640 },
                        height: { min: 480 },
                        facingMode: "environment",
                    },
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader",
                        "ean_8_reader",
                        "code_39_reader",
                    ],
                },
            },
            function (err) {
                if (err) {
                    console.error("Error during QuaggaJS initialization:", err);
                    return;
                }
                console.log("QuaggaJS initialized successfully.");
                Quagga.start(); // Start the scanner
            }
        );

        // Capture and display the barcode result
        Quagga.onDetected(function (result) {
            var code = result.codeResult.code;

            document.getElementById("scanned-result").innerText = code;

            console.log("Scanned code: " + code);
            // Generate QR code for the dashboard link
            generateQRCode('https://my.libresummerfreedom.com/register?id=' + code);

            // Show the modal
            $("#qrModal").modal("show");

            Quagga.stop(); // Stop the scanner after detecting a barcode
        });
    });

    $('#add-code').click(function () {
        console.log($('#manual-code').val());

        generateQRCode('https://my.libresummerfreedom.com/register?id=' + $('#manual-code').val());

        // Show the modal
        $("#qrModal").modal("show");
    });

    function generateQRCode(url) {
        var qrCodeContainer = document.getElementById("qr-code");
        qrCodeContainer.innerHTML = ""; // Clear previous QR code
        var qrCode = new QRCode(qrCodeContainer, {
            text: url,
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H,
        });
    }

    $("#closeModal").on("click", function () {
        location.reload(); // Refresh the page
    });
</script>
@endsection