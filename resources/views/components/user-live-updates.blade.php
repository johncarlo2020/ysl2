@if (request()->routeIs('landing', 'dashboard', 'station.show'))
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var stateUrl = @json(route('user.live-state'));
        var syncing = false;
        var syncAgain = false;

        async function syncState() {
            if (syncing) {
                syncAgain = true;
                return;
            }
            syncing = true;
            try {
                var response = await fetch(stateUrl, {
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    document.dispatchEvent(new CustomEvent('user:state', { detail: await response.json() }));
                }
            } catch (error) {
                console.error('Unable to sync user progress:', error);
            } finally {
                syncing = false;
                if (syncAgain) {
                    syncAgain = false;
                    syncState();
                }
            }
        }

        // Recover events missed while the browser was asleep or disconnected.
        window.addEventListener('focus', syncState);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) syncState();
        });
        syncState();

        var key = @json(config('broadcasting.connections.pusher.key'));
        if (!key || typeof Pusher === 'undefined') return;

        var pusher = new Pusher(key, {
            cluster: @json(config('broadcasting.connections.pusher.options.cluster', 'mt1')),
            forceTLS: true,
            channelAuthorization: {
                endpoint: @json(url('/broadcasting/auth')),
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }
        });
        var channel = pusher.subscribe('private-user.{{ auth()->id() }}');
        channel.bind('pusher:subscription_succeeded', syncState);
        channel.bind('nfc.assignment.changed', function (data) {
            document.dispatchEvent(new CustomEvent('user:nfc-changed', { detail: data }));
            syncState();
        });
        channel.bind('checked.in', function (data) {
            document.dispatchEvent(new CustomEvent('user:checked-in', { detail: data }));
            syncState();
        });
        channel.bind('pusher:subscription_error', function (error) {
            console.error('User channel subscription failed:', error);
        });
        pusher.connection.bind('error', function (error) {
            console.error('Pusher connection error:', error);
        });
    });
</script>
@endif
