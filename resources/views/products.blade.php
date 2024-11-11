@extends('layouts.admin')

@section('content')
<style>
    #customer-table tbody tr {
        cursor: pointer;
    }
</style>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Refill Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" method="podt" action="">
                    @foreach($products as $product)
                    <div class="mb-3">
                        <label for="productName" class="form-label">{{$product->name}}</label>
                        <input type="number" value="0" class="form-control" name="product_{{$product->id}}"
                            id="productName" required>
                    </div>
                    @endforeach

                    <!-- Add more fields as necessary -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="addProductForm">Save Product</button>
            </div>
        </div>
    </div>
</div>
<div class="mt-4 row">
    <div class="mb-4 col-lg-12 mb-lg-0">
        <div class="card">
            <div class="p-3 pb-0 card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-2">Products</h6>
                    <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addProductModal">
                        Refill
                    </button> -->
                </div>
            </div>

            <div class="table-responsive">
                <table id="customer-table" class="display nowrap" style="width:100%">
                    <thead>
                        <th>Name</th>
                        <th>Stocks Total</th>
                        <th>Stocks Available</th>
                        <th>Stocks Issued</th>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr>
                            <td>Locker {{ $product->name }}</td>
                            <td>{{ $product->allocation }}</td>
                            <td>{{ $product->available }}</td>
                            <td>{{ $product->total_collected }} / {{ $product->allocation }}</td>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

<!-- Include DataTables Buttons JS -->

<script>
    // $(document).ready(function() {
    //     $('#customer-table').DataTable({
    //         dom: 'Bfrtip',
    //         buttons: [
    //             'copy', 'excel', 'pdf', 'csv'
    //         ]
    //     });
    // });
    var table = $('#customer-table').DataTable({
        responsive: true,
        dom: "<'row'<'col-sm-12 col-md-3'l><'col-sm-6 col-md-6 align-items-end'B><'col-sm-12 col-md-3'f>>" +
            "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        order: [
            [0, 'asc']
        ]
    });







</script>
@endsection