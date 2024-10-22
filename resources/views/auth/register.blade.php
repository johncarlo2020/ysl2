<x-guest-layout>
    <div class="">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="">
                <x-text-input id="code" class="w-full mt-1" type="hidden" name="code" required placeholder="" />

                <x-text-input class="d-none w-full mt-1" type="hidden" name="password" value="password" />
            </div>
            <div class="flex items-center justify-end mt-4 d-none">
                <x-primary-button class="button" id="submitButton">
                    {{ __('SUBMIT') }}
                </x-primary-button>
            </div>
        </form>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            const urlParams = new URLSearchParams(window.location.search);
            // Extract the 'id' parameter from the URL
            const id = urlParams.get("id");
            // Log the 'id' value or use it as needed
            console.log(id);
            $("#code").val(id);

            $.ajax({
                url: '{{ route('checkExisting') }}', // Using Laravel's route() helper function
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the headers
                },
                data: {
                    code: id,
                },
                success: function(response) {
                    if (response == 1) {
                        $("form").attr("action", "{{ route('login') }}");
                        $("form").submit();

                        //change route to login then submit add laravel route name
                    } else {
                        $("form").submit();
                    }
                    console.log(response);
                },
                error: function(xhr, status, error) {}
            });

        });
    </script>
</x-guest-layout>
