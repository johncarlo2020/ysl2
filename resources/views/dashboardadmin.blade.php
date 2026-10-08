@extends('layouts.admin')

@section('content')
    <style>
        .admin-overview { padding: 20px 8px 32px; }
        .overview-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .overview-heading h2 { font-size: 26px; color: #344767; margin-bottom: 6px; }
        .overview-heading p { color: #8392ab; font-size: 14px; margin: 0; }
        .overview-link { background: #fff; border: 1px solid #e1e6ee; color: #344767; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; white-space: nowrap; }
        .overview-metrics, .overview-stations { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
        .admin-overview .card { border: 1px solid #e9edf3; box-shadow: 0 4px 20px #34476708; border-radius: 16px; }
        .metric-card { padding: 24px; display: flex; justify-content: space-between; gap: 12px; }
        .metric-label { font-size: 12px; font-weight: 600; color: #8392ab; margin-bottom: 12px; }
        .metric-value { font-size: 30px; font-weight: 700; color: #344767; margin: 0; line-height: 1.2; }
        .metric-icon { display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; background: #eef0ff; color: #5e72e4; }
        .metric-icon.green { background: #e7f5ec; color: #23864b; }
        .metric-icon.pink { background: #fcecf2; color: #c85c84; }
        .metric-icon.gold { background: #fff4dd; color: #b58428; }
        .overview-section-title { font-size: 17px; margin: 24px 0 16px; }
        .station-summary { padding: 20px; display: flex; align-items: flex-start; gap: 14px; height: 100%; }
        .station-summary img { width: 56px; height: 72px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
        .station-summary h6 { font-size: 12px; line-height: 18px; margin: 6px 0 12px; min-height: 36px; }
        .station-summary > div { flex: 1; min-width: 0; }
        .station-summary p { font-size: 12px; color: #8392ab; margin: 0; }
        .station-summary .station-label { font-size: 10px; color: #5e72e4; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; }
        .overview-charts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin: 24px 0; }
        .overview-charts .card { padding: 20px; min-width: 0; }
        .overview-charts figure { margin: 0; }
        .recent-users-heading { padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .recent-users-heading h5 { font-size: 17px; margin: 0; }
        .recent-users-heading a { font-size: 13px; font-weight: 600; }
        .overview-users { margin-bottom: 0; width: 100%; table-layout: fixed; min-width: 700px; }
        .overview-users th:first-child { width: 20%; }
        .overview-users th:not(:first-child) { width: {{ count($data['stations']) ? 80 / count($data['stations']) : 80 }}%; }
        .overview-users th .table-station-number { display: block; color: #5e72e4; font-size: 10px; letter-spacing: .6px; margin-bottom: 6px; }
        .overview-users td:first-child { overflow-wrap: anywhere; }
        .overview-users tbody tr:last-child td { border-bottom: 0; }
        .overview-users tbody tr:hover { background: #fafbff; }
        .overview-users th { background: #f8fafc; color: #8392ab; font-size: 11px; padding: 16px 20px; white-space: normal; line-height: 17px; vertical-align: top; border-top: 1px solid #edf0f5; }
        .overview-users td { padding: 16px 20px; font-size: 14px; }
        .overview-status { display: inline-flex; justify-content: center; align-items: center; width: 34px; height: 34px; border-radius: 50%; background: #f1f2f5; color: #8392ab; }
        .overview-status.complete { background: #e7f5ec; color: #23864b; }
        @media (max-width: 1199px) { .overview-metrics, .overview-stations { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) { .overview-charts { grid-template-columns: 1fr; } .overview-heading { align-items: flex-start; flex-direction: column; } .metric-card { padding: 16px; } .metric-value { font-size: 25px; } }
        @media (max-width: 420px) { .overview-stations { grid-template-columns: 1fr; } .metric-icon { width: 32px; height: 32px; } .overview-metrics { gap: 12px; } }
    </style>
    <div class="admin-overview">
        <div class="overview-heading">
            <div><h2>Dashboard overview</h2><p>User activity and station progress at a glance.</p></div>
            <a href="{{ route('users') }}" class="overview-link">Manage users <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i></a>
        </div>
        <div class="overview-metrics">
            @foreach ([['Total users', $data['usersCount'], 'fa-users', ''], ["Today's users", $data['userToday'], 'fa-calendar-day', 'pink'], ['Completion rate', $data['percentage'] . '%', 'fa-chart-simple', 'green'], ['Users finished', $data['completedUsers'], 'fa-circle-check', 'gold']] as $metric)
                <div class="card"><div class="metric-card">
                    <div><p class="metric-label">{{ $metric[0] }}</p><p class="metric-value">{{ $metric[1] }}</p></div>
                    <span class="metric-icon {{ $metric[3] }}"><i class="fa-solid {{ $metric[2] }}" aria-hidden="true"></i></span>
                </div></div>
            @endforeach
        </div>
        <h3 class="overview-section-title">Station activity</h3>
        <div class="overview-stations">
            @foreach ($data['stations'] as $station)
                <div class="card"><div class="station-summary">
                    <img src="{{ asset("images/station 0{$station['id']}.webp") }}" alt="">
                    <div><span class="station-label">Station {{ $station['id'] }}</span><h6>{{ $station['name'] }}</h6><p>Average time <strong class="text-dark">{{ $station['average_timespent'] }} min</strong></p></div>
                </div></div>
            @endforeach
        </div>
        <div class="overview-charts">
            <div class="card"><figure class="highcharts-figure"><div id="container2"></div></figure></div>
            <div class="card"><figure class="highcharts-figure"><div id="container"></div></figure></div>
        </div>
        <div class="card overflow-hidden">
            <div class="recent-users-heading"><h5>Recent users</h5><a href="{{ route('users') }}">View all <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a></div>
            <div class="table-responsive">
                <table class="table align-items-center overview-users">
                    <thead><tr><th>User ID</th>@foreach ($data['stations'] as $station)<th class="text-center"><span class="table-station-number">STATION {{ $station['id'] }}</span>{{ $station['name'] }}</th>@endforeach</tr></thead>
                    <tbody>
                        @forelse ($data['users'] as $user)
                            <tr>
                                <td><a href="{{ route('userData', ['user' => $user->id]) }}" class="text-primary font-weight-bold">{{ $user->code ?: $user->id }}</a></td>
                                @foreach ($user['stations'] as $id => $station)
                                    <td class="text-center"><span class="overview-status {{ $station['value'] ? 'complete' : '' }}" role="img" aria-label="{{ $station['value'] ? 'Completed' : 'Pending' }}" title="{{ $station['value'] ? 'Completed' : 'Pending' }}"><i class="fa-solid {{ (int) $id === 4 ? 'fa-gift' : ($station['value'] ? 'fa-check' : 'fa-xmark') }}" aria-hidden="true"></i></span></td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($data['stations']) + 1 }}" class="text-center text-secondary py-4">No users yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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
        Highcharts.setOptions({
            colors: ['#5e72e4', '#2d9d78', '#e49aaf', '#e8b85c', '#7186a5'],
            chart: { backgroundColor: '#ffffff', plotBackgroundColor: '#ffffff', style: { fontFamily: 'Open Sans, sans-serif' }, spacing: [12, 8, 12, 8] },
            title: { style: { color: '#344767', fontSize: '16px', fontWeight: '600' } },
            xAxis: { lineColor: '#e9edf3', tickColor: '#e9edf3', labels: { style: { color: '#8392ab', fontSize: '11px' } } },
            yAxis: { gridLineColor: '#f0f2f5', labels: { style: { color: '#8392ab', fontSize: '11px' } } },
            legend: { itemStyle: { color: '#67748e', fontSize: '11px', fontWeight: '400' } },
        });
        var labels = [];
        var data = [];
        var permissionName = "{{ $permission }}";

        var chart = @json($data['usersDaily']);


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
                height: 320
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
                height: 320
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
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom'
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
