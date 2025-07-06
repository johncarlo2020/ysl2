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
                                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
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
                                <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
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
                                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
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
                                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
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
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-body p-3">
                    <figure class="highcharts-figure">
                        <div id="container2"></div>
                    </figure>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-capitalize mb-0"></h6>
                        </div>
                        <div class="col-auto">
                            <div class="form-group mb-0 mr-3 ml-2">
                                <select class="form-control form-control-sm" id="date-format-select">
                                    @foreach ($data['dates'] as $key => $date)
                                        <option value="{{ $date['date'] }}">{{ $date['date'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body p-3">
                    <figure class="highcharts-figure">
                        <div id="container"></div>
                    </figure>
                </div>
            </div>
        </div>


    </div>

    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card ">
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
                                                    {{ $station['value'] ? 'Yes' : 'No' }}</h6>

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


    <script>
        var labels = [];
        var labels2 = [];

        var data = [];
        var data2 = [];
        var chart2;
        var selectedDate = $('#date-format-select').val();

        // Listen for change event on select element
        $('#date-format-select').change(function() {
            selectedDate = $(this).val(); // Get selected date

            // Assuming $data['registrationsPerHour'] is an associative array where keys are dates
            // and values are arrays of registration data
            var newChart = @json($data['registrationsPerHour']);


            // Assuming newChart contains an array of registration data
            console.log(selectedDate);
            newData

            var newLabel = [];
            var newData = [];


            // Assuming 'newChart' is in the format required by Chart.js
            newChart[selectedDate].forEach((dataPoint) => {
                newLabel.push(dataPoint
                    .hour);
                newData.push(dataPoint
                    .registrations);

            });

            high.xAxis[0].setCategories(newLabel);

            high.series[0].setData(newData);
            high.setTitle({
                text: 'Customers per Hour on ' + selectedDate
            });
        });

        var permissionName = "{{ $permission }}";

        var chart = @json($data['usersDaily']);
        console.log(chart);

        var day1 = "{{ $data['dates'][0]['date'] }}";
        var chart2 = @json($data['registrationsPerHour'][$data['dates'][0]['date']]);

        Object.keys(chart).forEach(function(date, index) {
            var dateObj = new Date(date);
            var formattedDate = dateObj.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric'
            });
            labels.push(formattedDate);
            data.push(chart[date]); // Push the count for the corresponding date
        });


        chart2.forEach(function(obj) {
            // Log index

            // Push date and hour as label
            labels2.push(obj.hour);

            // Push registrations count
            data2.push(obj.registrations);
        });

        var high = Highcharts.chart('container', {
            chart: {
                type: 'column' // Set chart type to 'column'
            },
            title: {
                text: 'Customers per Hour',
                align: 'left'
            },
            yAxis: {
                title: {
                    text: 'Registrations'
                }
            },
            xAxis: {
                categories: labels2, // Use labels2 as xAxis categories
                title: {
                    text: 'Hour'
                },
                accessibility: {
                    rangeDescription: labels2.join(', ') // Set range description using labels2
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: [{
                name: 'Registration',
                data: data2
            }],
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.y; // Display the data value as the label
                        },
                        inside: false,
                        verticalAlign: 'top', // Position the label at the top of the column
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

        var high2 = Highcharts.chart('container2', {
            chart: {
                type: 'column' // Set chart type to 'column'
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
                title: {
                    text: 'Hour'
                },
                accessibility: {
                    rangeDescription: labels.join(', ') // Set range description using labels2
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
                column: {
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.y; // Display the data value as the label
                        },
                        inside: false,
                        verticalAlign: 'top', // Position the label at the top of the column
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


        // function exportToPNG() {
        //     html2canvas(document.getElementById('chart-line'), {
        //         onrendered: function(canvas) {
        //             var link = document.createElement('a');
        //             link.href = canvas.toDataURL('image/png');
        //             link.download = 'chart.png';
        //             link.click();
        //         }
        //     });
        // }

        function exportToPDF() {
            var element = document.getElementById('chart-line');
            html2pdf()
                .from(element)
                .save('overview.pdf');
        }

        function exportToJPEG() {
            var canvas = document.getElementById('chart-line');
            var dataURL = canvas.toDataURL('image/jpeg');
            var link = document.createElement('a');
            link.href = dataURL;
            link.download = 'chart.jpeg';
            link.click();
        }

        function exportToPDF2() {
            var canvas = document.getElementById('chart-line2');
            var pageWidth = 595; // A4 page width in pixels
            var pageHeight = 842; // A4 page height in pixels

            // Set the canvas dimensions to fit within the page
            canvas.width = pageWidth;
            canvas.height = pageHeight;

            // Get the context of the canvas
            var ctx = canvas.getContext('2d');
            // Here you would draw your chart onto the canvas using the context 'ctx'
            // Ensure that the chart is drawn within the canvas dimensions

            // Convert the canvas to a data URL
            var dataURL = canvas.toDataURL();

            // Create a new image element
            var img = new Image();
            img.src = dataURL;

            // Create a new PDF document
            var pdf = new jsPDF('p', 'pt', [pageWidth, pageHeight]);

            // Add the image to the PDF document
            pdf.addImage(img, 'PNG', 0, 0, pageWidth, pageHeight);

            // Save the PDF document
            pdf.save('overview.pdf');
        }

        function exportToJPEG2() {
            var canvas = document.getElementById('chart-line2');
            var dataURL = canvas.toDataURL('image/jpeg');
            var link = document.createElement('a');
            link.href = dataURL;
            link.download = 'chart.jpeg';
            link.click();
        }



        // Iterate over the keys (dates) of the associative array

        // Assuming chart2 is an object with hour keys and registration counts as values


        // Iterate over each object in chart2 array


        var ctx1 = document.getElementById("chart-line").getContext("2d");
        var ctx2 = document.getElementById("chart-line2").getContext("2d");


        var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);
        var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);


        gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
        gradientStroke1.addColorStop(0.2,
            'rgba(94, 114, 228, 0.0)');
        gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

        gradientStroke2.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
        gradientStroke2.addColorStop(0.2,
            'rgba(94, 114, 228, 0.0)');
        gradientStroke2.addColorStop(0, 'rgba(94, 114, 228, 0)');
        new Chart(ctx1, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Customers",
                    tension: 0.4,
                    borderWidth: 0,
                    pointRadius: 0,
                    borderColor: "#5e72e4",
                    backgroundColor: gradientStroke1,
                    borderWidth: 3,
                    fill: true,
                    data: data,
                    maxBarThickness: 50

                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#fbfbfb',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#ccc',
                            padding: 20,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        var chart2 = new Chart(ctx2, {
            type: "bar",
            data: {
                labels: labels2,
                datasets: [{
                    label: selectedDate,
                    tension: 0.4,
                    borderWidth: 0,
                    pointRadius: 0,
                    borderColor: "#5e72e4",
                    backgroundColor: gradientStroke2,
                    borderWidth: 3,
                    fill: true,
                    data: data2,
                    maxBarThickness: 70
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: false
                    },
                    datalabels: {
                        display: true,
                        color: 'black', // Set the color of the labels
                        font: {
                            weight: 'bold' // Set the font weight of the labels
                        },
                        anchor: 'end', // Position of the labels relative to the anchor point
                        align: 'top', // Alignment of the labels relative to the anchor point
                        formatter: function(value) {
                            return value; // Return the value to be displayed
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#fbfbfb',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#ccc',
                            padding: 20,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
    </script>
@endsection
