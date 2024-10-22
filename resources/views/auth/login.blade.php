<x-guest-layout>
    <div class="login">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input
                    id="code"
                    class="block w-full mt-1"
                    type="hidden"
                    name="code"
                    :value="old('code')"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <!-- Password -->
            <x-text-input
                id="password"
                class="block w-full mt-1"
                type="hidden"
                name="password"
                value="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </form>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);

            // Extract the 'id' parameter from the URL
            const id = urlParams.get("id");

            // Log the 'id' value or use it as needed
            console.log(id);
            $("#code").val(id);
            $("form").submit();
        });
    </script>
</x-guest-layout>
