<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/dashboard.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>


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

                    <hr>

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
                <button class="new-task" style="display: none;">New Task</button>

                <div class="task-table-wrapper">
                    <div class="task-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="sticky-column">Task ID</th>
                                    <th class="sticky-column">Task Name</th>
                                    <th>Worker</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>Due Date</th>
                                    <th>Duration</th>
                                    <th>Priority</th>
                                    <th>Qty</th>
                                    <th>Unit Of Measure</th>
                                    <th>Unit Price (RM)</th>
                                    <th>Cost (RM)</th>
                                    <th>Remarks</th>
                                    <th>Warranty</th>
                                </tr>
                            </thead>
                            <tbody id="task-tbody">
                            <input type="hidden" id="user-role" value="{{ auth()->user()->role }}">
                            <input type="hidden" id="project-start-date" value="{{ \Carbon\Carbon::parse($project->startDate)->format('Y-m-d') }}">
                            <input type="hidden" id="project-end-date" value="{{ \Carbon\Carbon::parse($project->endDate)->format('Y-m-d') }}">
                            @foreach($tasks as $task)
                            <tr data-task-id="{{ $task->id }}">
                                <td class="sticky-column">{{ $task->id }}</td>
                                <td class="sticky-column">{{ $task->taskName }}</td>
                                @php
                                // Ensure that 'userID' is correctly referenced in the query
                                $isHomeowner = \App\Models\Homeowner::where('userID', auth()->user()->userID)->exists();
                                $isContractor = \App\Models\Contractor::where('userID', auth()->user()->userID)->exists();
                                $isWorker = \App\Models\Worker::where('userID', auth()->user()->userID)->exists();
                                @endphp

                                <td>
                                    <div class="owner-container" data-task-id="{{ $task->id }}">
                                        <!-- Display assigned owner -->
                                        <div class="owner-icon" 
                                             @if($userRole === 'homeowner')
                                             style="cursor: not-allowed;" 
                                             title="You are a homeowner, owner selection is disabled" 
                                             @elseif($userRole === 'worker')
                                             style="cursor: not-allowed;" 
                                             title="You are a worker, owner selection is disabled" 
                                             @else 
                                             onclick="toggleDropdown(event, 'owner', '{{ $task->id }}')" 
                                             @endif>
                                            @if($task->owner)
                                            <span class="owner-circle">{{ substr($task->owner, 0, 2) }}</span>
                                            <span class="owner-name">{{ $task->owner }}</span>
                                            @else
                                            <img src="{{ asset('images/userIcon.png') }}" alt="Owner Icon" class="owner-placeholder-icon">
                                            @endif
                                        </div>

                                        <!-- Dropdown for selecting a new owner -->
                                        <div class="owner-dropdown" id="owner-dropdown-{{ $task->id }}" style="display: none;">
                                            <ul>
                                                @foreach($contractors as $contractor)
                                                <li onclick="assignOwner('{{ $task->id }}', '{{ $contractor->userName }}', 'contractor')">
                                                    <span class="contractor-icon">{{ substr($contractor->userName, 0, 2) }}</span>
                                                    {{ $contractor->userName }} (Contractor)
                                                </li>
                                                @endforeach
                                                @foreach($workers as $worker)
                                                <li onclick="assignOwner('{{ $task->id }}', '{{ $worker->userName }}', 'worker')">
                                                    <span class="worker-icon">{{ substr($worker->userName, 0, 2) }}</span>
                                                    {{ $worker->userName }} (Worker)
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>


                                <td class="status">
                                    <div class="status-container">
                                        <span class="status {{ strtolower($task['status']) }}" 
                                              id="status-{{ $task['id'] }}" 
                                              @if($userRole === 'homeowner') readonly 
                                              style="cursor: not-allowed;" 
                                              title="You are a homeowner, status selection is disabled" 
                                              @else 
                                              onclick="toggleDropdown(event, 'status', '{{ $task['id'] }}')"
                                              @endif>
                                            {{ $task['status'] ?? 'Not Started' }}
                                        </span>

                                        <!-- Start with display: none for non-homeowners -->
                                        <div class="status-dropdown" id="status-dropdown-{{ $task['id'] }}" style="display: none;">
                                            <ul>
                                                <li class="status-option not-started" onclick="updateStatus('{{ $task['id'] }}', 'Not Started')">Not Started</li>
                                                <li class="status-option working" onclick="updateStatus('{{ $task['id'] }}', 'Working')">Working</li>
                                                <li class="status-option done" onclick="updateStatus('{{ $task['id'] }}', 'Done')">Done</li>
                                                <li class="status-option on-hold" onclick="updateStatus('{{ $task['id'] }}', 'On-Hold')">On Hold</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <input type="date" 
                                           id="start-date-{{ $task['id'] }}" 
                                           value="{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '' }}" 
                                           onchange="updateTaskField('{{ $task['id'] }}', 'startDate', this.value)" 
                                           style="border: none; background-color: transparent; width: 100%;"
                                           @if($userRole === 'homeowner') readonly @endif>
                                </td>
                                <td>
                                    <input type="date" 
                                           id="due-date-{{ $task['id'] }}" 
                                           value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}" 
                                           onchange="updateTaskField('{{ $task['id'] }}', 'endDate', this.value)" 
                                           style="border: none; background-color: transparent; width: 100%;"
                                           @if($userRole === 'homeowner') readonly @endif>
                                </td>
                                <td id="duration-{{ $task['id'] }}">{{ $task['duration'] ?? '-' }} {{ $task['durationUnit'] ?? '' }}</td>
                                <td class="priority">
                                    <div class="priority-container">
                                        <span class="priority {{ strtolower($task['priority']) }}" 
                                              id="priority-{{ $task['id'] }}" 
                                              @if($userRole === 'homeowner') readonly 
                                              style="cursor: not-allowed;" 
                                              title="You are a homeowner, priority selection is disabled" 
                                              @else 
                                              onclick="toggleDropdown(event, 'priority', '{{ $task['id'] }}')" 
                                              @endif>
                                            {{ $task['priority'] ?? 'None' }}
                                        </span>

                                        <div class="priority-dropdown" 
                                             id="priority-dropdown-{{ $task['id'] }}" 
                                             @if($userRole === 'homeowner') readonly 
                                             style="display: none;" 
                                             @else 
                                             style="display: none;" 
                                             @endif>
                                            <ul>
                                                <li class="priority-option none" onclick="updatePriority('{{ $task['id'] }}', 'None')">None</li>
                                                <li class="priority-option low" onclick="updatePriority('{{ $task['id'] }}', 'Low')">Low</li>
                                                <li class="priority-option medium" onclick="updatePriority('{{ $task['id'] }}', 'Medium')">Medium</li>
                                                <li class="priority-option high" onclick="updatePriority('{{ $task['id'] }}', 'High')">High</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <input type="number" id="qty-input-{{ $task['id'] }}" value="{{ $task['qty'] }}" 
                                           min="0" onchange="updateBudget('{{ $task['id'] }}')" 
                                           style="width: 60px; border: none;" 
                                           @if($userRole === 'homeowner') readonly  disabled style="border: none; cursor: not-allowed;" title="Only contractors can edit" 
                                           @elseif ($userRole === 'worker') readonly disabled style="border: none; cursor: not-allowed;" title="Only contractors can edit" @endif>
                                </td>

                                <td>
                                    <select id="uom-input-{{ $task['id'] }}" 
                                            onchange="updateTaskField('{{ $task['id'] }}', 'uom', this.value)" 
                                            style="border: none;"
                                            @if($userRole === 'homeowner' || $userRole === 'worker') readonly disabled style="border: none; cursor: not-allowed;" title="Only contractors can edit" @endif>
                                        <option value="-" {{ $task['uom'] == '-' ? 'selected' : '' }}>-</option>
                                        <option value="sqft" {{ $task['uom'] == 'sqft' ? 'selected' : '' }}>Square Feet (sqft)</option>
                                        <option value="sqm" {{ $task['uom'] == 'sqm' ? 'selected' : '' }}>Square Meters (sqm)</option>
                                        <option value="m" {{ $task['uom'] == 'm' ? 'selected' : '' }}>Meters (m)</option>
                                        <option value="ft" {{ $task['uom'] == 'ft' ? 'selected' : '' }}>Feet (ft)</option>
                                        <option value="set" {{ $task['uom'] == 'set' ? 'selected' : '' }}>Set</option>
                                        <option value="unit" {{ $task['uom'] == 'unit' ? 'selected' : '' }}>Unit</option>
                                    </select>

                                </td>

                                <td>
                                    <input type="number" id="unitPrice-input-{{ $task['id'] }}" value="{{ $task['unitPrice'] }}" 
                                           min="0" step="0.01" onchange="updateBudget('{{ $task['id'] }}')" 
                                           style="width: 80px; border: none;" 
                                           @if($userRole === 'homeowner') readonly  disabled style="border: none; cursor: not-allowed;" title="Only contractors can edit" 
                                           @elseif ($userRole === 'worker') readonly disabled style="border: none; cursor: not-allowed;" title="Only contractors can edit" @endif>
                                </td>
                                <td>
                                    <input type="number" 
                                           id="budget-input-{{ $task['id'] }}" 
                                           value="{{ $task['budget'] }}" 
                                           disabled 
                                           style="border: none; cursor: not-allowed;" 
                                           title="Budget is auto-calculated">
                                </td>
                                <td><input type="text" id="remarks-{{ $task['id'] }}" value="{{ $task['remarks'] }}" onchange="updateTaskField('{{ $task['id'] }}', 'remarks', this.value)" style="border: none;"></td>
                                <td class="warranty-cell">
                                    @php
                                    $hasWarranty = !empty($task->warrantyNo);
                                    @endphp

                                    <div class="warranty-wrapper">
                                        @if ($userRole === 'homeowner' && !$hasWarranty)
                                        <img src="{{ asset('images/warranty1.png') }}" 
                                             id="warranty-icon-{{ $task->id }}" 
                                             data-task-id="{{ $task->id }}"
                                             title="You are a homeowner, warranty creation icon is disabled" 
                                             style="cursor: not-allowed; width: 30px; height: 30px;" />
                                        <a href="#" class="warranty-details-link" onclick="viewWarrantyDetails('{{ $task->id }}')">No Warranty</a>
                                        @elseif ($userRole === 'worker' &&!$hasWarranty)
                                        <img src="{{ asset('images/warranty1.png') }}" 
                                             id="warranty-icon-{{ $task->id }}" 
                                             data-task-id="{{ $task->id }}"
                                             title="You are a worker, warranty creation is disabled" 
                                             style="cursor: not-allowed; width: 30px; height: 30px;" />
                                        <a href="#" class="warranty-details-link" onclick="viewWarrantyDetails('{{ $task->id }}')">No Warranty</a>
                                        @elseif($hasWarranty)
                                        <img src="{{ asset('images/warranty2.png') }}" 
                                             id="warranty-icon-{{ $task->id }}" 
                                             data-task-id="{{ $task->id }}" 
                                             title="Warranty already assigned" 
                                             style="cursor: not-allowed; width: 30px; height: 30px;" />
                                        <a href="#" class="warranty-details-link" onclick="viewWarrantyDetails('{{ $task->id }}')">Warranty</a>
                                        @else
                                        <img src="{{ asset('images/warranty1.png') }}" 
                                             id="warranty-icon-{{ $task->id }}" 
                                             data-task-id="{{ $task->id }}" 
                                             onclick="toggleWarranty(this)" 
                                             title="Click to assign warranty" 
                                             style="cursor: pointer; width: 30px; height: 30px;" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                            <!-- Empty row for adding new task -->
                            <tr id="new-task-row" style="background-color: #eff6ff;" onclick="highlightRow(this)">
                                <td class="sticky-column" id="new-task-id" style="color: #949494; font-size: 12px;">Auto-generated</td>
                                <td class="sticky-column">
                                    <input type="text" id="task-name-input" placeholder="Add Task">
                                    <div id="task-input-message" style="display: none; color: #888; font-size: 12px; margin-top: 5px;">
                                        Press Enter to add task.
                                    </div>
                                </td>
                                <td> 
                                    <div class="owner-container hidden">
                                        <img src="{{ asset('images/userIcon.png') }}" alt="Owner Icon" class="owner-placeholder-icon" onclick="toggleDropdown(event, 'owner', 'new-task')">

                                    </div>
                                </td>
                                <td class="status">
                                    <div class="status-container hidden">
                                        <span class="status" id="status-new-task" onclick="toggleDropdown(event, 'status', 'new-task')">Not Started</span>
                                        <div class="status-dropdown" id="status-dropdown-new-task">
                                            <ul>
                                                <li class="status-option not-started" onclick="updateStatus('new-task', 'Not Started')">Not Started</li>
                                                <li class="status-option working" onclick="updateStatus('new-task', 'Working')">Working</li>
                                                <li class="status-option done" onclick="updateStatus('new-task', 'Done')">Done</li>
                                                <li class="status-option on-hold" onclick="updateStatus('new-task', 'On Hold')">On Hold</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td><input type="date" class="hidden" id="start-date-input" value="{{ date('Y-m-d') }}" required></td>
                                <td><input type="date" class="hidden" id="due-date-input"></td>
                                <td><input type="text" class="hidden" id="duration-input" placeholder="Duration"></td>
                                <td class="priority">
                                    <div class="priority-container hidden">
                                        <span class="priority" id="priority-new-task" onclick="toggleDropdown(event, 'priority', 'new-task')">None</span>
                                        <div class="priority-dropdown" id="priority-dropdown-new-task">
                                            <ul>
                                                <li class="priority-option none" onclick="updatePriority('new-task', 'None')">None</li>
                                                <li class="priority-option low" onclick="updatePriority('new-task', 'Low')">Low</li>
                                                <li class="priority-option medium" onclick="updatePriority('new-task', 'Medium')">Medium</li>
                                                <li class="priority-option high" onclick="updatePriority('new-task', 'High')">High</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td><input type="number" class="hidden" id="qty-input" placeholder="Quantity"></td>
                                <td>
                                    <select id="uom-input" class="hidden">
                                        <option value="-">-</option>
                                        <option value="sqft">Square Feet (sqft)</option>
                                        <option value="sqm">Square Meters (sqm)</option>
                                        <option value="m">Meters (m)</option>
                                        <option value="ft">Feet (ft)</option>
                                        <option value="set">Set</option>
                                        <option value="unit">Unit</option>
                                    </select>

                                </td>

                                <td><input type="number" class="hidden" id="unitPrice-input" placeholder="Unit Price" ></td>
                                <td><input type="number" class="hidden" id="budget-input" placeholder="Budget"></td>
                                <td><input type="text" class="hidden" id="remarks-input" placeholder="Remarks"></td>
                                <td>
                                    <input type="checkbox" id="warranty-input" class="hidden">
                                </td>
                            </tr>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-column"></td>
                                    <td class="sticky-column"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="budget-total" id="budget-total">
                                        RM{{ number_format($tasks->sum('budget'), 2) }}
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div style="background-color: #fff5f5; padding: 20px; border: 1px solid #f5c6c6; border-radius: 16px; font-family: Arial, sans-serif; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 30px;">

                    <p style="font-weight: bold; font-size: 14px; color: #b01414; margin-bottom: 10px;">
                        Important Note
                    </p>

                    <p style="font-size: 13px; color: #5f4141; margin: 0;">
                        If the start date or end date is displayed in <span style="color: red; font-weight: bold;">red</span>, it means the end date is earlier than the start date. Please correct the dates.
                    </p>

                </div>


                <!-- Close Project Button -->
                @if($role === 'contractor')
                <div class="close-project-container">
                    <button id="closeProjectBtn" class="close-project-btn" data-project-id="{{ $project->projectID }}">Close Project</button>
                </div>
                @endif

            </div>
            <div class="footer">
                <span></span>
            </div>
            <!-- Confirmation Modal -->
            <div id="closeProjectModal" class="project-modal">
                <div class="project-modal-content">
                    <h3>Confirm Project Closure</h3>
                    <p>Are you sure you want to close the project <strong>{{ $project->projectName }}</strong>?</p>

                    <table class="project-details-table">
                        <tr>
                            <th>Project ID</th>
                            <td>{{ $project->projectID }}</td>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <td>{{ $project->startDate }}</td>
                        </tr>
                        <tr>
                            <th>End Date</th>
                            <td>{{ $project->endDate }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $project->projectStatus }}</td>
                        </tr>
                    </table>

                    <div class="project-modal-actions">
                        <button id="confirmCloseBtn" class="project-confirm-btn">Confirm</button>
                        <button id="cancelCloseBtn" class="project-cancel-btn">Cancel</button>
                    </div>
                </div>
            </div>
            <div id="overlay" class="overlay"></div>
            <div id="warranty-panel" class="warranty-panel" style="display: none;">
                <div class="warranty-panel-header">
                    <h3>Warranty Details</h3>
                </div>

                <div id="task-info">
                    <div class="task-info-header">
                        <h4 id="task-name">Task Name</h4>
                        <span id="task-status" class="status-tag">Status</span>
                        <span id="task-budget" class="budget"><strong>$0.00</strong></span>
                    </div>
                    <hr>
                    <div class="task-details">
                        <div><strong>Start Date:</strong> <span id="task-start-date">N/A</span></div>
                        <div><strong>End Date:</strong> <span id="task-end-date">N/A</span></div>
                        <div><strong>Duration:</strong> <span id="task-duration">N/A</span></div>
                        <div><strong>Status:</strong> <span id="task-status-detail">N/A</span></div>
                    </div>
                    <hr>
                    <div class="warranty-info">
                        <strong>Warranty Info:</strong>
                        <div><strong>Remarks:</strong> <span id="task-remarks">N/A</span></div>
                    </div>
                </div>

                <form id="warranty-form" class="warranty-form">
                    <h4 style="padding-bottom: 20px;">Warranty Info</h4>
                    <div class="form-group">
                        <label for="warranty-start-date">Start Date:</label>
                        <input type="date" id="warranty-start-date" required>
                    </div>

                    <div class="form-group">
                        <label for="warranty-duration">Duration:</label>
                        <div style="position: relative;">
                            <input type="number" id="warranty-duration" required>
                            <select id="warranty-duration-unit" required>
                                <option value="days">Days</option>
                                <option value="months">Months</option>
                                <option value="years">Years</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="warranty-description">Description:</label>
                        <textarea id="warranty-description" rows="4" placeholder="Enter warranty description..."></textarea>
                    </div>

                    <div class="warranty-panel-actions">
                        <button type="button" class="warranty-panel-btn warranty-cancel-btn" onclick="closeWarrantyPanel()">Cancel</button>
                        <button type="submit" class="warranty-panel-btn warranty-submit-btn">Submit</button>
                    </div>
                </form>
            </div>
            <!-- Modal Overlay -->
            <div id="warranty-modal-overlay" class="modal-overlay" onclick="closeWarrantyModal()"></div>

            <!-- Warranty Modal -->
            <div id="warranty-modal" class="warranty-modal">
                <div class="warranty-modal-content">
                    <span class="close-btn" onclick="closeWarrantyModal()">&times;</span>
                    <div id="warranty-details-container" class="warranty-content">
                        @php
                        $task = \App\Models\Task::with('warranty')->where('taskID', request()->taskID)->first();
                        @endphp

                        @if($task && $task->warranty)
                        @php $warranty = $task->warranty; @endphp
                        <div class="document-section">
                            <h3 style="font-size: 17px;">Warranty Information</h3>
                            <p><strong>Warranty No</strong>: {{ $warranty->warrantyNo }}</p>
                            <p><strong>Description</strong>: {{ $warranty->description ?? 'N/A' }}</p>
                            <p><strong>Start Date</strong>: {{ $warranty->startDate ? \Carbon\Carbon::parse($warranty->startDate)->format('Y-m-d') : 'N/A' }}</p>
                            <p><strong>End Date</strong>: {{ $warranty->endDate ? \Carbon\Carbon::parse($warranty->endDate)->format('Y-m-d') : 'N/A' }}</p>
                            <p><strong>Status</strong>: {{ $warranty->status ?? 'N/A' }}</p>
                        </div>

                        <div class="document-section">
                            <h3 style="font-size: 17px;">Linked Task Information</h3>
                            <p><strong>Task ID</strong>: {{ $task->taskID }}</p>
                            <p><strong>Task Name</strong>: {{ $task->taskName }}</p>
                            <p><strong>Priority</strong>: {{ ucfirst($task->priority) }}</p>
                            <p><strong>Start Date</strong>: {{ $task->startDate ? \Carbon\Carbon::parse($task->startDate)->format('Y-m-d') : 'N/A' }}</p>
                            <p><strong>Due Date</strong>: {{ $task->endDate ? \Carbon\Carbon::parse($task->endDate)->format('Y-m-d') : 'N/A' }}</p>
                        </div>
                        @else
                        <p>No warranty details available for this task.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
        const taskNameInput = document.getElementById('task-name-input');
        const taskInputMessage = document.getElementById('task-input-message');
        let messageDisplayed = false;
        taskNameInput.addEventListener('input', function () {
        if (taskNameInput.value.trim() !== '' && !messageDisplayed) {
        taskInputMessage.style.display = 'block';
        messageDisplayed = true;
        } else if (taskNameInput.value.trim() === '') {
        taskInputMessage.style.display = 'none';
        messageDisplayed = false;
        }
        });
        // Add task on pressing Enter
        taskNameInput.addEventListener('keydown', async function (event) {
        if (event.key === 'Enter' && taskNameInput.value.trim() !== '') {
        await addTask(taskNameInput.value.trim());
        taskNameInput.value = ''; // Clear the input field
        taskInputMessage.style.display = 'none'; // Hide the message
        messageDisplayed = false;
        }
        });
        // Add task on clicking outside (blur event)
        taskNameInput.addEventListener('blur', async function () {
        if (taskNameInput.value.trim() !== '') {
        await addTask(taskNameInput.value.trim());
        taskNameInput.value = ''; // Clear the input field
        taskInputMessage.style.display = 'none'; // Hide the message
        messageDisplayed = false;
        }
        });
        });
        document.addEventListener('DOMContentLoaded', function () {
        const projectStatus = "{{ $project->projectStatus }}";
        if (projectStatus === 'Completed') {
        // Disable all input fields, buttons, and dropdowns except those in the sidebar
        document.querySelectorAll('input, select, textarea, button').forEach(element => {
        if (!element.closest('.sidebar')) { // Exclude elements inside the sidebar
        element.disabled = true;
        element.style.cursor = 'not-allowed';
        element.title = 'Modification is not allowed for completed projects.';
        }
        });
        // Disable specific buttons except those in the sidebar
        document.querySelectorAll('.edit-info-btn, .new-task, .close-project-btn').forEach(button => {
        if (!button.closest('.sidebar')) { // Exclude buttons inside the sidebar
        button.disabled = true;
        button.style.cursor = 'not-allowed';
        button.title = 'Modification is not allowed for completed projects.';
        }
        });
        // Disable dropdowns except those in the sidebar
        document.querySelectorAll('.status, .priority, .owner-icon').forEach(element => {
        if (!element.closest('.sidebar')) { // Exclude dropdowns inside the sidebar
        element.style.pointerEvents = 'none';
        element.title = 'Modification is not allowed for completed projects.';
        }
        });
        }
        });
        document.addEventListener('DOMContentLoaded', function () {
        const userRole = document.getElementById('user-role').value;
        // Disable or hide fields based on the user's role
        if (userRole === 'worker') {
        // Disable fields that workers cannot update
        document.querySelectorAll('.budget-input, .unitPrice-input, .qty-input, .uom-input').forEach(input => {
        input.disabled = true;
        input.style.cursor = 'not-allowed';
        input.title = 'You are a worker, this field is disabled';
        });
        // Hide the "Assign Owner" dropdown for workers
        document.querySelectorAll('.owner-dropdown').forEach(container => {
        container.style.display = 'none';
        });
        } else if (userRole === 'homeowner') {
        // Disable all fields except remarks for homeowners
        document.querySelectorAll('input, select').forEach(input => {
        if (!input.id.startsWith('remarks-')) { // Check if the ID starts with 'remarks-'
        input.disabled = true;
        input.style.cursor = 'not-allowed';
        input.title = 'You are a homeowner, this field is disabled';
        }
        });
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
        function calculateDuration() {
        const startDateText = document.getElementById("startDateText").innerText.trim();
        const endDateText = document.getElementById("endDateText").innerText.trim();
        const durationText = document.getElementById("durationText");
        const startDate = new Date(startDateText);
        const endDate = new Date(endDateText);
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        durationText.innerText = "Invalid Dates";
        return;
        }

        const timeDiff = endDate - startDate;
        if (timeDiff < 0) {
        durationText.innerText = "Invalid Date Range";
        return;
        }

        const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
        durationText.innerText = `${days} days`;
        }


        document.addEventListener("DOMContentLoaded", calculateDuration);
        function updateDuration(taskId) {
        const startDateInput = document.getElementById(`start-date-${taskId}`);
        const dueDateInput = document.getElementById(`due-date-${taskId}`);
        const durationCell = document.getElementById(`duration-${taskId}`);
        if (startDateInput && dueDateInput && durationCell) {
        const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
        const dueDate = dueDateInput.value ? new Date(dueDateInput.value) : null;
        if (startDate && dueDate) {
        if (dueDate < startDate) {
        // Highlight the dates in red
        startDateInput.style.color = 'red';
        dueDateInput.style.color = 'red';
        // Set duration to '-' in the UI
        durationCell.textContent = '-';
        // Do not update the database with invalid dates
        return; // Exit the function without calling updateTaskField
        } else {
        // Reset the color to default
        startDateInput.style.color = '';
        dueDateInput.style.color = '';
        // Calculate the duration
        const diffTime = dueDate - startDate;
        const diffDays = Math.ceil(diffTime / (1000 * 3600 * 24)); // Duration in days

        let duration = diffDays;
        let durationUnit = 'days';
        if (diffDays >= 365) {
        duration = (diffDays / 365).toFixed(1); // Keep one decimal point
        durationUnit = 'years';
        } else if (diffDays >= 30) {
        duration = (diffDays / 30).toFixed(1); // Keep one decimal point
        durationUnit = 'months';
        }

        // Update the UI
        durationCell.textContent = `${duration} ${durationUnit}`;
        // Update duration and durationUnit in the database
        updateTaskField(taskId, 'duration', duration);
        updateTaskField(taskId, 'durationUnit', durationUnit);
        }
        } else {
        // Reset the color to default if dates are invalid
        startDateInput.style.color = '';
        dueDateInput.style.color = '';
        // Set duration to '-' in the UI
        durationCell.textContent = '-';
        // Do not update the database with invalid dates
        return; // Exit the function without calling updateTaskField
        }
        }
        }

        document.addEventListener('DOMContentLoaded', function () {
        const projectStartDate = new Date(document.getElementById('project-start-date').value);
        const projectEndDate = new Date(document.getElementById('project-end-date').value);
        // Attach event listeners to all date inputs
        document.querySelectorAll('input[type="date"]').forEach(input => {
        let originalValue = input.value; // Store the original value

        input.addEventListener('change', function (event) {
        const taskId = input.closest('tr').dataset.taskId; // Get task ID from data attribute
        const selectedDate = new Date(this.value);
        // Validate start date
        if (this.id.startsWith('start-date-')) {
        const endDateInput = document.getElementById(`due-date-${taskId}`);
        const endDate = new Date(endDateInput.value);
        if (selectedDate > endDate) {
        alert('Start date cannot be later than the end date.');
        this.value = originalValue; // Revert to the original value
        return;
        }

        if (selectedDate < projectStartDate || selectedDate > projectEndDate) {
        alert('Start date must be within the project start and end dates.');
        this.value = originalValue; // Revert to the original value
        return;
        }
        }

        // Validate end date
        if (this.id.startsWith('due-date-')) {
        const startDateInput = document.getElementById(`start-date-${taskId}`);
        const startDate = new Date(startDateInput.value);
        if (selectedDate < startDate) {
        alert('End date cannot be earlier than the start date.');
        this.value = originalValue; // Revert to the original value
        return;
        }

        if (selectedDate < projectStartDate || selectedDate > projectEndDate) {
        alert('End date must be within the project start and end dates.');
        this.value = originalValue; // Revert to the original value
        return;
        }
        }

        // Update the original value to the new valid value
        originalValue = this.value;
        // Update duration when date changes
        updateDuration(taskId);
        // Save the valid date to the database
        updateTaskField(taskId, this.id.startsWith('start-date-') ? 'startDate' : 'endDate', this.value);
        });
        });
        });
        async function updateTaskField(taskId, field, value) {
        // Skip updating the budget field if it's a generated column
        if (field === "budget") {
        console.log('Skipping update for generated column: budget');
        return;
        }

        // Validate dates before sending to the server
        if (field === 'startDate' || field === 'endDate') {
        const startDateInput = document.getElementById(`start-date-${taskId}`);
        const dueDateInput = document.getElementById(`due-date-${taskId}`);
        const startDate = new Date(startDateInput.value);
        const dueDate = new Date(dueDateInput.value);
        if (startDate > dueDate) {
        this.value = originalValue;
        return; // Exit the function without saving
        }
        }

        try {
        const response = await fetch('{{ route('updateTask') }}', {
        method: 'PUT',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ taskId, field, value }),
        });
        const result = await response.json();
        if (!response.ok) {
        console.error(`Failed to update task: ${result.error}`);
        alert('Failed to update task. Please try again.');
        } else {
        console.log('Task updated successfully:', result);
        }
        } catch (error) {
        console.error('Error updating task:', error);
        }
        }

        function parseDate(dateString) {
        if (!dateString || dateString === 'dd/mm/yyyy') return null;
        const [day, month, year] = dateString.split('/');
        return `${year}-${month}-${day}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
        // Attach event listeners to all date inputs
        document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function (event) {
        const taskId = input.closest('tr').dataset.taskId; // Get task ID from data attribute
        updateDuration(taskId); // Update duration when date changes
        });
        });
        });
        function toggleDropdown(event, type, taskId) {
        event.stopPropagation(); // Prevent bubbling
        const dropdown = document.getElementById(`${type}-dropdown-${taskId}`);
        if (!dropdown) return;
        // Hide other dropdowns
        document.querySelectorAll(`.${type}-dropdown`).forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
        });
        // Toggle current dropdown only if it's not a homeowner
        if (dropdown.style.display === 'none') {
        dropdown.style.display = 'block';
        } else {
        dropdown.style.display = 'none';
        }
        }


        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
        document.querySelectorAll('.owner-dropdown').forEach(dropdown => {
        dropdown.style.display = 'none';
        });
        });
        function assignOwner(taskId, ownerName, ownerType) {
        fetch('{{ route("assignOwner") }}', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                taskId: taskId,
                        owner: ownerName,
                        ownerType: ownerType,
                }),
        })
                .then(response => {
                if (!response.ok) {
                return response.json().then(err => {
                throw new Error(err.message || 'Failed to assign owner.');
                });
                }
                return response.json();
                })
                .then(data => {
                if (data.success) {
                // Update the UI to reflect the new owner
                const ownerContainer = document.querySelector(`.owner-container[data-task-id="${taskId}"]`);
                const ownerIcon = ownerContainer.querySelector('.owner-icon');
                ownerIcon.innerHTML = `
                <span class="owner-circle">${ownerName.substring(0, 2)}</span>
                <span class="owner-name">${ownerName}</span>
            `;
                ownerIcon.style.cursor = 'not-allowed'; // Disable further selection
                ownerIcon.title = 'Owner is already assigned'; // Update tooltip
                } else {
                alert('Failed to assign owner.');
                }
                })
                .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Failed to assign owner.');
                });
        }


        document.querySelectorAll('input').forEach(input => {
        input.addEventListener('blur', function() {
        const taskId = this.closest('tr').getAttribute('data-task-id'); // Get task ID
        const fieldName = this.id.split('-')[0];
        const fieldValue = this.value;
        if (taskId) {
        updateTaskField(taskId, fieldName, fieldValue);
        }
        });
        });
        function updateStatus(taskId, status) {
        const statusElement = document.getElementById(`status-${taskId}`);
        if (statusElement) {
        // Update text
        statusElement.textContent = status;
        // Remove existing status-related classes
        statusElement.classList.remove('not-started', 'working', 'done', 'on-hold');
        // Add the new status class
        statusElement.classList.add(status.toLowerCase().replace(' ', '-'));
        // Update status in the database
        updateTaskField(taskId, 'status', status);
        // Close the dropdown after selection
        const dropdown = document.getElementById(`status-dropdown-${taskId}`);
        if (dropdown) {
        dropdown.style.display = 'none';
        }
        }
        }

        // Update task priority
        function updatePriority(taskId, priority) {
        const priorityElement = document.getElementById(`priority-${taskId}`);
        if (priorityElement) {
        // Update text
        priorityElement.textContent = priority;
        // Remove existing priority-related classes
        priorityElement.classList.remove('none', 'low', 'medium', 'high');
        // Add the new priority class
        priorityElement.classList.add(priority.toLowerCase());
        // Update priority in the database
        updateTaskField(taskId, 'priority', priority);
        // Close the dropdown after selection
        const dropdown = document.getElementById(`priority-dropdown-${taskId}`);
        if (dropdown) {
        dropdown.style.display = 'none';
        }
        }
        }

        function updateBudget(taskId) {
        let qty = document.getElementById(`qty-input-${taskId}`).value;
        let unitPrice = document.getElementById(`unitPrice-input-${taskId}`).value;
        let budgetField = document.getElementById(`budget-input-${taskId}`);
        qty = parseFloat(qty) || 0;
        unitPrice = parseFloat(unitPrice) || 0;
        let total = qty * unitPrice;
        budgetField.value = total.toFixed(2);
        // Update the budget in the database (if needed)
        updateTaskField(taskId, 'budget', total);
        // Update total budget displayed
        updateTotalBudget();
        }

        function updateTotalBudget() {
        let totalBudget = 0;
        document.querySelectorAll("[id^='budget-input-']").forEach(input => {
        totalBudget += parseFloat(input.value) || 0;
        });
        document.getElementById("budget-total").innerText = `RM${totalBudget.toFixed(2)}`;
        }




        document.querySelectorAll('.task-table tr').forEach(row => {
        row.addEventListener('click', function() {
        document.querySelectorAll('.task-table td').forEach(cell => cell.classList.remove('highlight'));
        this.querySelectorAll('td').forEach(cell => cell.classList.add('highlight'));
        });
        });
        // Add dynamic handling for new tasks
        document.querySelector('.new-task').addEventListener('click', () => {
        const newTaskRow = document.getElementById('new-task-row');
        newTaskRow.style.display = 'table-row';
        document.getElementById('task-name-input').focus();
        // Attach event listeners to new task's date inputs
        const startDateInput = document.getElementById('start-date-input');
        const dueDateInput = document.getElementById('due-date-input');
        startDateInput.classList.remove('hidden');
        dueDateInput.classList.remove('hidden');
        startDateInput.addEventListener('change', () => updateDuration('new-task'));
        dueDateInput.addEventListener('change', () => updateDuration('new-task'));
        console.log('New task row displayed and listeners attached.');
        });
        // Handle Enter key for creating a new task
        // Handle Enter key for creating a new task
        document.getElementById('task-name-input').addEventListener('keydown', async function (event) {
        if (event.key === 'Enter' && this.value.trim() !== '') {
        const taskName = this.value.trim();
        const projectID = '{{ $project->projectID }}';
        try {
        const response = await fetch('{{ route('createTask') }}', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ taskName, projectID }),
        });
        if (response.ok) {
        const { task } = await response.json();
        appendNewTaskToTable(task); // Add the new task to the table
        this.value = ''; // Clear the input field
        window.location.reload(); // Refresh the page after creating the task
        } else {
        const error = await response.json();
        alert('Failed to create task: ' + error.message);
        }
        } catch (error) {
        console.error('Error creating task:', error);
        alert('An error occurred while creating the task.');
        }
        }
        });
        // Handle task name update when input field loses focus (blur event)
        document.getElementById('task-name-input').addEventListener('blur', async function () {
        if (this.value.trim() !== '') {
        const taskName = this.value.trim();
        const taskId = 'new-task'; // Use the task ID once the task is created
        try {
        // Assuming you want to send the updated taskName to your backend
        const response = await fetch('{{ route('updateTask') }}', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ taskId, taskName }),
        });
        if (response.ok) {
        const { task } = await response.json();
        updateTaskField(taskId, 'taskName', taskName); // Call function to update the task field in DOM
        } else {
        const error = await response.json();
        alert('Failed to update task: ' + error.message);
        }
        } catch (error) {
        console.error('Error updating task:', error);
        alert('An error occurred while updating the task.');
        }
        }
        });
        function appendNewTaskToTable(task) {
        const taskTbody = document.getElementById('task-tbody');
        const newTaskRow = document.getElementById('new-task-row'); // Reference to the "Add Task" row

        // Create the new task row
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-task-id', task.taskID); // Ensure task ID is set
        newRow.innerHTML = `
            <td class="sticky-column">${task.taskID}</td>
            <td class="sticky-column">${task.taskName}</td>
            <td>
                <div class="owner-container" data-task-id="${task.taskID}">
                    <img src="{{ asset('images/userIcon.png') }}" alt="Owner Icon" class="owner-placeholder-icon" onclick="toggleDropdown(event, 'owner', '${task.taskID}')">
                    <div class="owner-dropdown">
                        <ul>
                            <li onclick="assignOwner('${task.taskID}', 'WX', 'Worker')">WX</li>
                            <li onclick="assignOwner('${task.taskID}', 'JD', 'Worker')">JD</li>
                        </ul>
                    </div>
                </div>
            </td>
            <td class="status">
                <span class="status" id="status-${task.taskID}" onclick="toggleDropdown(event, 'status', '${task.taskID}')">Not Started</span>
            </td>
            <td><input type="date" id="start-date-${task.taskID}" value="${task.startDate || ''}" onchange="updateTaskField('${task.taskID}', 'startDate', this.value)" style="border: none;"></td>
            <td><input type="date" id="due-date-${task.taskID}" value="${task.dueDate || ''}" onchange="updateTaskField('${task.taskID}', 'dueDate', this.value)" style="border: none;"></td>
            <td id="duration-${task.taskID}">-</td>
            <td class="priority">
                <span class="priority" id="priority-${task.taskID}" onclick="toggleDropdown(event, 'priority', '${task.taskID}')">None</span>
            </td>
            <td>${task.qty || ''}</td>
            <td>${task.uom || ''}</td>
            <td>${task.unitPrice || ''}</td>
            <td>${task.budget || ''}</td>
            <td>${task.remarks || ''}</td>
        `;
        // Append the new row before the "Add Task" row
        taskTbody.insertBefore(newRow, newTaskRow);
        // Ensure the "Add Task" row is visible at the end
        newTaskRow.style.display = 'table-row';
        }

        // Reset new task row
        function resetNewTaskRow() {
        document.getElementById('task-name-input').value = '';
        document.getElementById('start-date-input').value = '';
        document.getElementById('due-date-input').value = '';
        document.getElementById('duration-input').value = '';
        document.getElementById('qty-input').value = '';
        document.getElementById('uom-input').value = '';
        document.getElementById('unitPrice-input').value = '';
        document.getElementById('budget-input').value = '';
        document.getElementById('remarks-input').value = '';
        document.getElementById('new-task-row').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
        const taskTbody = document.getElementById('task-tbody');
        const newTaskRow = document.getElementById('new-task-row');
        // Function to ensure the Add Task row is at the bottom
        function ensureAddTaskRowAtBottom() {
        if (newTaskRow && taskTbody) {
        taskTbody.appendChild(newTaskRow); // Move the Add Task row to the bottom
        }
        }

        // Call this function after any table update
        ensureAddTaskRowAtBottom();
        // Example: Add event listeners for dynamic row changes
        document.querySelectorAll('.new-task').forEach(button => {
        button.addEventListener('click', () => {
        ensureAddTaskRowAtBottom(); // Keep Add Task row at the bottom
        });
        });
        // If dynamically appending new rows, call the function after adding a row
        document.addEventListener('taskTableUpdated', () => {
        ensureAddTaskRowAtBottom();
        });
        });
        document.addEventListener('DOMContentLoaded', function () {
        const closeProjectModal = document.getElementById('closeProjectModal');
        const confirmCloseBtn = document.getElementById('confirmCloseBtn');
        const cancelCloseBtn = document.getElementById('cancelCloseBtn');
        const closeProjectBtn = document.getElementById('closeProjectBtn');
        closeProjectModal.style.display = 'none';
        // Open the modal when clicking the "Close Project" button
        closeProjectBtn.addEventListener('click', function () {
        closeProjectModal.style.display = 'flex';
        });
        // Close the modal when clicking "Cancel"
        cancelCloseBtn.addEventListener('click', function () {
        closeProjectModal.style.display = 'none';
        });
        // Handle project closure confirmation
        confirmCloseBtn.addEventListener('click', function () {
        const projectID = closeProjectBtn.getAttribute('data-project-id'); // Ensure the project ID is dynamically fetched

        fetch(`/closeProject/${projectID}`, {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: 'Completed' })
        })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                alert('Project closed successfully!');
                location.reload(); // Reload to reflect the updated status
                } else {
                alert('Failed to close the project.');
                }
                })
                .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while closing the project.');
                })
                .finally(() => {
                closeProjectModal.style.display = 'none'; // Hide modal after request
                });
        });
        });
        function toggleWarranty(iconElement) {
        const taskId = iconElement.getAttribute('data-task-id');
        const warrantyPanel = document.getElementById('warranty-panel');
        const overlay = document.getElementById('overlay');
        // Check if the task already has a warranty
        const hasWarranty = iconElement.src.includes('warranty2.png');
        if (hasWarranty) {
        alert('Warranty already assigned. You cannot add another warranty.');
        return; // Exit the function if warranty is already assigned
        }

        if (!taskId) {
        alert('Task ID is invalid.');
        return;
        }

        // Fetch task details using the taskId
        fetch(`/getTaskDetails/${taskId}`)
                .then(response => response.json())
                .then(data => {
                if (data && data.task) {
                const task = data.task;
                const taskInfoHTML = `
                    <span class="taskName">${task.name} </span>
                    <span class="status-tag">${task.status} </span>
                    <span class="budget"><strong>RM${task.budget}</strong></span>
                    <hr>
                    <div class="task-details">
                        <table>
                            <tbody>
                                <tr>
                                    <td><strong>Start Date</strong></td>
                                    <td>: ${task.startDate || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date</strong></td>
                                    <td>: ${task.endDate || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Duration</strong></td>
                                    <td>: ${task.duration || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Remarks</strong></td>
                                    <td>: ${task.remarks || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                `;
                document.getElementById('task-info').innerHTML = taskInfoHTML;
                } else {
                alert('Failed to load task details.');
                }
                })
                .catch(error => {
                console.error('Error fetching task details:', error);
                alert('An error occurred while fetching task details.');
                });
        // Show the warranty panel and overlay
        warrantyPanel.style.display = 'block';
        warrantyPanel.style.right = '0'; // Slide panel in from the right
        overlay.style.display = 'block'; // Show overlay to dim the background
        }


        document.querySelectorAll('.warranty-cell img').forEach((img) => {
        img.addEventListener('click', function () {
        // Only call toggleWarranty for warranty1.png (tasks without warranty)
        if (this.src.includes('warranty1.png')) {
        toggleWarranty(this);
        }
        });
        });
        function updateWarranty(taskId, warrantyData) {
        fetch(`/updateWarranty/${taskId}`, {
        method: 'POST',
                headers: {
                'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                startDate: warrantyData.startDate,
                        duration: warrantyData.duration,
                        durationUnit: warrantyData.durationUnit,
                        description: warrantyData.description,
                }),
        })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                // Change the icon to warranty2.png
                const warrantyIcon = document.getElementById(`warranty-icon-${taskId}`);
                warrantyIcon.src = '{{ asset('images/warranty2.png') }}';
                warrantyIcon.style.cursor = 'not-allowed';
                warrantyIcon.title = 'Warranty already assigned';
                // Add a hyperlink for viewing warranty details
                const warrantyDetailsLink = document.createElement('a');
                warrantyDetailsLink.href = '#';
                warrantyDetailsLink.textContent = 'Warranty';
                warrantyDetailsLink.className = 'warranty-details-link';
                warrantyDetailsLink.onclick = function () {
                viewWarrantyDetails(taskId);
                };
                // Append the link next to the icon
                const warrantyCell = document.querySelector(`.warranty-cell img[data-task-id="${taskId}"]`).parentElement;
                if (warrantyCell) {
                warrantyCell.appendChild(warrantyDetailsLink);
                }
                alert('Warranty details updated successfully');
                closeWarrantyPanel();
                } else {
                alert('Failed to update warranty details: ' + data.message);
                }
                })
                .catch(error => {
                console.error('Error updating warranty:', error);
                alert('An error occurred while updating warranty details.');
                });
        }
        function closeWarrantyPanel() {
        const warrantyPanel = document.getElementById('warranty-panel');
        const overlay = document.getElementById('overlay');
        warrantyPanel.style.right = '-400px'; // Slide panel out of view
        overlay.style.display = 'none'; // Hide overlay
        clearWarrantyForm();
        }

// Remove the warranty from the task
        function removeWarranty(taskId) {
        fetch('/removeWarranty', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ taskId: taskId }),
        })
                .then(response => response.json())
                .then(data => {
                alert('Warranty removed');
                // Reset the icon to warranty1.png if warranty is removed
                const warrantyIcon = document.getElementById('warranty-icon');
                warrantyIcon.src = 'warranty1.png';
                // Remove the warranty details link
                document.getElementById('warranty-link-container').innerHTML = '';
                })
                .catch(error => {
                console.error('Error removing warranty:', error);
                });
        }

        function clearWarrantyForm() {
        document.getElementById('warranty-start-date').value = '';
        document.getElementById('warranty-duration').value = '';
        document.getElementById('warranty-duration-unit').value = 'days';
        document.getElementById('warranty-description').value = '';
        }

// Event listener for warranty form submission
        document.getElementById('warranty-form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission

        const taskId = document.querySelector('.warranty-cell img[data-task-id]:not([src*="warranty2.png"])')?.getAttribute('data-task-id');
        const warrantyData = {
        startDate: document.getElementById('warranty-start-date').value,
                duration: document.getElementById('warranty-duration').value,
                durationUnit: document.getElementById('warranty-duration-unit').value,
                description: document.getElementById('warranty-description').value,
        };
        // Automatically calculate the end date
        const durationValue = parseInt(warrantyData.duration, 10);
        const startDate = new Date(warrantyData.startDate);
        let endDate = new Date(startDate);
        if (warrantyData.durationUnit === 'days') {
        endDate.setDate(startDate.getDate() + durationValue);
        } else if (warrantyData.durationUnit === 'months') {
        endDate.setMonth(startDate.getMonth() + durationValue);
        } else if (warrantyData.durationUnit === 'years') {
        endDate.setFullYear(startDate.getFullYear() + durationValue);
        }

        warrantyData.endDate = endDate.toISOString().split('T')[0];
        // Send warranty data to the server
        updateWarranty(taskId, warrantyData);
        });
        // Function to update warranty duration dynamically
        function updateWarrantyDuration() {
        const warrantyStartDateInput = document.getElementById('warranty-start-date');
        const warrantyDurationInput = document.getElementById('warranty-duration');
        if (warrantyStartDateInput) {
        const startDate = new Date(warrantyStartDateInput.value);
        if (startDate && endDate && endDate >= startDate) {
        const diffTime = endDate - startDate;
        const diffDays = Math.ceil(diffTime / (1000 * 3600 * 24)); // Duration in days

        let duration = diffDays;
        let durationUnit = 'days';
        if (diffDays >= 365) {
        // Convert to years
        duration = (diffDays / 365).toFixed(1); // Keep one decimal point
        durationUnit = 'years';
        } else if (diffDays >= 30) {
        // Convert to months
        duration = (diffDays / 30).toFixed(1); // Keep one decimal point
        durationUnit = 'months';
        }

        // Set the duration value and automatically select the unit
        warrantyDurationInput.value = duration;
        const durationUnitDisplay = document.getElementById('warranty-duration-unit-display');
        if (durationUnitDisplay) {
        durationUnitDisplay.textContent = durationUnit; // Display the unit in text form
        }

        // Store the duration unit value for later submission
        document.getElementById('warranty-duration-unit').value = durationUnit;
        } else if (endDate < startDate) {
        alert('End date cannot be earlier than the start date.');
        warrantyDurationInput.value = ''; // Reset the duration if invalid
        }
        }
        }

// Add event listeners to the start and end date inputs to recalculate the warranty duration
        document.getElementById('warranty-start-date').addEventListener('change', updateWarrantyDuration);
        function viewWarrantyDetails(taskId) {
        fetch(`/viewWarrantyDetails/${taskId}`)
                .then(response => response.json())
                .then(data => {
                if (data.warranty && data.task) {
                let statusClass = data.warranty.status === "Active" ? "status-active" :
                        data.warranty.status === "Expired" ? "status-expired" :
                        "status-pending";
                // Format the dates to YYYY-MM-DD
                const formatDate = (dateString) => {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toISOString().split('T')[0]; // Extract the date part
                };
                document.getElementById('warranty-details-container').innerHTML = `
                    <h3 class="modal-title">Warranty & Task Details</h3>
                    
                    <div class="document-section">
                        <h3>Warranty Information</h3>
                        <table class="info-table">
                            <tr>
                                <td><img src="{{ asset('images/warranty.png') }}" class="icon"> Warranty No</td>
                                <td>: ${data.warranty.warrantyNo}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/info.png') }}" class="icon"> Description</td>
                                <td>: ${data.warranty.description ?? 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/date1.png') }}" class="icon"> Start Date</td>
                                <td>: ${formatDate(data.warranty.startDate)}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/date1.png') }}" class="icon"> End Date</td>
                                <td>: ${formatDate(data.warranty.endDate)}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/status.png') }}" class="icon"> Status</td>
                                <td>: <span class="status-badge ${statusClass}">${data.warranty.status ?? 'N/A'}</span></td>
                            </tr>
                        </table>
                    </div>

                    <div class="document-section">
                        <h3>Task Information</h3>
                        <table class="info-table">
                            <tr>
                                <td><img src="{{ asset('images/task.png') }}" class="icon"> Task ID</td>
                                <td>: ${data.task.taskID}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/taskName.png') }}" class="icon"> Task Name</td>
                                <td>: ${data.task.taskName}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/priority.png') }}" class="icon"> Priority</td>
                                <td>: ${data.task.priority ? data.task.priority.charAt(0).toUpperCase() + data.task.priority.slice(1) : 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/date2.png') }}" class="icon"> Start Date</td>
                                <td>: ${formatDate(data.task.startDate)}</td>
                            </tr>
                            <tr>
                                <td><img src="{{ asset('images/date2.png') }}" class="icon"> Due Date</td>
                                <td>: ${formatDate(data.task.endDate)}</td>
                            </tr>
                        </table>
                    </div>
                `;
                } else {
                document.getElementById('warranty-details-container').innerHTML = "<p>No warranty details available.</p>";
                }

                // Ensure modal is hidden first
                document.getElementById('warranty-modal').style.display = 'block';
                document.getElementById('warranty-modal-overlay').style.display = 'block';
                })
                .catch(error => {
                console.error('Error fetching warranty details:', error);
                });
        }

        function closeWarrantyModal() {
        document.getElementById('warranty-modal').style.display = 'none';
        document.getElementById('warranty-modal-overlay').style.display = 'none';
        }

        document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('warranty-modal').style.display = 'none';
        document.getElementById('warranty-modal-overlay').style.display = 'none';
        });


    </script>
</html>


