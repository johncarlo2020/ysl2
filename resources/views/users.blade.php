@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
    <style>
        .users-table-card .dt-container { padding: 0 20px 20px; }
        #customer-table th, #customer-table td { padding: 16px; vertical-align: middle; }
        #customer-table th { font-size: 11px; letter-spacing: .5px; color: #67748e; background: #f8fafc; border-bottom: 1px solid #e9edf3; }
        .users-table-card .card-header { padding: 24px; border-bottom: 1px solid #edf0f5; }
        .users-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; }
        .users-search { display: flex; align-items: center; gap: 10px; border: 1px solid #dfe5ee; border-radius: 10px; padding: 0 14px; color: #8392ab; width: 320px; max-width: 100%; background: #f8fafc; }
        .users-search:focus-within { border-color: #5e72e4; box-shadow: 0 0 0 3px #5e72e415; }
        .users-search input { border: 0; outline: 0; background: transparent; padding: 12px 0; width: 100%; font-size: 14px; color: #344767; }
        .users-export { position: relative; flex-shrink: 0; }
        .users-export summary { list-style: none; cursor: pointer; background: #5e72e4; color: #fff; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .users-export summary::-webkit-details-marker { display: none; }
        .users-export summary:focus-visible { outline: 3px solid #b6bfff; outline-offset: 2px; }
        .users-export-menu { position: absolute; right: 0; top: calc(100% + 8px); z-index: 20; min-width: 180px; padding: 6px; background: #fff; border: 1px solid #e9edf3; border-radius: 12px; box-shadow: 0 12px 32px #34476720; }
        .users-export-menu button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; background: transparent; padding: 10px 12px; text-align: left; color: #344767; border-radius: 7px; font-size: 13px; }
        .users-export-menu button:hover, .users-export-menu button:focus-visible { background: #f0f2ff; color: #5e72e4; }
        .users-export-menu i { width: 16px; }
        .users-table-card .dt-buttons { display: none; }
        .users-table-card .dt-layout-row:last-child { border-top: 1px solid #edf0f5; padding-top: 16px; margin-top: 16px; }
        .users-table-card .dt-info, .users-table-card .dt-length { color: #8392ab; font-size: 12px; }
        .users-table-card .dt-length select { border: 1px solid #dfe5ee; border-radius: 8px; padding: 6px 10px; margin-right: 8px; color: #344767; background: #fff; }
        .users-table-card .dt-paging .dt-paging-button { border: 1px solid #e9edf3 !important; border-radius: 8px !important; background: #fff !important; color: #67748e !important; min-width: 34px; padding: 7px 10px !important; margin: 0 3px; font-size: 12px; box-shadow: none !important; }
        .users-table-card .dt-paging .dt-paging-button.current { background: #5e72e4 !important; border-color: #5e72e4 !important; color: #fff !important; }
        .users-table-card .dt-paging .dt-paging-button:hover { background: #eef0ff !important; color: #5e72e4 !important; }
        .users-table-card .dt-container .dt-paging .dt-paging-button.current,
        .users-table-card .dt-container .dt-paging .dt-paging-button.current:hover,
        .users-table-card .dt-container .dt-paging .dt-paging-button.current:focus {
            background: #5e72e4 !important;
            border-color: #5e72e4 !important;
            color: #fff !important;
        }
        .users-table-card .dt-paging .dt-paging-button.disabled { opacity: .4; }
        @media (max-width: 576px) { .users-toolbar { padding: 16px; gap: 10px; } .users-export summary { padding: 12px; } .users-table-card .dt-container { padding: 0 12px 16px; } }

        .user-timestamp { display: flex; flex-direction: column; gap: 4px; min-width: 130px; font-size: 12px; color: #344767; }
        .user-timestamp span { font-size: 11px; color: #8392ab; }
        .user-duration { display: inline-block; padding: 7px 10px; border-radius: 8px; background: #e7f5ec; color: #23864b; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .user-in-progress { color: #8392ab; font-size: 12px; white-space: nowrap; }
        #customer-table th { max-width: 190px; }
        .station-status { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; font-weight: 700; }
        .station-status.complete { background: #e7f5ec; color: #23864b; }
        .station-status.pending { background: #f1f2f5; color: #8392ab; }
        #customer-table td.dt-empty { padding: 48px 16px; background: #fff; text-align: center; }
        .users-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .users-empty-icon { display: flex; align-items: center; justify-content: center; width: 64px; height: 64px; margin-bottom: 6px; border-radius: 18px; background: #eef0ff; color: #5e72e4; font-size: 26px; }
        .users-empty strong { color: #344767; font-size: 16px; font-weight: 600; }
        .users-empty p { margin: 0; max-width: 320px; color: #8392ab; font-size: 13px; line-height: 1.6; white-space: normal; }
    </style>
    <div class="mt-4 mb-4 card users-table-card">
        <div class="card-header pb-3">
            <h5 class="mb-1">Users</h5>
            <p class="text-sm text-secondary mb-0">Select a user ID to manage station checks. Times shown in Manila time.</p>
        </div>
        <div class="users-toolbar">
            <label class="users-search" for="users-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="users-search" type="search" placeholder="Search by user ID or mobile number" aria-label="Search users">
            </label>
            <details class="users-export" id="users-export">
                <summary><i class="fa-solid fa-arrow-up-from-bracket me-2" aria-hidden="true"></i>Export current page <i class="fa-solid fa-chevron-down ms-2" aria-hidden="true"></i></summary>
                <div class="users-export-menu">
                    @foreach (['copy' => ['fa-copy', 'Copy'], 'csv' => ['fa-file-csv', 'CSV'], 'excel' => ['fa-file-excel', 'Excel'], 'pdf' => ['fa-file-pdf', 'PDF'], 'print' => ['fa-print', 'Print']] as $type => $button)
                        <button type="button" data-export="{{ $type }}"><i class="fa-solid {{ $button[0] }}" aria-hidden="true"></i>{{ $button[1] }}</button>
                    @endforeach
                </div>
            </details>
        </div>
        <div class="table-responsive">
            <table id="customer-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Mobile number</th>
                        <th>Registered</th>
                        <th>Completed</th>
                        <th>Total duration</th>
                        @foreach ($data['stations'] as $id => $station)
                            <th class="text-center">{{ $station['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = new DataTable('#customer-table', {
                order: [[0, 'desc']],
                pageLength: 10,
                serverSide: true,
                orderMulti: false,
                processing: true,
                ajax: {
                    url: @json(route('users')),
                    dataSrc: (response) => {
                        const body = document.createElement('tbody');
                        body.innerHTML = response.rows;
                        return Array.from(body.rows, (row) => Array.from(row.cells, (cell) => cell.innerHTML));
                    },
                },
                columnDefs: [{ targets: '_all', defaultContent: '' }],
                layout: {
                    topStart: { buttons: ['copy', 'csv', 'excel', 'pdf', 'print'].map(function (type) {
                        return {
                            extend: type,
                            name: type,
                            exportOptions: {
                                format: {
                                    body: function (data, row, column, node) {
                                        if (node.querySelector('.station-status')) {
                                            return Array.from(node.querySelectorAll('.station-status'))
                                                .map(function (station) { return station.title; }).join('; ');
                                        }
                                        return node.dataset.export || node.textContent.trim();
                                    },
                                },
                            },
                        };
                    }) },
                    topEnd: null,
                    bottomStart: ['pageLength', 'info'],
                    bottomEnd: 'paging',
                },
                language: {
                    search: 'Search users:',
                    searchPlaceholder: 'User ID or mobile number',
                    emptyTable: '<div class="users-empty"><span class="users-empty-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span><strong>No users yet</strong><p>Registered users will appear here once they start their journey.</p></div>',
                    zeroRecords: '<div class="users-empty"><span class="users-empty-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span><strong>No records found</strong><p>Try a different user ID or mobile number, or clear your search to see all users.</p></div>',
                },
            });
            let searchTimer;
            document.getElementById('users-search').addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => table.search(this.value).draw(), 350);
            });
            const exportMenu = document.getElementById('users-export');
            exportMenu.querySelectorAll('[data-export]').forEach(function (button) {
                button.addEventListener('click', function () {
                    table.button(button.dataset.export + ':name').trigger();
                    exportMenu.open = false;
                    exportMenu.querySelector('summary').focus();
                });
            });
            document.addEventListener('click', function (event) {
                if (!exportMenu.contains(event.target)) exportMenu.open = false;
            });
            exportMenu.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    exportMenu.open = false;
                    exportMenu.querySelector('summary').focus();
                }
            });
        });
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
@endpush
