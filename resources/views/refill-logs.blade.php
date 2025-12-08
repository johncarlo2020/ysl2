@extends('layouts.admin')

@section('content')
    <style>
        #refill-logs-table tbody tr {
            cursor: pointer;
        }
    </style>

    <div class="mt-4 row">
        <div class="mb-4 col-lg-12 mb-lg-0">
            <div class="card">
                <div class="p-3 pb-0 card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-2">Refill Logs</h6>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="refill-logs-table" class="display nowrap" style="width:100%">
                        <thead>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Locker</th>
                            <th>Previous Amount</th>
                            <th>Quantity Added</th>
                            <th>New Amount</th>
                            <th>User</th>
                            <th>Notes</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->type === 'refill')
                                            <span class="badge bg-success">Refill</span>
                                        @else
                                            <span class="badge bg-warning">Roulette</span>
                                        @endif
                                    </td>
                                    <td>Locker {{ $log->locker->name }}</td>
                                    <td>{{ $log->previous_amount }}</td>
                                    <td>
                                        @if($log->quantity_added > 0)
                                            <span class="badge bg-success">+{{ $log->quantity_added }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $log->quantity_added }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->new_amount }}</td>
                                    <td>{{ $log->user ? $log->user->name : 'N/A' }}</td>
                                    <td>{{ $log->notes ?? '-' }}</td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css">

    <!-- Include DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

    <script>
        var table = $('#refill-logs-table').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-12 col-md-3'l><'col-sm-6 col-md-6 align-items-end'B><'col-sm-12 col-md-3'f>>" +
                "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            order: [
                [0, 'desc']
            ]
        });
    </script>
@endsection
