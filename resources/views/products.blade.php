@extends('layouts.admin')

@section('content')
    <style>
        #customer-table tbody tr {
            cursor: pointer;
        }
    </style>

    <!-- Add Stock Confirmation Modal -->
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to add stock for <strong id="productName"></strong>?</p>
                    
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Current Available Stock:</span>
                            <strong id="currentStock">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>Total Allocation:</span>
                            <strong id="totalAllocation">0</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stockQuantity" class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" id="stockQuantity" min="1" value="1" 
                               oninput="updateNewStock()" required>
                        <small class="text-muted">Maximum: <span id="maxQuantity">0</span></small>
                    </div>
                    
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between">
                            <span>New Available Stock:</span>
                            <strong id="newStock">1</strong>
                        </div>
                    </div>
                    
                    <input type="hidden" id="productId">
                    <input type="hidden" id="currentStockValue">
                    <input type="hidden" id="allocationValue">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmAddStock()">Confirm</button>
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
                            <th>ID</th>

                            <th>Name</th>
                            <th>Stocks Available</th>
                            <th>Stocks Issued</th>
                            <th>Total Added</th>
                            <th>Refill</th>

                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>

                                    <td>Locker {{ $product->name }}</td>
                                    <td>{{ $product->available }}</td>
                                    <td>{{ $product->total_collected }} / {{ $product->allocation }}</td>
                                    <td><span class="badge bg-info">{{ $product->total_added }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-warning"
                                            onclick="openAddStockModal({{ $product->id }}, 'Locker {{ $product->name }}', {{ $product->available }}, {{ $product->allocation }})">Add Stocks</button>
                                    </td>
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

        function openAddStockModal(productId, productName, currentStock, allocation) {
            $('#productId').val(productId);
            $('#productName').text(productName);
            $('#currentStock').text(currentStock);
            $('#totalAllocation').text(allocation);
            $('#currentStockValue').val(currentStock);
            $('#allocationValue').val(allocation);
            
            // No maximum limit - allow any quantity
            $('#maxQuantity').text('Unlimited');
            $('#stockQuantity').val(1).removeAttr('max').prop('disabled', false);
            $('#newStock').text(parseInt(currentStock) + 1);
            $('#addStockModal .btn-primary').prop('disabled', false).text('Confirm');
            
            $('#addStockModal').modal('show');
        }

        function closeModal() {
            $('#addStockModal').modal('hide');
        }

        function updateNewStock() {
            var currentStock = parseInt($('#currentStockValue').val()) || 0;
            var quantity = parseInt($('#stockQuantity').val()) || 0;
            
            if (quantity < 1) {
                $('#stockQuantity').val(1);
                quantity = 1;
            }
            
            var newStock = currentStock + quantity;
            $('#newStock').text(newStock);
        }

        function confirmAddStock() {
            var productId = $('#productId').val();
            var quantity = $('#stockQuantity').val();
            
            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity (minimum 1)');
                return;
            }

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('refill') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                data: {
                    id: productId,
                    quantity: parseInt(quantity)
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Error adding stock. Please try again.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    alert(errorMessage);
                }
            });
        }

        function refill(product) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('refill') }}', // Using Laravel's route() helper function
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the headers
                },
                data: {
                    id: product,
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {

                }
            });
        }
    </script>
@endsection
