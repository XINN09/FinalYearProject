<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Labour Cost - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/labourCost.css') }}">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                            <h3>
                                <a href="{{ route('projectCost', ['projectID' => $project->projectID]) }}" class="back-arrow">&lt;</a> 
                                Project Labour Cost - Worker
                            </h3>
                            <input type="text" id="searchWorker" placeholder="Search worker..." onkeyup="filterWorkers()">
                        </div>

                        <hr style="margin-bottom: 20px;">


                        <!-- No Workers Assigned Message -->
                        <div id="noWorkersAssignedMessage" class="no-workers-message">
                            No workers are assigned to this project.
                        </div>

                        <!-- Total Labour Cost -->
                        <div class="total-labour-cost">
                            <h3>Total Labour Cost: RM{{ number_format($totalLabourCost, 2) }}</h3>
                        </div>

                        <!-- Chart Container -->
                        <div class="chart-container">
                            <!-- Zero Labour Cost Message -->
                            <div id="zeroLabourCostMessage" class="zero-labour-cost-message">
                                The labour cost for this project is RM0.
                            </div>
                            <canvas id="labourCostChart"></canvas>
                        </div>


                        <!-- Labour Cost Cards -->
                        <div class="labour-cost-container">
                            <h3 style="margin-bottom: 15px;">Labour Cost Pay</h3>
                            <div class="labour-card-container">
                                @foreach($workers as $worker)
                                <div class="labour-card" data-worker-name="{{ $worker['userName'] }}">
                                    <h4>{{ $worker['userName'] }}</h4>
                                    <p>Day Worked: {{ $worker['daysWorked'] }}</p>
                                    <p>Rate: RM<span class="rate">{{ number_format($worker['ratePerDay'], 2) }}</span>/day</p>
                                    <p>Total Pay: RM<span class="total-pay">{{ number_format($worker['daysWorked'] * $worker['ratePerDay'], 2) }}</span></p>
                                    <button class="modify-button" onclick="openModifyModal('{{ $worker['workerID'] }}', '{{ $worker['ratePerDay'] }}')">Modify</button>
                                </div>
                                @endforeach
                            </div>
                            <!-- No Workers Found Message -->
                            <div id="noWorkersMessage" style="display: none; text-align: center; margin-top: 20px; color: #777;">
                                No workers found.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modify Modal -->
        <div id="modifyModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModifyModal()">&times;</span>
                <h3>Modify Worker Rate</h3>
                <input type="number" id="newRate" placeholder="Enter new rate" style="margin: 15px 0; padding: 5px;">
                <button onclick="saveNewRate()">Save</button>
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
            { id: "projectNameText", type: "text" },
            { id: "startDateText", type: "date" },
            { id: "endDateText", type: "date" },
            { id: "addressText", type: "text" },
            { id: "descriptionText", type: "text" }
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
            // Worker Data for Pie Chart
            const workers = @json($workers);
            const workerNames = workers.map(worker => worker.userName);
            const workerCosts = workers.map(worker => worker.daysWorked * worker.ratePerDay);
            const noWorkersAssignedMessage = document.getElementById('noWorkersAssignedMessage');
            const totalLabourCostDiv = document.querySelector('.total-labour-cost');
            const chartContainer = document.querySelector('.chart-container');
            const labourCostContainer = document.querySelector('.labour-cost-container');
            if (workers.length === 0) {
            noWorkersAssignedMessage.style.display = 'block'; // Show the "No Workers Assigned" message
            totalLabourCostDiv.style.display = 'none'; // Hide total labour cost
            chartContainer.style.display = 'none'; // Hide chart container
            labourCostContainer.style.display = 'none'; // Hide labour cost container
            } else {
            noWorkersAssignedMessage.style.display = 'none'; // Hide the "No Workers Assigned" message
            }

            // Check if the total labour cost is 0
            const zeroLabourCostMessage = document.getElementById('zeroLabourCostMessage');
            const totalLabourCost = {{ $totalLabourCost }};
            if (totalLabourCost === 0) {
            zeroLabourCostMessage.style.display = 'block'; // Show the "Labour Cost is 0" message
            document.getElementById('labourCostChart').style.display = 'none'; // Hide the chart
            } else {
            zeroLabourCostMessage.style.display = 'none'; // Hide the "Labour Cost is 0" message
            }

            // Initialize Pie Chart (only if there are workers and labour cost is not 0)
            if (workers.length > 0 && totalLabourCost > 0) {
            const ctx = document.getElementById('labourCostChart').getContext('2d');
            const labourCostChart = new Chart(ctx, {
            type: 'pie',
                    data: {
                    labels: workerNames,
                            datasets: [{
                            data: workerCosts,
                                    backgroundColor: [
                                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                                    ],
                            }]
                    },
                    options: {
                    responsive: true,
                            maintainAspectRatio: false,
                    }
            });
            }

            // Modify Modal Functions
            let currentWorkerID = null;
            function openModifyModal(workerID, currentRate) {
            currentWorkerID = workerID;
            document.getElementById('newRate').value = currentRate;
            document.getElementById('modifyModal').style.display = 'block';
            }

            function closeModifyModal() {
            document.getElementById('modifyModal').style.display = 'none';
            }

            async function saveNewRate() {
            const newRate = document.getElementById('newRate').value;
            if (!newRate || isNaN(newRate) || newRate < 0) {
            alert('Please enter a valid rate.');
            return;
            }

            try {
            const response = await fetch(`/update-worker-rate/${currentWorkerID}`, {
            method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ ratePerDay: newRate }),
            });
            if (!response.ok) {
            throw new Error('Network response was not ok');
            }

            const result = await response.json();
            if (result.success) {
            // Refresh the page to reflect the updated data
            window.location.reload();
            } else {
            alert(result.message || 'Failed to update rate.');
            }
            } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the rate.');
            }
            }

            // Search Worker Function
            function filterWorkers() {
            const searchQuery = document.getElementById('searchWorker').value.toLowerCase();
            const workerCards = document.querySelectorAll('.labour-card');
            let found = false;
            workerCards.forEach(card => {
            const workerName = card.getAttribute('data-worker-name').toLowerCase();
            if (workerName.includes(searchQuery)) {
            card.style.display = 'block';
            found = true;
            } else {
            card.style.display = 'none';
            }
            });
            // Show or hide the "No workers found" message
            const noWorkersMessage = document.getElementById('noWorkersMessage');
            if (found) {
            noWorkersMessage.style.display = 'none';
            } else {
            noWorkersMessage.style.display = 'block';
            }
            }

            function disableModifyButtonsIfProjectCompleted() {
            const projectStatus = "{{ $project->projectStatus }}"; // Get the project status from the backend
            if (projectStatus.toLowerCase() === 'completed') {
            // Disable all Modify buttons
            const modifyButtons = document.querySelectorAll('.modify-button');
            modifyButtons.forEach(button => {
            button.disabled = true;
            button.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
            button.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
            });
            }
            }

            // Call the function when the DOM is fully loaded
            document.addEventListener('DOMContentLoaded', disableModifyButtonsIfProjectCompleted);
        </script>
    </body>
</html>