<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/home.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
                <div class="container">
                    <header>
                        <h2>
                            Welcome {{ $user->userName }} 
                            <span class="role-badge {{ $user->contractor ? 'contractor' : ($user->worker ? 'worker' : ($user->homeowner ? 'homeowner' : 'default')) }}">
                                {{ $user->contractor ? 'Contractor' : ($user->worker ? 'Worker' : ($user->homeowner ? 'Homeowner' : 'Guest')) }}
                            </span>
                        </h2>

                        @if($user->contractor)
                        <p>Contractor Dashboard | Managing {{ $stats['activeProjects'] }} Active Projects</p>
                        @elseif($user->worker)
                        <p>Worker Dashboard | {{ $stats['openTasks'] }} Tasks Assigned Today</p>
                        @elseif($user->homeowner)
                        <p>Your Home Renovation Journey | {{ $stats['activeProjects'] }} Active Projects</p>
                        @else
                        <p>Quickly access your recent projects, reports, and workspaces in ALLOYMONT</p>
                        @endif
                    </header>

                    <!-- Stats Section -->
                    <div class="stats-section {{ $user->homeowner ? 'homeowner-theme' : ($user->worker ? 'worker-theme' : 'default-theme') }}">
                        <div class="stat-box">
                            <span>{{ $stats['activeProjects'] }}</span>
                            <p>Active Projects</p>
                        </div>

                        <div class="stat-box">
                            <span>{{ $stats['completedProjects'] }}</span>
                            <p>Completed Projects</p>
                        </div>

                        @if(!$user->homeowner)
                        <div class="stat-box">
                            <span>{{ $stats['openTasks'] }}</span>
                            <p>Open Tasks</p>
                        </div>
                        @endif

                        @if(!$user->homeowner)
                        <div class="stat-box">
                            <span>{{ $stats['closedTasks'] }}</span>
                            <p>Closed Tasks</p>
                        </div>
                        @endif

                        @if(!$user->worker)
                        <div class="stat-box">
                            <span>{{ $stats['openIssues'] }}</span>
                            <p>Open Issues</p>
                        </div>
                        @endif

                        @if(!$user->worker)
                        <div class="stat-box">
                            <span>{{ $stats['closedIssues'] }}</span>
                            <p>Closed Issues</p>
                        </div>
                        @endif
                    </div>

                    @if(!$user->worker)
                    <!-- Recently Viewed Projects Section -->
                    <div class="recent-projects">
                        <h3>Recently Viewed Projects</h3>
                        @if(count($recentProjects) > 0)
                        <ul>
                            @foreach($recentProjects->take(4) as $project)
                            <li>
                                <a href="{{ route('project.dashboard', ['projectID' => $project['id']]) }}">
                                    <span>{{ $project['name'] }}</span>
                                    @php
                                    if (!empty($project['dueDate'])) {
                                    $dueDate = \Carbon\Carbon::parse($project['dueDate']);
                                    $daysLeft = \Carbon\Carbon::now()->diffInDays($dueDate, false);
                                    $dueText = '';
                                    $dueClass = '';

                                    if ($daysLeft < 0) {
                                    $dueText = 'Overdue';
                                    $dueClass = 'text-red-500';
                                    } elseif ($daysLeft < 7) {
                                    $dueText = "Due in $daysLeft days";
                                    $dueClass = 'text-red-500';
                                    } elseif ($daysLeft < 30) {
                                    $dueText = "Due in " . ceil($daysLeft / 7) . " weeks";
                                    } else {
                                    $dueText = "Due in " . ceil($daysLeft / 30) . " months";
                                    }
                                    } else {
                                    $dueText = 'No Due Date';
                                    $dueClass = '';
                                    }
                                    @endphp
                                    <small class="{{ $dueClass }}">{{ $dueText }}</small>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p>No recently viewed projects.</p>
                        @endif
                    </div>
                    @endif

                    <!-- Tasks and Issues Section -->
                    <div class="tasks-issues-section">
                        @if(!$user->homeowner) 
                        <div class="tasks-issues-section">
                            <!-- Tasks Box (Always shown) -->
                            <div class="tasks-box">
                                <h3>My Tasks</h3>
                                @if(count($tasks) > 0)
                                @foreach($tasks as $task)
                                <div class="task-item">
                                    <div class="task-name">
                                        <a href="{{ route('project.dashboard', ['projectID' => $task['projectID']]) }}">{{ $task['name'] }}</a>
                                    </div>
                                    <div class="task-project">
                                        <a href="{{ route('project.dashboard', ['projectID' => $task['projectID']]) }}">{{ $task['project'] }}</a>
                                    </div>
                                    <div class="task-date">
                                        @php
                                        $daysLeft = null;
                                        if (!empty($task['endDate']) && $task['endDate'] !== 'No Due Date') {
                                        $endDate = \Carbon\Carbon::parse($task['endDate']);
                                        $daysLeft = $endDate->diffInDays(\Carbon\Carbon::now(), false);
                                        }
                                        @endphp
                                        <span style="{{ !is_null($daysLeft) && $daysLeft <= 3 && $daysLeft >= 0 ? 'color: red;' : '' }}">
                                            {{ !empty($task['endDate']) && $task['endDate'] !== 'No Due Date' ? \Carbon\Carbon::parse($task['endDate'])->format('Y-m-d') : 'No Due Date' }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                                <a href="{{ route('work') }}" class="view-more">View More</a>
                                @else
                                <div class="no-tasks">
                                    <p style="padding-top: 20px;">No tasks assigned to you yet.</p>
                                </div>
                                @endif
                            </div>
                            @if($user->worker)
                            <!-- Worker-specific Stats Section -->
                            <div class="worker-stats-section">
                                <div class="stat-row">
                                    <div class="stat-cell">
                                        <i class="fas fa-clock"></i> <!-- Clock Icon -->
                                        <div style="color: black; font-size: 14px;">Total Work Days</div>
                                        <div style="color: black; font-size: 20px;">{{ $totalWorkDays }}</div>
                                    </div>
                                </div>
                                <div class="stat-row">
                                    <div class="stat-cell">
                                        <i class="fas fa-check-circle"></i> <!-- Check Icon -->
                                        <div style="color: black; font-size: 14px;">Completed Tasks</div>
                                        <div style="color: black; font-size: 20px;">{{ $completedTasks }}</div>
                                    </div>
                                </div>
                            </div>


                            @elseif($user->contractor)
                            <!-- Issues Box (Only for Contractors) -->
                            <div class="issues-box">
                                <h3>My Issues</h3>
                                @if(count($issues) > 0)
                                @foreach($issues as $issue)
                                <div class="task-item">
                                    <div class="task-name">
                                        <a href="{{ route('issues') }}">{{ $issue->issuesName }}</a>
                                    </div>
                                    <div class="task-project">
                                        <a href="{{ route('issues') }}">{{ $issue->issuesStatus }}</a>
                                    </div>
                                    <div class="task-date">
                                        @if (!empty($issue->dueDate) && $issue->dueDate !== 'No Due Date')
                                        @php
                                        $dueDate = \Carbon\Carbon::parse($issue->dueDate);
                                        $daysLeft = $dueDate->diffInDays(\Carbon\Carbon::now(), false);
                                        @endphp
                                        <span style="{{ $daysLeft <= 3 && $daysLeft >= 0 ? 'color: red;' : '' }}">
                                            {{ $dueDate->format('Y-m-d') }}
                                        </span>
                                        @else
                                        <span>No Due Date</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                <a href="{{ route('issues') }}" class="view-more">View More</a>
                                @else
                                <div class="no-issues">
                                    <p style="padding-top: 20px;">No issues assigned to you yet.</p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @elseif($user->homeowner)
                        <!-- Homeowner-specific Sections -->
                        <div class="upcoming-tasks">
                            <h3 style="padding-bottom: 20px; font-size: 18px; color: black;">Upcoming Tasks</h3>
                            @php
                            function getPriorityColor($priority) {
                            return match ($priority) {
                            'Low' => '#41c5f8',
                            'Medium' => '#6a5acd',
                            'High' => '#4b0082',
                            default => '#808080',
                            };
                            }
                            @endphp

                            @if($tasks->count() > 0)
                            @foreach($tasks as $task)
                            <div class="task-card" style="border-left: 5px solid {{ getPriorityColor($task->priority) }};">
                                <div class="task-header">
                                    <span class="task-title">{{ $task->taskName }}</span>
                                    <span class="task-date">{{ \Carbon\Carbon::parse($task->startDate)->format('Y-m-d') }}</span>
                                </div>
                                <div class="task-body">
                                    <p class="task-remarks">{{ $task->remarks ?: '-' }}</p>
                                </div>
                                <div class="task-footer">
                                    @php
                                    $assignedPeople = $groupedAssignments[$task->taskID] ?? [];
                                    @endphp
                                    @foreach($assignedPeople as $person)
                                    <span class="task-avatar">{{ strtoupper(substr($person, 0, 1)) }}</span>
                                    <span class="task-name">{{ $person }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="no-tasks">
                                <p>No upcoming tasks in the next 2 weeks.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Issues Box: Show project documents -->
                        <div class="issues-box">
                            <h3>Project Documents</h3>
                            @php
                            $documents = \App\Models\Documents::whereIn('projectID', $recentProjects->pluck('id'))->take(3)->get();
                            $fileIcons = [
                            'pdf'  => asset('images/pdf-icon.png'),
                            'doc'  => asset('images/doc-icon.png'),
                            'docx' => asset('images/docx-icon.png'),
                            'xls'  => asset('images/xls-icon.png'),
                            'xlsx' => asset('images/xlsx-icon.png'),
                            'jpg'  => asset('images/jpg-icon.png'),
                            'jpeg' => asset('images/jpeg-icon.png'),
                            'png'  => asset('images/png-icon.png'),
                            'txt'  => asset('images/txt-icon.png'),
                            ];
                            $fileColors = [
                            'doc'  => '#cff3ff',   // Light Blue
                            'docx' => '#ddebff',   // Light Light Blue
                            'txt'  => '#dbffe8',   // Light Light Blue
                            'pdf'  => '#ffe9e9',   // Light Red
                            'xls'  => '#e7ffdb',   // Light Green
                            'xlsx' => '#dbffe8',   // Light Light Green
                            'png'  => '#e7ffdb',   // Light Light Green
                            'jpg'  => '#ffebd7',   // Light Yellow
                            'jpeg' => '#ffe9e9',   // Light Yellow
                            ];
                            @endphp

                            @if(count($documents) > 0)
                            @foreach($documents as $document)
                            @php
                            $fileExtension = pathinfo($document->documentName, PATHINFO_EXTENSION);
                            $icon = $fileIcons[$fileExtension] ?? asset('images/default-icon.png');
                            $bgColor = $fileColors[$fileExtension] ?? '#F0F0F0';
                            @endphp

                            <div class="document-item">
                                <div class="document-icon" style="background-color: {{ $bgColor }};">
                                    <img src="{{ $icon }}" alt="{{ $fileExtension }} icon">
                                </div>

                                <div class="document-info">
                                    <a href="{{ route('document.project', ['projectID' => $document->projectID]) }}" class="document-name">
                                        {{ $document->documentName }}
                                    </a>
                                    <div class="document-meta">
                                        <span class="file-type">{{ strtoupper($fileExtension) }}</span>
                                        <span class="file-date">{{ $document->created_at->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="no-issues">
                                <p style="padding-top: 20px;">No documents uploaded yet.</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('../js/sidebar.js') }}"></script>
    </body>
</html>