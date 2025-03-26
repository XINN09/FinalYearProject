<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project Cost Details - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/costDetails.css') }}">
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
                                Project Cost Details
                            </h3>
                            <input type="text" id="searchTask" placeholder="Search task..." onkeyup="filterTasks()">
                        </div>

                        <hr style="margin-bottom: 20px;">

                        <table id="costTable" class="cost-table">
                            @php
                            $isContractor = \App\Models\Contractor::where('userID', auth()->user()->userID)->exists();
                            @endphp
                            <thead>
                                <tr>
                                    <th>Task Name</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>UOM</th>
                                    <th>Cost</th>
                                    <th @if(!$isContractor) style="display: none;"  disabled @endif>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($tasks as $task)
                                <tr id="taskRow-{{ $task->taskID }}" class="task-row">
                                    <td>{{ $task->taskName }}</td>
                                    <td>
                                        <span class="view-mode">RM {{ number_format($task->unitPrice, 2) }}</span>
                                        <input type="number" class="edit-mode" value="{{ $task->unitPrice }}" style="display:none;">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $task->qty }}</span>
                                        <input type="number" class="edit-mode" value="{{ $task->qty }}" style="display:none;">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $task->uom ?: '-' }}</span>
                                        <select class="edit-mode" style="display:none;">
                                            <option value="-" {{ $task->uom == '-' ? 'selected' : '' }}>-</option>
                                            <option value="pcs" {{ $task->uom == 'pcs' ? 'selected' : '' }}>pcs</option>
                                            <option value="kg" {{ $task->uom == 'kg' ? 'selected' : '' }}>kg</option>
                                            <option value="m" {{ $task->uom == 'm' ? 'selected' : '' }}>m</option>
                                            <option value="liters" {{ $task->uom == 'liters' ? 'selected' : '' }}>liters</option>
                                        </select>
                                    </td>

                                    <td>
                                        <span class="view-mode">RM {{ number_format($task->budget, 2) }}</span>
                                        <input type="number" class="edit-mode" value="{{ $task->budget }}" style="display:none;" readonly>
                                    </td>
                                    <td @if(!$isContractor) style="display: none;"  disabled @endif>
                                        <button class="edit-btn" onclick="toggleEdit('{{ $task->taskID }}')">Edit</button>
                                        <button class="save-btn" onclick="saveTask('{{ $task->taskID }}')" style="display:none;">Save</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="foot">
                                    <td colspan="4" style="text-align: right; font-weight: bold;">Total Cost:</td>
                                    <td id="totalCost">RM 0.00</td>
                                    <td @if(!$isContractor) style="display: none;"  disabled @endif></td>
                                </tr>
                            </tfoot>
                        </table>
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

                document.addEventListener('DOMContentLoaded', calculateTotal);
                function toggleEdit(taskID) {
                const row = document.getElementById('taskRow-' + taskID);
                const viewMode = row.querySelectorAll('.view-mode');
                const editMode = row.querySelectorAll('.edit-mode');
                const editBtn = row.querySelector('.edit-btn');
                const saveBtn = row.querySelector('.save-btn');
                row.classList.toggle('editing');
                if (row.classList.contains('editing')) {
                // Switch to edit mode
                viewMode.forEach(el => el.style.display = 'none');
                editMode.forEach(el => el.style.display = 'inline');
                editBtn.style.display = 'none';
                saveBtn.style.display = 'inline';
                // Attach live budget calculation listeners
                const unitPriceInput = editMode[0]; // Unit Price input
                const qtyInput = editMode[1]; // Quantity input
                const budgetInput = editMode[3]; // Budget input (readonly)

                function calculateBudget() {
                const unitPrice = parseFloat(unitPriceInput.value) || 0;
                const qty = parseFloat(qtyInput.value) || 0;
                budgetInput.value = (unitPrice * qty).toFixed(2);
                }

                unitPriceInput.addEventListener('input', calculateBudget);
                qtyInput.addEventListener('input', calculateBudget);
                // Initial budget calculation
                calculateBudget();
                row._budgetListeners = [calculateBudget, calculateBudget];
                } else {
                // Switch to view mode
                viewMode.forEach(el => el.style.display = 'inline');
                editMode.forEach(el => el.style.display = 'none');
                editBtn.style.display = 'inline';
                saveBtn.style.display = 'none';
                if (row._budgetListeners) {
                const unitPriceInput = editMode[0];
                const qtyInput = editMode[1];
                unitPriceInput.removeEventListener('input', row._budgetListeners[0]);
                qtyInput.removeEventListener('input', row._budgetListeners[1]);
                delete row._budgetListeners;
                }
                }
                }


                function saveTask(taskID) {
                const row = document.getElementById('taskRow-' + taskID);
                const inputs = row.querySelectorAll('.edit-mode');
                const data = {
                unitPrice: parseFloat(inputs[0].value) || 0,
                        qty: parseFloat(inputs[1].value) || 0,
                        uom: inputs[2].value  // This is the <select> dropdown
                };
                fetch(`/task/${taskID}/update`, {
                method: 'POST',
                        headers: {
                        'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                }).then(response => response.json())
                        .then(result => {
                        if (result.message === 'Task updated successfully') {
                        location.reload(); // Refresh the page to show updated data
                        } else {
                        alert('Failed to update task.');
                        }
                        }).catch(error => {
                console.error('Error updating task:', error);
                alert('An error occurred. Please try again.');
                });
                }



                function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.task-row').forEach(row => {
                const budget = parseFloat(row.querySelector('td:nth-child(5) .view-mode').textContent.replace('RM', '').replace(',', '').trim());
                total += budget;
                });
                document.getElementById('totalCost').textContent = 'RM ' + total.toFixed(2);
                }

                function filterTasks() {
                const search = document.getElementById('searchTask').value.toLowerCase();
                document.querySelectorAll('.task-row').forEach(row => {
                const taskName = row.cells[0].textContent.toLowerCase();
                row.style.display = taskName.includes(search) ? '' : 'none';
                });
                }

                function disableEditButtonsIfProjectCompleted() {
                const projectStatus = "{{ $project->projectStatus }}"; // Get the project status from the backend
                if (projectStatus.toLowerCase() === 'completed') {
                // Disable all edit buttons
                document.querySelectorAll('.edit-btn').forEach(button => {
                button.disabled = true;
                button.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                button.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                });
                // Disable the edit button in the project info panel
                const editInfoButton = document.getElementById('editButton');
                if (editInfoButton) {
                editInfoButton.disabled = true;
                editInfoButton.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                editInfoButton.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                }
                }
                }

                // Call the function when the DOM is fully loaded
                document.addEventListener('DOMContentLoaded', disableEditButtonsIfProjectCompleted);
            </script>

    </body>
</html>