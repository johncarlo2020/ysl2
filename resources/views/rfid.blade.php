@extends('layouts.admin')

@section('content')
<style>
    .rfid-page { margin-top: 24px; color: #344767; }
    .rfid-card { border: 1px solid #edf0f5; border-radius: 16px; overflow: hidden; margin-bottom: 24px; }
    .rfid-header { padding: 24px; border-bottom: 1px solid #edf0f5; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .rfid-header h5 { margin: 0 0 6px; }
    .rfid-caption { margin: 0; color: #8392ab; font-size: 13px; line-height: 1.6; }
    .rfid-app-note { display: flex; gap: 14px; align-items: center; padding: 18px 24px; background: #eef0ff; border-radius: 12px; margin-bottom: 24px; }
    .rfid-app-note > i { color: #5e72e4; font-size: 24px; }
    .rfid-app-note strong { font-size: 14px; }
    .rfid-app-note p { margin: 4px 0 0; font-size: 13px; color: #67748e; }
    .rfid-toolbar { padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .rfid-search { display: flex; align-items: center; gap: 10px; border: 1px solid #dfe5ee; border-radius: 10px; padding: 0 14px; background: #f8fafc; color: #8392ab; width: 340px; max-width: 100%; }
    .rfid-search input { border: 0; background: transparent; outline: 0; padding: 12px 0; width: 100%; color: #344767; font-size: 13px; }
    .rfid-search:focus-within { border-color: #5e72e4; box-shadow: 0 0 0 3px #5e72e415; }
    .rfid-filter { display: flex; gap: 4px; padding: 4px; border: 1px solid #e9edf3; border-radius: 10px; background: #f8fafc; }
    .rfid-filter button { border: 0; border-radius: 7px; padding: 9px 12px; background: transparent; color: #67748e; font-size: 12px; font-weight: 600; cursor: pointer; }
    .rfid-filter button[aria-pressed="true"] { background: #5e72e4; color: #fff; }
    .rfid-filter button:focus-visible { outline: 3px solid #b6bfff; outline-offset: 2px; }
    .rfid-button { border: 0; border-radius: 10px; padding: 12px 16px; font-size: 13px; font-weight: 600; background: #5e72e4; color: #fff; cursor: pointer; }
    .rfid-button:hover { background: #4c60d2; }
    .rfid-button:focus-visible { outline: 3px solid #b6bfff; outline-offset: 2px; }
    .rfid-button.secondary { background: #f1f2f5; color: #67748e; }
    .rfid-button.danger { background: #fff1f2; color: #b42332; }
    .rfid-button:disabled { opacity: .5; cursor: wait; }
    .rfid-card .dt-container { padding: 0 20px 20px; }
    #rfid-table th, #rfid-table td { padding: 16px; vertical-align: middle; }
    #rfid-table th { background: #f8fafc; font-size: 11px; color: #67748e; letter-spacing: .5px; border-bottom: 1px solid #e9edf3; }
    #rfid-table td { font-size: 13px; }
    .rfid-status { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; background: #e7f5ec; color: #23864b; font-size: 12px; white-space: nowrap; }
    .rfid-status.pending { background: #f1f2f5; color: #8392ab; }
    .rfid-uid { font-family: monospace; color: #67748e; }
    .rfid-card .dt-buttons .dt-button { background: #fff; border: 1px solid #dfe5ee; border-radius: 8px; color: #67748e; font-size: 12px; padding: 8px 12px; }
        .rfid-card .dt-layout-row:last-child { border-top: 1px solid #edf0f5; padding-top: 16px; margin-top: 16px; }
        .rfid-card .dt-info, .rfid-card .dt-length { color: #8392ab; font-size: 12px; }
        .rfid-card .dt-length select { border: 1px solid #dfe5ee; border-radius: 8px; padding: 6px 10px; margin-right: 8px; color: #344767; background: #fff; }
        .rfid-card .dt-paging .dt-paging-button { border: 1px solid #e9edf3 !important; border-radius: 8px !important; background: #fff !important; color: #67748e !important; min-width: 34px; padding: 7px 10px !important; margin: 0 3px; font-size: 12px; box-shadow: none !important; }
        .rfid-card .dt-paging .dt-paging-button.current { background: #5e72e4 !important; border-color: #5e72e4 !important; color: #fff !important; }
        .rfid-card .dt-paging .dt-paging-button:hover { background: #eef0ff !important; color: #5e72e4 !important; }
        .rfid-card .dt-container .dt-paging .dt-paging-button.current,
        .rfid-card .dt-container .dt-paging .dt-paging-button.current:hover,
        .rfid-card .dt-container .dt-paging .dt-paging-button.current:focus {
            background: #5e72e4 !important;
            border-color: #5e72e4 !important;
            color: #fff !important;
        }
        .rfid-card .dt-paging .dt-paging-button.disabled { opacity: .4; }
    .rfid-empty { padding: 32px 16px; text-align: center; }
    .rfid-empty i { display: block; font-size: 30px; color: #5e72e4; margin-bottom: 16px; }
    .rfid-empty strong { display: block; margin-bottom: 6px; }
    .rfid-empty p { margin: 0; font-size: 13px; color: #8392ab; }
    .rfid-dialog { width: calc(100% - 32px); max-width: 460px; padding: 28px; border: 1px solid #edf0f5; border-radius: 18px; color: #344767; box-shadow: 0 24px 64px #34476730; }
    .rfid-dialog::backdrop { background: #172b4d80; }
    .rfid-dialog h2 { font-size: 20px; margin-bottom: 12px; }
    .rfid-dialog .form-control { border-radius: 10px; padding: 12px; margin: 8px 0 16px; }
    .rfid-dialog-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .rfid-result { margin-top: 16px; padding: 14px; border-radius: 10px; background: #f8fafc; font-size: 13px; }
    .rfid-result:empty { display: none; }
    @media (max-width: 576px) { .rfid-header, .rfid-toolbar { padding: 16px; } .rfid-card .dt-container { padding: 0 12px 16px; } }
        .rfid-export { position: relative; flex-shrink: 0; }
        .rfid-export summary { list-style: none; cursor: pointer; background: #5e72e4; color: #fff; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .rfid-export summary::-webkit-details-marker { display: none; }
        .rfid-export summary:focus-visible { outline: 3px solid #b6bfff; outline-offset: 2px; }
        .rfid-export-menu { position: absolute; right: 0; top: calc(100% + 8px); z-index: 20; min-width: 180px; padding: 6px; background: #fff; border: 1px solid #e9edf3; border-radius: 12px; box-shadow: 0 12px 32px #34476720; }
        .rfid-export-menu button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; background: transparent; padding: 10px 12px; text-align: left; color: #344767; border-radius: 7px; font-size: 13px; }
        .rfid-export-menu button:hover, .rfid-export-menu button:focus-visible { background: #f0f2ff; color: #5e72e4; }
        .rfid-export-menu i { width: 16px; }
        .rfid-card .dt-buttons { display: none; }
</style>
<div class="rfid-page">
    <div class="rfid-app-note"><i class="fa-solid fa-desktop" aria-hidden="true"></i><div><strong>Link cards in the app</strong><p>Use the desktop app to link or replace a user’s NFC card. Search and review card links here.</p></div></div>
    <section class="card rfid-card">
        <div class="rfid-header"><div><h5>RFID cards</h5><p class="rfid-caption">Card links for users who have not completed all stations.</p></div></div>
        <div class="rfid-toolbar"><label class="rfid-search" for="rfid-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input type="search" id="rfid-search" placeholder="Search user ID, mobile number or UID" aria-label="Search RFID cards"></label><details class="rfid-export" id="rfid-export">
                <summary><i class="fa-solid fa-arrow-up-from-bracket me-2" aria-hidden="true"></i>Export <i class="fa-solid fa-chevron-down ms-2" aria-hidden="true"></i></summary>
                <div class="rfid-export-menu">
                    @foreach (['copy' => ['fa-copy', 'Copy'], 'csv' => ['fa-file-csv', 'CSV'], 'excel' => ['fa-file-excel', 'Excel']] as $type => $button)
                        <button type="button" data-export="{{ $type }}"><i class="fa-solid {{ $button[0] }}" aria-hidden="true"></i>{{ $button[1] }}</button>
                    @endforeach
                </div>
            </details></div>
        <p id="rfid-feedback" class="rfid-caption px-4" role="status"></p>
        <div class="px-4 pb-3"><div class="rfid-filter d-inline-flex" role="group" aria-label="Filter by card assignment">
            <button type="button" data-card-filter="all" aria-pressed="true">All</button>
            <button type="button" data-card-filter="assigned" aria-pressed="false">Assigned</button>
            <button type="button" data-card-filter="unassigned" aria-pressed="false">Unassigned</button>
        </div></div>
        <div class="table-responsive">
            <table id="rfid-table" class="display" style="width:100%">
                <thead><tr><th>User ID</th><th>Mobile number / Code</th><th>RFID UID</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-user-id="{{ $user->id }}">
                            <td><a class="font-weight-bold text-primary" href="{{ route('userData', ['user' => $user->id]) }}">{{ $user->id }}</a></td>
                            <td>{{ $user->code ?: '—' }}</td>
                            <td class="rfid-uid">{{ $user->rfid_uid ?: '—' }}</td>
                            <td>@if ($user->rfid_uid)<span class="rfid-status"><i class="fa-solid fa-link" aria-hidden="true"></i>Linked</span>@else<span class="rfid-status pending">Not linked</span>@endif</td>
                            <td>@if ($user->rfid_uid)<button type="button" class="rfid-button danger unlink-btn" data-user-id="{{ $user->id }}" data-user-code="{{ $user->code }}"><i class="fa-solid fa-link-slash me-1" aria-hidden="true"></i>Unlink</button>@else<span class="rfid-caption">Link in app</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<dialog class="rfid-dialog" id="unlink-dialog" aria-labelledby="unlink-title">
    <h2 id="unlink-title">Unlink this card?</h2><p class="rfid-caption" id="unlink-description"></p>
    <div class="rfid-dialog-actions"><button type="button" class="rfid-button secondary" id="cancel-unlink" autofocus>Cancel</button><button type="button" class="rfid-button danger" id="confirm-unlink">Yes, unlink</button></div>
</dialog>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = new DataTable('#rfid-table', {
        order: [[0, 'desc']],
        columnDefs: [{ targets: 4, orderable: false, searchable: false }],
        layout: { topStart: { buttons: ['copy', 'csv', 'excel'].map(type => ({ extend: type, name: type, exportOptions: { columns: [0, 1, 2, 3] } })) }, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
        language: {
            emptyTable: '<div class="rfid-empty"><i class="fa-solid fa-id-card" aria-hidden="true"></i><strong>No users to show</strong><p>Users with unfinished stations will appear here.</p></div>',
            zeroRecords: '<div class="rfid-empty"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><strong>No records found</strong><p>Try another search or card assignment filter.</p></div>'
        }
    });
    document.getElementById('rfid-search').addEventListener('input', function () { table.search(this.value).draw(); });
    const exportMenu = document.getElementById('rfid-export');
    exportMenu.querySelectorAll('[data-export]').forEach(button => button.addEventListener('click', function () {
        table.button(this.dataset.export + ':name').trigger();
        exportMenu.open = false;
        exportMenu.querySelector('summary').focus();
    }));
    document.addEventListener('click', event => { if (!exportMenu.contains(event.target)) exportMenu.open = false; });
    exportMenu.addEventListener('keydown', event => {
        if (event.key === 'Escape') { exportMenu.open = false; exportMenu.querySelector('summary').focus(); }
    });
    const filterButtons = document.querySelectorAll('[data-card-filter]');
    filterButtons.forEach(button => button.addEventListener('click', function () {
        filterButtons.forEach(item => item.setAttribute('aria-pressed', String(item === this)));
        const filter = this.dataset.cardFilter;
        table.column(3).search(filter === 'all' ? '' : (filter === 'assigned' ? '^Linked$' : '^Not linked$'), { regex: true, smart: false }).draw();
    }));
    const unlinkDialog = document.getElementById('unlink-dialog');
    const feedback = document.getElementById('rfid-feedback');
    let pendingUser = null;
    async function post(url, data) {
        const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(data) });
        const body = await response.json();
        if (!response.ok) throw new Error(body.errors?.rfid_uid?.[0] || body.message || 'Request failed. Please try again.');
        return body;
    }
    function openUnlink(id, code) {
        pendingUser = id;
        document.getElementById('unlink-description').textContent = 'Remove the card linked to user ' + code + '? A new card can be linked in the desktop app.';
        unlinkDialog.showModal();
    }
    document.getElementById('rfid-table').addEventListener('click', event => {
        const button = event.target.closest('.unlink-btn');
        if (button) openUnlink(button.dataset.userId, button.dataset.userCode);
    });
    document.getElementById('cancel-unlink').addEventListener('click', () => unlinkDialog.close());
    document.getElementById('confirm-unlink').addEventListener('click', async function () {
        this.disabled = true;
        const userId = pendingUser;
        try {
            await post(@json(route('rfid.unlink')), { user_id: userId });
            const row = table.row('[data-user-id="' + userId + '"]');
            if (row.any()) {
                const node = row.node();
                node.cells[2].textContent = '—';
                node.cells[3].innerHTML = '<span class="rfid-status pending">Not linked</span>';
                node.cells[4].innerHTML = '<span class="rfid-caption">Link in app</span>';
                row.invalidate('dom').draw(false);
            }
            feedback.textContent = 'Card unlinked successfully.';
            unlinkDialog.close();
        } catch (error) { document.getElementById('unlink-description').textContent = error.message; }
        finally { this.disabled = false; }
    });

});
</script>
@endpush
