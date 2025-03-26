<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Calendar - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/projectCost.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
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
                                Project Cost - Calendar
                            </h3>
                        </div>
                        <hr style="margin-bottom: 20px;">
                        <div class="calendar-container">
                            <!-- Sidebar for Payment List -->
                            <div class="payment-list">
                                <h3 class="upcoming-payment">Upcoming Payments</h3>
                                @if(count($events) > 0)
                                @foreach ($events as $event)
                                <div class="payment-item">{{ $event['title'] }}</div>
                                @endforeach
                                @else
                                <div class="no-payments-message">
                                    <p>No upcoming payments found.</p>
                                </div>
                                @endif
                            </div>

                            <!-- Calendar -->
                            <div class="calendar">
                                <div id="calendar"></div>
                            </div>
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
                document.addEventListener('DOMContentLoaded', function () {
                    var calendarEl = document.getElementById('calendar');

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        events: @json($events)
                    });

                    calendar.render();
                });
            </script>
    </body>
</html>