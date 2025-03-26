<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project Cost - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/projectCost.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <div class="main-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                @include('generalComponent.sidebar')
            </aside>

            <!-- Main Content Area -->
            <div class="main-content">
                <!-- Navigation -->
                <header class="navigation">
                    @include('generalComponent.navigation')
                </header>

                <!-- Page Content -->
                <div class="content">
                    <h1 style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                        <div class="tag-header" style="display: flex;">
                            <span style="font-size: 13px; color: #ffffff; background-color: #f0990e; padding: 2px 15px; font-weight: normal; border-radius: 15px; margin: 0 10px;">Project</span>
                            <span style="font-size: 13px; color: #ffffff; background-color: #45a6eb; padding: 2px 15px; font-weight: normal; border-radius: 15px; margin: 0 10px;">
                                {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : 'N/A' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : 'N/A' }}
                            </span>
                        </div>
                        <div class="project-top" onclick="toggleProjectInfo()">
                            <span>{{ $project->projectName }}</span>
                            <button class="toggle-info-btn">
                                <i id="projectInfoIcon" class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </h1>


                    <div class="project-info-panel" id="projectInfoPanel" style="display: none;">
                        <h2>
                            <span id="projectNameText">{{ $project->projectName }}</span>
                            @if($role === 'contractor')
                            <button class="edit-info-btn" id="editButton" onclick="toggleEditMode()">
                                <i class="fa fa-edit"></i> <span id="editButtonText">Edit</span>
                            </button>
                            @endif
                        </h2>

                        <div class="project-address">
                            <p><span id="addressText">{{ $project->projectAddress }}</span></p>
                        </div>

                        <hr style="margin: 10px 0;">

                        <p style="font-size: 14px; font-weight: bold; padding-bottom: 10px;">Project Info</p>
                        <table class="project-info-table">
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td><i class="fa fa-calendar" style="color:#808080; padding-right: 6px;"></i>
                                    <span id="startDateText">{{ \Carbon\Carbon::parse($project->startDate)->format('Y-m-d') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>End Date:</strong></td>
                                <td><i class="fa fa-calendar" style="color:#808080; padding-right: 6px;"></i>
                                    <span id="endDateText">{{ \Carbon\Carbon::parse($project->endDate)->format('Y-m-d') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Duration:</strong></td>
                                <td><i class="fa fa-clock" style="color:#808080; padding-right: 6px;"></i><span id="durationText">-</span></td>
                            </tr>
                            <tr>
                                <td><strong>Contractor:</strong></td>
                                <td class="user-cell">
                                    @if($project->contractorUser)
                                    <span class="user-avatar" style="background-color: #ff5ce8;">
                                        {{ strtoupper(substr($project->contractorUser->userName, 0, 1)) }}
                                    </span>
                                    {{ $project->contractorUser->userName }}
                                    @else
                                    N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Homeowner:</strong></td>
                                <td class="user-cell">
                                    @if($project->homeownerUser)
                                    <span class="user-avatar" style="background-color: #ff6912;">
                                        {{ strtoupper(substr($project->homeownerUser->userName, 0, 1)) }}
                                    </span>
                                    {{ $project->homeownerUser->userName }}
                                    @else
                                    N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="status-badge {{ strtolower($project->projectStatus) }}">
                                        {{ $project->projectStatus }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td>
                                    <span id="descriptionText">{{ $project->projectDesc }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @include('generalComponent.viewNav')

                    <div class="documents">
                        <div class="title">
                            <h3>Project Cost</h3>
                        </div>
                        <hr>
                        <!-- Payment Summary -->
                        <div class="summary-chart-container">
                            <div class="summary">
                                <div class="card">
                                    <h3 class="card_title">Total Costs</h3>
                                    <p class="amount">RM{{ number_format($totalCost, 2) }}</p>
                                </div>
                                <div class="card-paid">
                                    <h3 class="card_title">Total Paid</h3>
                                    <p class="amount">RM{{ number_format($totalPaid, 2) }}</p>
                                </div>
                            </div>
                            <div class="chart-container">
                                <h3 class="chart-title">Payment Phases</h3>
                                <canvas id="paymentPhasesChart"></canvas>
                            </div>
                        </div>

                        <div class="reminder-container">
                            <h3 class="reminder-title">Reminders</h3>
                            <div class="reminder-icons">
                                <!-- Cost Details - Everyone can access -->
                                <a href="{{ route('costDetails', ['projectID' => $project->projectID]) }}" class="reminder-item">
                                    <img src="{{ asset('images/projectCost.png') }}" alt="Calendar">
                                    <p>Cost Details</p>
                                </a>

                                <!-- Calendar - Everyone can access -->
                                <a href="{{ route('calendar', ['projectID' => $project->projectID]) }}" class="reminder-item">
                                    <img src="{{ asset('images/calendar.png') }}" alt="Calendar">
                                    <p>Calendar</p>
                                </a>

                                <!-- Upload Invoice - Only Homeowner and Contractor can access -->
                                @if (auth()->user()->role === 'homeowner')
                                <a href="{{ route('receipt', ['projectID' => $project->projectID]) }}" class="reminder-item">
                                    <img src="{{ asset('images/upload.png') }}" alt="Upload Invoice">
                                    <p>Upload Invoice</p>
                                </a>
                                @endif
                                
                                @if (auth()->user()->role === 'contractor')
                                <a href="{{ route('receipt', ['projectID' => $project->projectID]) }}" class="reminder-item">
                                    <img src="{{ asset('images/upload.png') }}" alt="Upload Invoice">
                                    <p>Check Invoice</p>
                                </a>
                                @endif

                                <!-- Labour Cost - Only Contractor and Worker can access -->
                                @if (auth()->user()->role === 'contractor' || auth()->user()->role === 'worker')
                                <a href="{{ route('labourCost', ['projectID' => $project->projectID]) }}" class="reminder-item">
                                    <img src="{{ asset('images/labour.png') }}" alt="Labour Cost">
                                    <p>Labour Cost</p>
                                </a>
                                @endif
                            </div>
                        </div>

                        <!-- Active Payments Table -->
                        <section class="active-payments">
                            <div class="tab">
                                <button class="tablinks active" onclick="filterPayments('all')">All</button>
                                <button class="tablinks" onclick="filterPayments('pending')">Pending</button>
                                <button class="tablinks" onclick="filterPayments('paid')">Paid</button>
                                <button class="tablinks" onclick="filterPayments('overdue')">Overdue</button>
                            </div>
                            <hr>
                            <table class="payment-table">
                                <thead>
                                    <tr>
                                        <th colspan="2">Payment Status</th>
                                        <th>Reference No</th>
                                        <th>Total Amount</th>
                                        <th>Payment Date</th>
                                        <th>Due Date</th>
                                        <th>Contractor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="no-record-message" style="display: none;">
                                        <td colspan="7" style="text-align: center; color: #888;">No record matched</td>
                                    </tr>
                                    @foreach ($allPayments as $payment)
                                    @php
                                    // Payment Date - Show "-" if null
                                    $paymentDate = $payment->paymentDate ? \Carbon\Carbon::parse($payment->paymentDate)->format('Y-m-d') : '-';

                                    // Determine Due Date / Validity End
                                    $dueDate = null;
                                    $dueDateText = 'N/A';
                                    if ($payment->invoice && $payment->invoice->dueDate) {
                                    $dueDate = \Carbon\Carbon::parse($payment->invoice->dueDate);
                                    $dueDateText = $dueDate->format('Y-m-d');
                                    } elseif ($payment->quotation && $payment->quotation->validityEnd) {
                                    $dueDate = \Carbon\Carbon::parse($payment->quotation->validityEnd);
                                    $dueDateText = $dueDate->format('Y-m-d');
                                    }

                                    // Remarks logic
                                    $remarks = 'No Due Date Available';
                                    if ($payment->paymentStatus === 'paid') {
                                    $remarks = 'Payment Done';
                                    } elseif ($dueDate) {
                                    $daysLeft = \Carbon\Carbon::today()->diffInDays($dueDate, false);
                                    $remarks = $daysLeft > 0 ? "Due in $daysLeft days" : ($daysLeft == 0 ? 'Due today' : 'Missed Payment');
                                    }
                                    @endphp
                                    <tr class="payment-row" data-status="{{ strtolower($payment->paymentStatus) }}">
                                        <td style="padding: 10px 3px 10px 10px;">
                                            <span class="status {{ strtolower($payment->paymentStatus) }}">
                                                {{ ucfirst($payment->paymentStatus) }}
                                            </span>
                                        </td>
                                        <td style="padding-left: 0; font-size: 13px; font-weight: bold;">{{ $remarks }}</td>
                                        <td>{{ $payment->referenceNo }}</td>
                                        <td>RM{{ number_format($payment->paymentAmount, 2) }}</td>
                                        <td>{{ $paymentDate }}</td>
                                        <td style="color: {{ $payment->paymentStatus === 'paid' ? 'inherit' : 'red' }};">
                                            {{ $dueDateText }}
                                        </td>
                                        <td>{{ $payment->contractorName }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <script>

            let editMode = false;
            function toggleEditMode() {
                editMode = !editMode;
                const buttonText = document.getElementById("editButtonText");
                if (editMode) {
                    buttonText.innerText = "Save"; // Edit -> Save
                } else {
                    buttonText.innerText = "Edit"; // Save -> Edit
                    saveProjectInfo();
                }

                const fields = [
                    {id: "projectNameText", type: "text"},
                    {id: "startDateText", type: "date"},
                    {id: "endDateText", type: "date"},
                    {id: "addressText", type: "text"},
                    {id: "descriptionText", type: "text"}
                ];
                fields.forEach(field => {
                    const existingElement = document.getElementById(field.id);
                    const parent = existingElement.parentNode;
                    if (editMode) {
                        const input = document.createElement("input");
                        input.type = field.type;
                        input.value = existingElement.innerText.trim();
                        input.id = field.id;
                        input.className = "editable-field";
                        parent.replaceChild(input, existingElement);
                    } else {
                        const input = document.getElementById(field.id);
                        const span = document.createElement("span");
                        span.id = field.id;
                        span.innerText = input.value;
                        parent.replaceChild(span, input);
                    }
                });
            }




            function saveProjectInfo() {
                const getValue = (id) => {
                    const element = document.getElementById(id);
                    return element.tagName === 'INPUT' ? element.value : element.innerText.trim();
                };
                const data = {
                    projectName: getValue("projectNameText"),
                    startDate: getValue("startDateText"),
                    endDate: getValue("endDateText"),
                    projectAddress: getValue("addressText"),
                    projectDesc: getValue("descriptionText"),
                };
                fetch(`/projects/update/{{ $project->projectID }}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(data)
                }).then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                location.reload();
                            } else {
                                alert("Failed to update project information.");
                            }
                        }).catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while updating project information.");
                });
                calculateDuration();
            }


            function toggleProjectInfo() {
                const panel = document.getElementById('projectInfoPanel');
                panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
                updateProjectTopStyle();
            }

// This keeps the project-top "active" state correct at all times
            function updateProjectTopStyle() {
                const panel = document.getElementById('projectInfoPanel');
                const projectTop = document.querySelector('.project-top');
                const icon = document.getElementById('projectInfoIcon');
                if (panel.style.display === 'block') {
                    projectTop.classList.add('active');
                    icon.classList.remove('fa-angle-down');
                    icon.classList.add('fa-angle-up');
                } else {
                    projectTop.classList.remove('active');
                    icon.classList.remove('fa-angle-up');
                    icon.classList.add('fa-angle-down');
                }
            }

// Call this immediately after any action that could hide/show the panel
            document.addEventListener('DOMContentLoaded', updateProjectTopStyle);
            document.addEventListener("DOMContentLoaded", function () {
                calculateDuration(); // Ensure this is called
            });
            function calculateDuration() {
                const startDateText = document.getElementById("startDateText").innerText.trim();
                const endDateText = document.getElementById("endDateText").innerText.trim();
                const durationText = document.getElementById("durationText");

                console.log("Start Date Text:", startDateText); // Debugging
                console.log("End Date Text:", endDateText); // Debugging

                const startDate = new Date(startDateText);
                const endDate = new Date(endDateText);

                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    console.error("Invalid Dates:", startDateText, endDateText); // Debugging
                    durationText.innerText = "Invalid Dates";
                    return;
                }

                const timeDiff = endDate - startDate;
                if (timeDiff < 0) {
                    console.error("Invalid Date Range:", startDateText, endDateText); // Debugging
                    durationText.innerText = "Invalid Date Range";
                    return;
                }

                const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
                durationText.innerText = `${days} days`;
            }

            function filterPayments(status) {
                let rows = document.querySelectorAll(".payment-row");
                let noRecordMessage = document.getElementById("no-record-message");
                let hasVisibleRows = false;

                // Remove 'active' class from all tablinks
                let tablinks = document.querySelectorAll(".tablinks");
                tablinks.forEach(tab => tab.classList.remove("active"));

                // Find the button that was clicked and add 'active' class to it
                let clickedButton = Array.from(tablinks).find(tab => tab.innerText.toLowerCase() === status || (status === "all" && tab.innerText === "All"));
                if (clickedButton) {
                    clickedButton.classList.add("active");
                }

                // Filter payment rows based on status
                rows.forEach(row => {
                    if (status === "all" || row.getAttribute("data-status") === status) {
                        row.style.display = "table-row";
                        hasVisibleRows = true;
                    } else {
                        row.style.display = "none";
                    }
                });

                // Show "No record matched" message if no rows are visible
                noRecordMessage.style.display = hasVisibleRows ? "none" : "table-row";
            }

            // Optional: Trigger "All" tab by default when the page loads
            document.addEventListener('DOMContentLoaded', function () {
                filterPayments('all');
            });

            // Get payment data from Laravel
            const monthlyPaidData = @json($monthlyPaidData);
                    // Extract labels (months) and data (amounts)
                    const months = Object.keys(monthlyPaidData);
            const paidAmounts = Object.values(monthlyPaidData);

            // Render the bar chart
            const ctx = document.getElementById('paymentPhasesChart').getContext('2d');
            const paymentPhasesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                            label: 'Amount Paid (RM)',
                            data: paidAmounts,
                            backgroundColor: 'rgb(92, 119, 234, 0.5)',
                            borderColor: 'rgb(184, 199, 253, 0.8)',
                            borderWidth: 1
                        }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </body>
</html>