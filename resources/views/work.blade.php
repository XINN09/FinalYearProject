<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Work</title>
        <link rel="stylesheet" href="{{ asset('css/work.css') }}">
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
                    <!-- Breadcrumbs and Controls -->
                    <div class="page-controls">
                        <div class="breadcrumbs">
                            <h3>My Works</h3>
                        </div>
                        <div class="control-buttons">
                            <input type="text" placeholder="Search task..." class="search-bar">
                            <div class="filter-icon" onclick="">
                                <!-- Add an icon or text for the filter action -->
                                <img src="{{ asset('icon/filter.png') }}" alt="Filter" class="filter-icon-img">
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <!-- Issues Table -->
                        <table class="issues-table">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Task ID</th>
                                    <th>Task Name</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($tasks) > 0)
                                @foreach($tasks as $task)
                                @php
                                // Log the task and its assignments for debugging
                                Log::info('Task Assignments:', ['taskID' => $task->taskID, 'assignments' => $task->assignments]);
                                @endphp
                                <tr>
                                    <td>{{ $task->project->projectName ?? 'N/A' }}</td>
                                    <td>{{ $task->taskID }}</td>
                                    <td>{{ $task->taskName }}</td>
                                    <td>
                                        <div class="owner">
                                            @php
                                            // Find the owner (worker or contractor) for the task
                                            $owner = null;
                                            foreach ($task->assignments as $assignment) {
                                            if ($assignment->workerID) {
                                            $owner = $assignment->worker;  // If workerID is set, get the worker
                                            break;
                                            } elseif ($assignment->contractorID) {
                                            $owner = $assignment->contractor;  // If contractorID is set, get the contractor
                                            break;
                                            }
                                            }
                                            @endphp
                                            @if($owner)
                                            <div class="reporter">
                                                <img src="{{ asset('icon/userProfile.png') }}" alt="Profile"/>
                                                <span class="owner-name">{{ $owner->user->userName ?? 'Unknown' }}</span>
                                            </div>
                                            @else
                                            <span class="owner-name">Unassigned</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-{{ strtolower($task->status) }}">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->endDate)
                                        {{ \Carbon\Carbon::parse($task->endDate)->format('d-m-Y') }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="no-tasks">No tasks assigned to you yet.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="footer">
                    <span>Total Count: 1</span>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const searchInput = document.querySelector(".search-bar");
                const tableRows = document.querySelectorAll(".issues-table tbody tr");
                const filterIcon = document.querySelector(".filter-icon-img");
                let ascendingOrder = true;

                // Search Function
                searchInput.addEventListener("input", function () {
                    const searchText = this.value.toLowerCase();

                    tableRows.forEach(row => {
                        const taskName = row.cells[2].textContent.toLowerCase(); // Task Name is now in the 3rd column (index 2)
                        row.style.display = taskName.includes(searchText) ? "" : "none";
                    });
                });

                // Sorting Function
                filterIcon.addEventListener("click", function () {
                    const tableBody = document.querySelector(".issues-table tbody");
                    const rowsArray = Array.from(tableBody.querySelectorAll("tr"));

                    rowsArray.sort((a, b) => {
                        const nameA = a.cells[2].textContent.toLowerCase(); // Task Name is now in the 3rd column (index 2)
                        const nameB = b.cells[2].textContent.toLowerCase();

                        if (ascendingOrder) {
                            return nameA.localeCompare(nameB);
                        } else {
                            return nameB.localeCompare(nameA);
                        }
                    });

                    // Toggle order for next click
                    ascendingOrder = !ascendingOrder;

                    // Append sorted rows back to the table
                    rowsArray.forEach(row => tableBody.appendChild(row));
                });
            });
        </script>

    </body>
</html>
