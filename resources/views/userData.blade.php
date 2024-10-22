@extends('layouts.admin')

@section('content')
    <style>
        .big-checkbox {
            transform: scale(1.5);
            /* Increase the size of the checkbox */
        }

        .stripe-li:nth-child(even) {
            background-color: #f2f2f2;
            /* Even rows background color */
        }

        .stripe-li:nth-child(odd) {
            background-color: #ffffff;
            /* Odd rows background color */
        }
    </style>
    <div class="row">
        <div class="mx-4 shadow-lg card ">
            <div class="p-3 card-body">
                <div class="row gx-4">
                    <div class="col-auto">
                        <div class="">
                            <i class="fa-solid fa-user" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                                {{ $user->fname }} {{ $user->lname }}
                            </h5>
                            <p class="mb-0 text-sm font-weight-bold">
                                {{ $user->email }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="py-4 container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-sm text-uppercase">User Information</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">First Name</label>
                                        <input class="form-control" type="text" value="{{ $user->fname }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Last Name</label>
                                        <input class="form-control" type="text" value="{{ $user->lname }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Email Address</label>
                                        <input class="form-control" type="email" value="{{ $user->email }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Phone Number</label>
                                        <input class="form-control" type="text" value="{{ $user->number }}">
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark">
                            <p class="text-sm text-uppercase">Contact Information</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Address</label>
                                        <input class="form-control" type="text" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">City</label>
                                        <input class="form-control" type="text" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Country</label>
                                        <input class="form-control" type="text" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Postal code</label>
                                        <input class="form-control" type="text" value="">
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark">

                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="p-3 pb-0 card-header">
                            <h6 class="mb-0">Stations</h6>
                        </div>
                        <div class="p-3 card-body">
                            <ul class="list-group">
                                @foreach ($user['stations'] as $station)
                                    <li
                                        class="mb-2 border-0 list-group-item stripe-li d-flex justify-content-between ps-0 ">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-1 text-sm text-dark"></h6>
                                                <h6 class="mb-1 text-sm text-dark">#{{ $station['id'] }}
                                                    {{ $station['name'] }}</h6>
                                                <span class="text-xs">Average Time : <span
                                                        class="font-weight-bold">{{ $station['time_spent'] }}
                                                        minutes</span></span>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="checkbox" data-id="{{ $station['id'] }}"
                                                id="station_checkbox_{{ $station['id'] }}" class="big-checkbox"
                                                {{ $station['value'] ? 'checked' : '' }}>
                                        </div>
                                    </li>
                                @endforeach
                                <li
                                    class="mb-2 border-0 list-group-item d-flex justify-content-between ps-0 border-radius-lg">
                                    <div class="d-flex align-items-center">

                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-sm text-dark">Total Minutes</h6>
                                            <span class="text-xs"> <span class="font-weight-bold">{{ $totalMinutes }}
                                                    minutes</span></span>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        var permissionName = "{{ $permission }}";
        if(permissionName === 'full'){
            $('.big-checkbox').change(function() {
            var newState = $(this).prop('checked');
            var user_id = {{ $user->id }}
            var station_id = $(this).data('id');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('check') }}', // Using Laravel's route() helper function
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the headers
                },
                data: {
                    user_id: user_id,
                    station_id: station_id
                },
                success: function(response) {

                },
                error: function(xhr, status, error) {

                }
            });
        });
        }
       
    </script>
@endsection
