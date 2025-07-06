@extends('layouts.admin')

@section('content')
    <style>
        .icon-stations {
            width: 60px;
            height: 60px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;

            img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
        }
    </style>
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Customers</p>
                                <h5 class="font-weight-bolder">
                                    {{ $data['usersCount'] }}
                                </h5>
                                {{-- <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+55%</span>
                                since yesterday
                            </p> --}}
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fa-solid fa-user text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Customer</p>
                                <h5 class="font-weight-bolder">
                                    {{ $data['userToday'] }}
                                </h5>
                                {{-- <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+3%</span>
                                since last week
                            </p> --}}
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="fa-solid fa-calendar-day text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Completion Rate</p>
                                <h5 class="font-weight-bolder">
                                    {{ $data['percentage'] }}%
                                </h5>

                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="fa-solid fa-percent text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Customers Finished</p>
                                <h5 class="font-weight-bolder">
                                    {{ $data['completedUsers'] }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="fa-solid fa-circle-check text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        @foreach ($data['stations'] as $station)
            <div class="col">
                <div class="card mb-3">
                    <div class="card-body d-flex justify-content-between rounded  p-3 ">
                        <div class="d-flex align-items-center w-100">
                            <div class="icon-stations">
                                <img class="" src="{{ asset("images/station 0{$station['id']}.webp") }}"
                                    alt="Station Image">
                            </div>
                            <div class="d-flex flex-column">
                                <h6 class="mb-1 text-dark text-sm">{{ $station['name'] }}</h6>
                                <span class="text-xs">Average Time : <span
                                        class="font-weight-bold">{{ $station['average_timespent'] }}
                                        minutes</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row mt-4">
        <div class="col-lg-6 mb-lg-0 mb-3">
            <div class="card z-index-2 h-100">
                <div class="card-body p-3">
                    <figure class="highcharts-figure">
                        <div id="container2"></div>
                    </figure>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-lg-0 mb-3">
            <div class="card z-index-2 h-100">
                <div class="card-body card-with-filter p-3">
                    <figure class="highcharts-figure">
                        <div id="container"></div>
                    </figure>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-3">
            <div class="card card h-100">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-2">Customer</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center ">
                        <tbody>
                            @foreach ($data['users'] as $user)
                                <tr>
                                    <td class="w-5">
                                        <div class="d-flex px-2 py-1 align-items-center">
                                            <div class="ms-4">
                                                <h6 class="text-sm mb-0">{{ $user->id }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-10">
                                        <div class="d-flex px-2 py-1 align-items-center">
                                            <div class="ms-4">
                                                <p class="text-xs font-weight-bold mb-0">Name</p>
                                                <h6 class="text-sm mb-0">{{ $user->fname }} {{ $user->lname }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($user['stations'] as $station)
                                        <td>
                                            <div class="text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $station['name'] }}
                                                </p>
                                                <h6
                                                    class="text-sm mb-0 {{ $station['value'] ? 'text-success' : 'text-danger' }}">
                                                    {{ $station['value'] ? 'Yes' : 'No' }}
                                                </h6>

                                            </div>
                                        </td>
                                    @endforeach


                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
     </div>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Chart.js 3.x -->
    <!-- Chart.js 2.x -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>
    <!-- Chart.js Datalabels plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/canvas2image/0.1.0/canvas2image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <style>
        .highcharts-data-table table {
            border-collapse: collapse;
            border-spacing: 0;
            background-color: transparent;
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .highcharts-data-table th,
        .highcharts-data-table td {
            border: 1px solid #dee2e6;
            padding: .75rem;
            vertical-align: top;
        }

        .highcharts-data-table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .highcharts-data-table tbody+tbody {
            border-top: 2px solid #dee2e6;
        }

        .highcharts-data-table .highcharts-table-caption {
            caption-side: bottom;
            padding-top: .75rem;
            padding-bottom: .75rem;
            color: #6c757d;
            text-align: left;
        }
    </style>

    <script>
        var labels = [];
        var data = [];
        var permissionName = "{{ $permission }}";

        var chart = @json($data['usersDaily']);
        console.log(chart);

        Object.keys(chart).forEach(function(date, index) {
            var dateObj = new Date(date);
            var formattedDate = dateObj.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric'
            });
            labels.push(formattedDate);
            data.push(chart[date]); // Push the count for the corresponding date
        });

        var registrationsPerHour = @json($data['registrationsPerHour']);
        var hours = Object.keys(registrationsPerHour).sort(function(a, b) {
            var timeA = new Date('1970/01/01 ' + a.replace(/([ap]m)/, ' $1'));
            var timeB = new Date('1970/01/01 ' + b.replace(/([ap]m)/, ' $1'));
            return timeA - timeB;
        });
        var allDates = [];

        // Get all unique dates
        for (var hour in registrationsPerHour) {
            if (registrationsPerHour.hasOwnProperty(hour)) {
                registrationsPerHour[hour].forEach(function(item) {
                    if (allDates.indexOf(item.date) === -1) {
                        allDates.push(item.date);
                    }
                });
            }
        }
        allDates.sort();

        // Prepare series data
        var seriesData = allDates.map(function(date) {
            var dataPoints = hours.map(function(hour) {
                var registration = registrationsPerHour[hour].find(r => r.date === date);
                return registration ? registration.registrations : 0;
            });
            return {
                name: date,
                data: dataPoints
            };
        });

        var high = Highcharts.chart('container', {
            chart: {
                type: 'column',
                height: 400
            },
            title: {
                text: 'Hourly Customer Registrations by Date',
                align: 'left'
            },
            xAxis: {
                categories: hours,
                crosshair: true,
                accessibility: {
                    description: 'Hours'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Number of Registrations'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y} registrations</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            if (this.y > 0) {
                                return this.y;
                            }
                            return null;
                        }
                    }
                }
            },
            series: seriesData,
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });

        var high2 = Highcharts.chart('container2', {
            chart: {
                type: 'spline', // Changed from 'line' to 'spline' for curved lines
                height: 400
            },
            title: {
                text: 'Customers Overview',
                align: 'left'
            },
            yAxis: {
                title: {
                    text: 'Registrations'
                }
            },
            xAxis: {
                categories: labels, // Use labels2 as xAxis categories
                accessibility: {
                    rangeDescription: labels.join(', ')
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: [{
                name: 'Registration',
                data: data
            }],
            plotOptions: {
                series: {
                    fill: true, // enable area under the line
                    borderColor: '#3b82f6', // blue line
                    backgroundColor: 'rgba(59, 130, 246, 0.2)', // shaded area
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.y; // Show the count at each dot
                        },
                        verticalAlign: 'bottom',
                        crop: false,
                        overflow: 'none'
                    }
                }
            },
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });


    </script>
@endsection
