<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Gantt Chart - {{ $project->projectName }}</title>
        <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
        <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <link rel="stylesheet" type="text/css" href="{{ asset('css/gantt.css') }}">
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

                    <!-- Gantt Chart Navigation Bar -->
                    <div class="gantt-nav">
                        <button onclick="autoFit()" title="Auto Fit">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button onclick="zoomOut()" title="Zoom Out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button onclick="zoomIn()" title="Zoom In">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <select id="view-mode" onchange="changeViewMode(this.value)">
                            <option value="day">Day</option>
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>

                    <!-- Gantt Chart Container -->
                    <div class="documents">
                        <div class="title">
                            <h3>Gantt Chart View</h3>
                        </div>

                        <p style="color: #e1912d; font-style: italic; margin: 0 20px 1rem 20px; font-size: 13px;">
                            Note: If a task's start date and end date are shown as <strong>1970-01-01</strong>, this indicates that the actual start date and end date have not been assigned yet.
                        </p>
                        <hr>
                        <div id="gantt_chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const projectStatus = "{{ $project->projectStatus }}";

                if (projectStatus === 'Completed') {
                    // Disable task updates (drag-and-drop, resizing)
                    gantt.config.drag_move = false;
                    gantt.config.drag_resize = false;
                    gantt.config.drag_progress = false;

                    // Disable the lightbox (editing)
                    gantt.config.readonly = true;


                    // Disable the "Delete Task" button
                    const deleteTaskButton = document.querySelector('.gantt_delete_button');
                    if (deleteTaskButton) {
                        deleteTaskButton.disabled = true;
                        deleteTaskButton.style.cursor = 'not-allowed';
                        deleteTaskButton.title = 'Task deletion is not allowed for completed projects.';
                    }
                }
            });


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
            const projectStartDate = new Date("{{ $project->startDate }}");
            const projectEndDate = new Date("{{ $project->endDate }}");
            const userRole = "{{ $role }}";

            // Enable the zoom extension
            gantt.plugins({
                zoom: true // Enable zoom functionality
            });

            // Initialize Gantt Chart
            gantt.config.columns = [
                {name: "text", label: "Task Name", width: "*", tree: true, resize: true},
                {name: "start_date", label: "Start Date", align: "center", width: "*", resize: true},
                {name: "end_date", label: "End Date", align: "center", width: "*", resize: true},
                {name: "duration", label: "Duration", align: "center", width: "100", resize: true}
            ];

            gantt.templates.task_class = function (start, end, task) {
                let classes = ["status-" + task.status.toLowerCase().replace(/\s/g, "-")];
                if (task.hasTaskReport) {
                    classes.push("paid-task"); // Add class for paid tasks
                }
                return classes.join(" ");
            };

            // Customize the scale cell to display the date range
            gantt.templates.scale_cell_class = function (date, scale) {
                return "scale-cell";
            };



            gantt.templates.scale_cell_value = function (date, scale) {
                if (scale === "day") {
                    return gantt.date.date_to_str("%d %M")(date);
                } else if (scale === "week") {
                    var start = gantt.date.week_start(date);
                    var end = gantt.date.add(start, 6, "day");
                    return gantt.date.date_to_str("%d")(start) + " - " + gantt.date.date_to_str("%d %M")(end);
                } else if (scale === "month") {
                    return gantt.date.date_to_str("%F")(date);
                } else if (scale === "year") {
                    return gantt.date.date_to_str("%Y")(date);
                }
                return "";
            };


            // Add a clarifying row above the gantt_scale_line
            gantt.config.scales = [
                {unit: "month", step: 1, format: "%F, %Y"}, // Top row for month/year
                {unit: "day", step: 1, format: "%d %M"}     // Bottom row for day/week
            ];

            // Initialize the Gantt chart
            gantt.init("gantt_chart");

            // Adjust column widths to fit content
            adjustColumnWidths();

            // Set up zoom levels
            gantt.ext.zoom.init({
                levels: [
                    {
                        name: "day",
                        scale_height: 50,
                        min_column_width: 80,
                        scales: [
                            {unit: "week", step: 1, format: function (date) {
                                    var start = gantt.date.week_start(date);
                                    var end = gantt.date.add(start, 6, "day");
                                    return gantt.date.date_to_str("%M %d")(start) + " - " + gantt.date.date_to_str("%M %d")(end);
                                }}, // Week range
                            {unit: "day", step: 1, format: "%d %M"} // Day row
                        ]
                    },
                    {
                        name: "week",
                        scale_height: 50,
                        min_column_width: 50,
                        scales: [
                            {unit: "month", step: 1, format: "%F %Y"}, // Month row
                            {unit: "week", step: 1, format: function (date) {
                                    var start = gantt.date.week_start(date);
                                    var end = gantt.date.add(start, 6, "day");
                                    return gantt.date.date_to_str("%d")(start) + " - " + gantt.date.date_to_str("%d %M")(end);
                                }} // Week range
                        ]
                    },
                    {
                        name: "month",
                        scale_height: 50,
                        min_column_width: 120,
                        scales: [
                            {unit: "year", step: 1, format: "%Y"}, // Year row
                            {unit: "month", step: 1, format: "%F"} // Month row
                        ]
                    },
                    {
                        name: "year",
                        scale_height: 50,
                        min_column_width: 120,
                        scales: [
                            {unit: "year", step: 5, format: function (date) {
                                    var startYear = Math.floor(date.getFullYear() / 5) * 5;
                                    var endYear = startYear + 4;
                                    return startYear + " - " + endYear;
                                }}, // 5-year range
                            {unit: "year", step: 1, format: "%Y"}
                        ]
                    }
                ]
            });


            const projectID = "{{ $project->projectID }}";

            fetch(`/gantt/tasks/${projectID}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("Fetched Gantt Data:", data);
                        gantt.parse(data);
                    })
                    .catch(error => console.error("Error fetching Gantt data:", error));

            function autoFit() {
                let tasks = [];

                // Iterate over all tasks and collect them in an array
                gantt.eachTask(function (task) {
                    tasks.push(task);
                });

                if (tasks.length === 0)
                    return;

                // Find the earliest start date and latest end date
                let minDate = tasks[0].start_date;
                let maxDate = tasks[0].end_date;

                tasks.forEach(task => {
                    if (task.start_date < minDate)
                        minDate = task.start_date;
                    if (task.end_date > maxDate)
                        maxDate = task.end_date;
                });

                // Calculate the total duration in days
                const duration = gantt.calculateDuration(minDate, maxDate, "day");

                // Adjust the zoom level based on the duration
                if (duration <= 7) {
                    gantt.ext.zoom.setLevel("day");
                    updateScales("day");
                } else if (duration <= 30) {
                    gantt.ext.zoom.setLevel("week");
                    updateScales("week");
                } else if (duration <= 365) {
                    gantt.ext.zoom.setLevel("month");
                    updateScales("month");
                } else {
                    gantt.ext.zoom.setLevel("year");
                    updateScales("year");
                }
            }
            // Zoom In Function
            function zoomIn() {
                gantt.ext.zoom.zoomIn();
            }

            // Zoom Out Function
            function zoomOut() {
                gantt.ext.zoom.zoomOut();
            }

            function updateScales(viewMode) {
                if (viewMode === "day") {
                    gantt.config.scales = [
                        {unit: "week", step: 1, format: function (date) {
                                var start = gantt.date.week_start(date);
                                var end = gantt.date.add(start, 6, "day");
                                return gantt.date.date_to_str("%M %d")(start) + " - " + gantt.date.date_to_str("%M %d")(end);
                            }}, // Week range
                        {unit: "day", step: 1, format: "%d %M"} // Day row
                    ];
                } else if (viewMode === "week") {
                    gantt.config.scales = [
                        {unit: "month", step: 1, format: "%F %Y"}, // Month row
                        {unit: "week", step: 1, format: function (date) {
                                var start = gantt.date.week_start(date);
                                var end = gantt.date.add(start, 6, "day");
                                return gantt.date.date_to_str("%d")(start) + " - " + gantt.date.date_to_str("%d %M")(end);
                            }} // Week range
                    ];
                } else if (viewMode === "month") {
                    gantt.config.scales = [
                        {unit: "year", step: 1, format: "%Y"}, // Year row
                        {unit: "month", step: 1, format: "%F"} // Month row
                    ];
                } else if (viewMode === "year") {
                    gantt.config.scales = [
                        {unit: "year", step: 5, format: function (date) {
                                var startYear = Math.floor(date.getFullYear() / 5) * 5;
                                var endYear = startYear + 4;
                                return startYear + " - " + endYear;
                            }}, // 5-year range
                        {unit: "year", step: 1, format: "%Y"} // Individual years
                    ];
                }

                gantt.render();
            }

// Initialize the default scale
            updateScales("day");


// Update scales when changing view mode
            function changeViewMode(mode) {
                updateScales(mode);
                gantt.ext.zoom.setLevel(mode);
            }


            // Toggle Column Visibility
            function toggleColumn(columnName) {
                const column = gantt.config.columns.find(col => col.name === columnName);
                if (column) {
                    column.hide = !column.hide;
                    gantt.render();
                }
            }

            // Adjust column widths to fit content
            function adjustColumnWidths() {
                const columns = gantt.config.columns;
                columns.forEach(column => {
                    if (column.name === "text") {
                        column.width = 200; // Adjust as needed
                    } else if (column.name === "start_date" || column.name === "end_date") {
                        column.width = 200;
                    } else if (column.name === "duration") {
                        column.width = 100; // Adjust as needed
                    }
                });
                gantt.render();
            }

            // Event listener for task updates (when dragging tasks)
            gantt.attachEvent("onTaskUpdated", function (id, task) {
                updateTaskInDatabase(task);
            });

            // Event listener for task updates (when editing in the lightbox)
            gantt.attachEvent("onAfterTaskUpdate", function (id, task) {
                updateTaskInDatabase(task);
            });

            // Event listener for task deletion (when deleting in the lightbox)
            gantt.attachEvent("onBeforeTaskDelete", function (id) {
                // Hide the lightbox temporarily
                const lightbox = document.querySelector('.gantt_cal_light');
                const task = gantt.getTask(id);
                if (lightbox) {
                    lightbox.style.display = 'none';
                }

                // Prevent homeowner from deleting tasks
                if (userRole === "homeowner") {
                    alert("Homeowners are not allowed to delete tasks.");
                    return false;
                }

                // Prevent workers from deleting tasks
                if (userRole === "worker") {
                    alert("Workers are not allowed to delete tasks.");
                    return false;
                }

                // Prevent deletion of paid tasks
                if (task.hasTaskReport) {
                    alert("This task is paid and cannot be deleted.");
                    return false;
                }

                // Show the confirmation box
                const confirmation = confirm("Are you sure you want to delete this task?");
                if (confirmation) {
                    deleteTaskInDatabase(id);
                    return true; // Allow the deletion to proceed
                } else {
                    // Show the lightbox again if the user cancels the deletion
                    if (lightbox) {
                        lightbox.style.display = '';
                    }
                    return false; // Prevent the deletion
                }
            });

            // Update task in database
            function updateTaskInDatabase(task) {
                const projectStatus = "{{ $project->projectStatus }}";

                if (projectStatus === 'Completed') {
                    alert('Task updates are not allowed for completed projects.');
                    window.location.reload();
                    return;
                }

                if (userRole === "homeowner") {
                    alert("Homeowners are not allowed to modify tasks.");
                    window.location.reload();
                    return;
                }
                const taskData = {
                    id: task.id, // Task ID
                    text: task.text, // Task Name
                    start_date: task.start_date, // Start Date
                    end_date: task.end_date, // End Date
                    duration: task.duration, // Duration
                };

                console.log('Updating Task:', taskData); // Log the task data

                fetch('/update-task-gantt', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(taskData),
                })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    console.error('Failed to update task:', data.error);
                                    alert(data.error || 'Failed to update task. Please check the dates.'); // Show error message


                                    window.location.reload();

                                    throw new Error(data.error);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Task Updated Successfully:', data);
                        })
                        .catch(error => {
                            console.error('Error updating task:', error);

                        });
            }

            function deleteTaskInDatabase(taskId) {

                const projectStatus = "{{ $project->projectStatus }}";

                if (projectStatus === 'Completed') {
                    alert('Task deletion is not allowed for completed projects.');
                    return;
                }
                console.log('Deleting Task:', taskId); // Log the task ID

                fetch(`/delete-task/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    console.error('Failed to delete task:', data.error);
                                    throw new Error(data.error);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Task Deleted Successfully:', data);
                            // Optionally, refresh the Gantt chart after deletion
                            fetch(`/gantt/tasks/${projectID}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        gantt.clearAll();
                                        gantt.parse({data: data.data});
                                    })
                                    .catch(error => console.error("Error fetching Gantt data:", error));
                        })
                        .catch(error => console.error('Error deleting task:', error));
            }
        </script>
    </body>
</html>