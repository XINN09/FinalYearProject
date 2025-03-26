<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Mail\WorkerInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ReceiptUploadNotification;
use App\Mail\ReceiptRejectionNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use App\Models\Contractor;
use App\Models\Homeowner;
use App\Models\Worker;
use App\Models\ContractorWorker;
use App\Models\Assignment;
use App\Models\Documents;
use App\Models\Warranty;
use App\Models\WarrantyRequest;
use App\Models\Issues;
use App\Models\ServiceReport;
use App\Models\Report;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller {

    /**
     * Display the dashboard with tasks.
     *
     * @return \Illuminate\View\View
     */
    public function home() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        \Log::info('Authenticated User:', ['user' => $user]);

        $role = $user->role;

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Initialize stats with default values
        $stats = [
            'activeProjects' => 0,
            'completedProjects' => 0,
            'openTasks' => 0,
            'closedTasks' => 0,
            'openIssues' => 0,
            'closedIssues' => 0,
        ];

        if ($user->homeowner) {
            $homeowner = $user->homeowner;

            // Corrected query to fetch issues linked to homeowner projects
            $issues = Issues::join('warranty_requests', 'issues.requestID', '=', 'warranty_requests.requestID')
                    ->join('warranties', 'warranty_requests.warrantyNo', '=', 'warranties.warrantyNo')
                    ->join('tasks', 'warranties.warrantyNo', '=', 'tasks.warrantyNo')
                    ->join('projects', 'tasks.projectID', '=', 'projects.projectID')
                    ->where('projects.ownerID', $homeowner->ownerID)
                    ->get();

            $openIssues = $issues->where('issuesStatus', '!=', 'completed')->count();
            $closedIssues = $issues->where('issuesStatus', 'completed')->count();

            $stats = [
                'activeProjects' => Project::where('ownerID', $homeowner->ownerID)->where('projectStatus', 'Active')->count(),
                'completedProjects' => Project::where('ownerID', $homeowner->ownerID)->where('projectStatus', 'Completed')->count(),
                'openTasks' => Task::join('projects', 'tasks.projectID', '=', 'projects.projectID')
                        ->where('projects.ownerID', $homeowner->ownerID)
                        ->whereIn('tasks.status', ['Not Started', 'Working', 'On Hold'])
                        ->count(),
                'closedTasks' => Task::join('projects', 'tasks.projectID', '=', 'projects.projectID')
                        ->where('projects.ownerID', $homeowner->ownerID)
                        ->where('tasks.status', 'Done')
                        ->count(),
                'openIssues' => $openIssues,
                'closedIssues' => $closedIssues,
            ];

            $projects = Project::where('ownerID', $homeowner->ownerID)->get();

            $tasks = Task::join('projects', 'tasks.projectID', '=', 'projects.projectID')
                    ->where('projects.ownerID', $homeowner->ownerID)
                    ->whereNotNull('tasks.startDate') // Ensure startDate is not null
                    ->whereBetween('tasks.startDate', [now(), now()->addWeeks(2)]) // Fetch tasks in next 2 weeks
                    ->orderBy('tasks.startDate', 'asc') // Sort by soonest start date
                    ->take(3)
                    ->get(['tasks.taskID', 'tasks.taskName', 'tasks.startDate', 'tasks.priority', 'tasks.remarks']);

            // Fetch assignments and join with worker, contractor, and user tables
            $assignments = Assignment::leftJoin('workers', 'assignments.workerID', '=', 'workers.workerID')
                    ->leftJoin('contractors', 'assignments.contractorID', '=', 'contractors.contractorID')
                    ->leftJoin('users as workerUsers', 'workers.userID', '=', 'workerUsers.userID')
                    ->leftJoin('users as contractorUsers', 'contractors.userID', '=', 'contractorUsers.userID')
                    ->whereIn('assignments.taskID', $tasks->pluck('taskID'))
                    ->select(
                            'assignments.taskID',
                            'workerUsers.userName as workerName',
                            'contractorUsers.userName as contractorName'
                    )
                    ->get();

// Process the assignments to group by taskID and combine worker & contractor names
            $groupedAssignments = [];
            foreach ($assignments as $assignment) {
                $taskID = $assignment->taskID;
                if (!isset($groupedAssignments[$taskID])) {
                    $groupedAssignments[$taskID] = [];
                }
                if ($assignment->workerName) {
                    $groupedAssignments[$taskID][] = $assignment->workerName;
                }
                if ($assignment->contractorName) {
                    $groupedAssignments[$taskID][] = $assignment->contractorName;
                }
            }

            // Fetch documents related to projects
            $documents = Documents::whereIn('projectID', $projects->pluck('projectID'))->get();

            return view('home', compact('user', 'role', 'recentProjects', 'issues', 'stats', 'documents', 'tasks', 'groupedAssignments'));
        }

        if ($user->contractor) {
            $contractor = $user->contractor;

            $role = $user->role;

            // Fetch issues related to this contractor's projects
            $issues = Issues::join('warranty_requests', 'issues.requestID', '=', 'warranty_requests.requestID')
                    ->join('warranties', 'warranty_requests.warrantyNo', '=', 'warranties.warrantyNo')
                    ->join('tasks', 'warranties.warrantyNo', '=', 'tasks.warrantyNo')
                    ->join('projects', 'tasks.projectID', '=', 'projects.projectID')
                    ->where('projects.contractorID', $contractor->contractorID)
                    ->get();

            $openIssues = $issues->where('issuesStatus', '!=', 'completed')->count();
            $closedIssues = $issues->where('issuesStatus', 'completed')->count();

            $stats = [
                'activeProjects' => Project::where('contractorID', $contractor->contractorID)->where('projectStatus', 'Active')->count(),
                'completedProjects' => Project::where('contractorID', $contractor->contractorID)->where('projectStatus', 'Completed')->count(),
                'openTasks' => Assignment::join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                        ->where('assignments.contractorID', $contractor->contractorID)
                        ->whereIn('tasks.status', ['Not Started', 'Working', 'On Hold'])
                        ->count(),
                'closedTasks' => Assignment::join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                        ->where('assignments.contractorID', $contractor->contractorID)
                        ->where('tasks.status', 'Done')
                        ->count(),
                'openIssues' => $openIssues,
                'closedIssues' => $closedIssues,
            ];

            $tasks = Assignment::with(['task.project'])
                    ->where('contractorID', $contractor->contractorID)
                    ->orderBy('created_at', 'desc')
                    ->take(2)
                    ->get()
                    ->map(function ($assignment) {
                return [
            'name' => $assignment->task->taskName ?? 'N/A',
            'project' => $assignment->task->project->projectName ?? 'N/A',
            'projectID' => $assignment->task->project->projectID ?? null,
            'endDate' => !empty($assignment->task->endDate) ? Carbon::parse($assignment->task->endDate)->format('Y-m-d') : 'No Due Date',
                ];
            });

            return view('home', compact('user', 'role', 'stats', 'tasks', 'issues', 'recentProjects'));
        }

        if ($user->worker) {
            $worker = $user->worker;

            $role = $user->role;

            // Calculate tasks assigned today for the worker
            $tasksAssignedToday = Assignment::where('workerID', $worker->workerID)
                    ->whereDate('assignDateTime', today())
                    ->count();

            $stats = [
                'activeProjects' => 0, // Workers do not have direct project assignments
                'completedProjects' => 0,
                'openTasks' => Assignment::where('workerID', $worker->workerID)
                        ->join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                        ->whereIn('tasks.status', ['Not Started', 'Working', 'On Hold'])
                        ->count(),
                'closedTasks' => Assignment::where('workerID', $worker->workerID)
                        ->join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                        ->where('tasks.status', 'Done')
                        ->count(),
                'tasksAssignedToday' => $tasksAssignedToday, // Add this to stats
                'openIssues' => 0, // Placeholder
                'closedIssues' => 0, // Placeholder
            ];

            $tasks = Assignment::with(['task.project'])
                    ->where('workerID', $worker->workerID)
                    ->orderBy('created_at', 'desc')
                    ->take(2)
                    ->get()
                    ->map(function ($assignment) {
                return [
            'name' => $assignment->task->taskName ?? 'N/A',
            'project' => $assignment->task->project->projectName ?? 'N/A',
            'projectID' => $assignment->task->project->projectID ?? null,
            'endDate' => !empty($assignment->task->endDate) ? Carbon::parse($assignment->task->endDate)->format('Y-m-d') : 'No Due Date',
                ];
            });

            // Calculate Total Work Days
            $totalWorkDays = Assignment::where('workerID', $worker->workerID)
                    ->join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                    ->where('tasks.status', 'Done')
                    ->sum('tasks.duration');
            // Calculate Completed Tasks
            $completedTasks = Assignment::where('workerID', $worker->workerID)
                    ->join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                    ->where('tasks.status', 'Done')
                    ->count();

            $issues = []; // Placeholder for issues

            return view('home', compact('user', 'role', 'stats', 'tasks', 'issues', 'recentProjects', 'totalWorkDays', 'completedTasks'));
        }

        // If user has no recognized role, show homepage with default stats
        return view('home', compact('user', 'stats', 'recentProjects'));
    }

    public function getRecentProjects($userId) {
        // For homeowners, get projects where the homeowner is associated
        if ($homeowner = Homeowner::where('userID', $userId)->first()) {
            return Project::where('ownerID', $homeowner->ownerID)
                            ->where('projectStatus', '!=', 'Completed') // Exclude completed projects
                            ->orderBy('updated_at', 'desc')
                            ->take(10)
                            ->get(['projectID', 'projectName', 'updated_at', 'endDate'])
                            ->map(function ($project) {
                                return [
                            'id' => $project->projectID,
                            'name' => $project->projectName,
                            'lastViewed' => $project->updated_at->format('m-d-Y'),
                            'dueDate' => $project->endDate ?? null,
                                ];
                            });
        }

        // For contractors, get projects where the contractor is associated
        if ($contractor = Contractor::where('userID', $userId)->first()) {
            return Project::where('contractorID', $contractor->contractorID)
                            ->where('projectStatus', '!=', 'Completed') // Exclude completed projects
                            ->orderBy('updated_at', 'desc')
                            ->take(10)
                            ->get(['projectID', 'projectName', 'updated_at', 'endDate'])
                            ->map(function ($project) {
                                return [
                            'id' => $project->projectID,
                            'name' => $project->projectName,
                            'lastViewed' => $project->updated_at->format('m-d-Y'),
                            'dueDate' => $project->endDate ?? null,
                                ];
                            });
        }

        // For workers, get projects where the worker is assigned tasks
        if ($worker = Worker::where('userID', $userId)->first()) {
            $assignedTasks = Assignment::where('workerID', $worker->workerID)
                    ->pluck('taskID')
                    ->toArray();

            return Task::whereIn('taskID', $assignedTasks)
                            ->join('projects', 'tasks.projectID', '=', 'projects.projectID')
                            ->where('projects.projectStatus', '!=', 'Completed') // Exclude completed projects
                            ->select('projects.projectID', 'projects.projectName', 'projects.updated_at', 'projects.endDate')
                            ->distinct('projects.projectID')
                            ->orderBy('projects.updated_at', 'desc')
                            ->take(10)
                            ->get()
                            ->map(function ($project) {
                                return [
                            'id' => $project->projectID,
                            'name' => $project->projectName,
                            'lastViewed' => $project->updated_at->format('m-d-Y'),
                            'dueDate' => $project->endDate ?? null,
                                ];
                            });
        }

        // Return an empty array if no projects are found for the user
        return [];
    }

    public function getCompletedProjects() {
        $user = Auth::user();

        if ($user->homeowner) {
            $homeowner = $user->homeowner;
            $completedProjects = Project::where('ownerID', $homeowner->ownerID)
                    ->where('projectStatus', 'Completed')
                    ->get(['projectID as id', 'projectName as name']);
        } elseif ($user->contractor) {
            $contractor = $user->contractor;
            $completedProjects = Project::where('contractorID', $contractor->contractorID)
                    ->where('projectStatus', 'Completed')
                    ->get(['projectID as id', 'projectName as name']);
        } elseif ($user->worker) {
            $worker = $user->worker;
            $assignedTasks = Assignment::where('workerID', $worker->workerID)
                    ->pluck('taskID')
                    ->toArray();

            $completedProjects = Task::whereIn('taskID', $assignedTasks)
                    ->join('projects', 'tasks.projectID', '=', 'projects.projectID')
                    ->where('projects.projectStatus', 'Completed')
                    ->select('projects.projectID as id', 'projects.projectName as name')
                    ->distinct()
                    ->get();
        } else {
            $completedProjects = [];
        }

        return response()->json($completedProjects);
    }

    public function createProject(Request $request) {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'projectName' => 'required|string|max:40',
                'projectAddress' => 'required|string|max:100',
                'projectDescription' => 'nullable|string|max:100',
                'startDate' => 'required|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                    ], [
                'projectName.required' => 'The project name is required.',
                'projectName.max' => 'The project name must not exceed 40 characters.',
                'projectAddress.required' => 'The project address is required.',
                'projectAddress.max' => 'The project address must not exceed 100 characters.',
                'projectDescription.max' => 'The project description must not exceed 100 characters.',
                'startDate.required' => 'The start date is required.',
                'endDate.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]);

            // Generate unique projectID
            // Generate unique projectID
            $year = now()->format('y'); // Get last two digits of the year (e.g., "25")
            $lastProject = Project::where('projectID', 'LIKE', "P{$year}%")
                    ->orderBy('projectID', 'desc')
                    ->first();

            if ($lastProject) {
                // Extract numeric part correctly (starting from index 2)
                $lastNumber = (int) substr($lastProject->projectID, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1; // Start numbering from 001
            }

            // Ensure the format P<YY><NNN>
            $projectID = 'P' . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Get the logged-in user
            $user = Auth::user();
            $contractor = $user->contractor;

            if (!$contractor) {
                return response()->json(['message' => 'Contractor not found for the user.'], 400);
            }

            // Create the project
            $project = Project::create([
                        'projectID' => $projectID,
                        'projectName' => $validatedData['projectName'],
                        'projectAddress' => $validatedData['projectAddress'],
                        'projectDesc' => $validatedData['projectDescription'],
                        'startDate' => $validatedData['startDate'],
                        'endDate' => $validatedData['endDate'],
                        'contractorID' => $contractor->contractorID,
                        'ownerID' => null,
                        'projectStatus' => 'Active',
            ]);

            return response()->json(['message' => 'Project created successfully', 'data' => $project], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function updateProject(Request $request, $id) {
        $project = Project::findOrFail($id);
        if ($project->projectStatus === 'Completed') {
            return response()->json(['success' => false, 'message' => 'Modification is not allowed for completed projects.'], 403);
        }
        $project->update($request->only(['projectName', 'startDate', 'endDate', 'projectAddress', 'projectDesc']));
        return response()->json(['success' => true]);
    }

    public function getProjects() {
        $projects = Project::orderBy('projectName', 'asc')->get(['projectID', 'projectName']);
        return response()->json(['projects' => $projects]);
    }

    public function projectDashboard($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $role = $user->role;
        $userRole = $user->role;

        if ($user->contractor()->exists()) {
            $role = 'contractor';
        } elseif ($user->homeowner()->exists()) {
            $role = 'homeowner';
        } elseif ($user->worker()->exists()) {
            $role = 'worker';
        }


        // Check if user is a contractor
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        // Check if user is a homeowner
        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;
        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        // Check if user is a worker
        $worker = $user->worker;
        $workerProject = null;
        if ($worker) {
            // Find tasks assigned to the worker
            $assignedTasks = Assignment::where('workerID', $worker->workerID)
                    ->pluck('taskID')
                    ->toArray();

            // Find the project associated with the assigned tasks
            $workerProject = Task::whereIn('taskID', $assignedTasks)
                    ->where('projectID', $projectID)
                    ->exists();
        }

        // Allow access if the user is a contractor, homeowner, or worker assigned to tasks in the project
        if (!$contractorProject && !$homeownerProject && !$workerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        // Get the project (either from contractor, homeowner, or worker check)
        $project = $contractorProject ?? $homeownerProject ?? Project::find($projectID);
        $currentProject = $project;
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Fetch tasks for the project
        $tasks = Task::leftJoin('assignments', 'tasks.taskID', '=', 'assignments.taskID')
                ->leftJoin('workers', 'assignments.workerID', '=', 'workers.workerID')
                ->leftJoin('users as workerUsers', 'workers.userID', '=', 'workerUsers.userID')
                ->leftJoin('contractors', 'assignments.contractorID', '=', 'contractors.contractorID')
                ->leftJoin('users as contractorUsers', 'contractors.userID', '=', 'contractorUsers.userID')
                ->leftJoin('warranties', 'tasks.warrantyNo', '=', 'warranties.warrantyNo')
                ->where('tasks.projectID', $project->projectID)
                ->get([
            'tasks.taskID as id',
            'tasks.taskName',
            'tasks.status',
            'tasks.startDate as start_date',
            'tasks.endDate as due_date',
            'tasks.duration',
            'tasks.durationUnit',
            'tasks.priority',
            'tasks.qty',
            'tasks.uom',
            'tasks.unitPrice',
            'tasks.budget',
            'tasks.remarks',
            'warranties.warrantyNo',
            DB::raw("COALESCE(workerUsers.userName, contractorUsers.userName) as owner"),
            DB::raw("COALESCE(assignments.workerID, assignments.contractorID) as ownerID"), // Add ownerID for reference
        ]);

        foreach ($tasks as $task) {
            $task->start_date = $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : null;
            $task->due_date = $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : null;
        }



        // Fetch only the contractor who created the project
        $contractors = User::whereHas('contractor', function ($query) use ($project) {
                    $query->where('contractorID', $project->contractorID);
                })->select('userID', 'userName')->get();

        // Fetch workers linked to the contractor
        $workers = ContractorWorker::with('worker.user')
                ->where('contractorID', $project->contractorID)
                ->whereNotNull('workerID') // Ensure the workerID is not null
                ->get()
                ->pluck('worker.user') // Extract the user details of the workers
                ->filter(); // Remove null values

        return view('dashboard', [
            'project' => $project,
            'currentProject' => $currentProject,
            'tasks' => $tasks,
            'recentProjects' => $recentProjects,
            'workers' => $workers,
            'contractors' => $contractors,
            'role' => $role,
            'userRole' => $userRole,
            'startDate' => $project->startDate,
            'endDate' => $project->endDate,
        ]);
    }

    public function getProjectOwner($projectID) {
        $project = Project::findOrFail($projectID);

        if ($project->ownerID) {
            $owner = Homeowner::find($project->ownerID);
            $user = User::find($owner->userID);

            return response()->json([
                        'owner' => [
                            'userName' => $user->userName,
                            'email' => $user->email,
                        ]
            ]);
        }

        return response()->json(['owner' => null]);
    }

    public function dashboard() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the contractor associated with the user
        $contractor = $user->contractor;

        if (!$contractor) {
            return redirect()->route('errorPage')->with('error', 'Contractor not found for the user.');
        }

        // Fetch recent projects associated with the contractor
        $recentProjects = $this->getRecentProjects($contractor->contractorID);

        // Example tasks with expanded information (normally, you'd fetch these from a database)
        $tasks = [
            [
                'id' => 1,
                'name' => 'Task 1',
                'owner' => 'WX',
                'status' => 'Working',
                'priority' => 'Low',
                'start_date' => '1 Sep',
                'due_date' => '4 Sep',
                'duration' => '3 days',
                'qty' => '1',
                'uom' => '',
                'unitPrice' => '50',
                'budget' => '100',
                'remarks' => 'Initial task setup',
            ],
        ];

        return view('dashboard', compact('tasks', 'recentProjects'));
    }

    public function createTask(Request $request) {
        $user = Auth::user();

        // Check if the user is a contractor
        if (!$user || !$user->contractor) {
            return response()->json(['error' => 'Unauthorized. Only contractors can create tasks.'], 403);
        }

        $project = Project::findOrFail($request->projectID);

        // Check if the project status is "Completed"
        if ($project->projectStatus === 'Completed') {
            return response()->json(['error' => 'Task creation is not allowed for completed projects.'], 403);
        }


        $request->validate([
            'taskName' => 'required|string|max:30',
            'projectID' => 'required|string|exists:projects,projectID',
        ]);

        // Generate taskID
        $year = now()->format('y');
        $latestTask = Task::where('taskID', 'LIKE', "T$year%")
                ->orderBy('taskID', 'desc')
                ->first();

        $taskNumber = $latestTask ? (int) substr($latestTask->taskID, 3) + 1 : 1;
        $taskID = 'T' . $year . str_pad($taskNumber, 4, '0', STR_PAD_LEFT);

        // Create the task
        $task = Task::create([
                    'taskID' => $taskID,
                    'taskName' => $request->taskName,
                    'status' => 'Not Started',
                    'startDate' => null,
                    'endDate' => null,
                    'duration' => 0,
                    'durationUnit' => 'days',
                    'priority' => 'None',
                    'qty' => 1,
                    'uom' => '',
                    'unitPrice' => 0,
                    'remarks' => null,
                    'projectID' => $request->projectID,
        ]);

        // Retrieve contractor info
        $project = Project::findOrFail($request->projectID);
        $contractor = Contractor::find($project->contractorID);

        return response()->json([
                    'task' => $task,
                    'contractor' => $contractor ? $contractor->name : 'No contractor assigned'
                        ], 201);
    }

    public function updateTask(Request $request) {
        $user = Auth::user();

        // Check if the user is authorized
        if (!$user) {
            return response()->json(['error' => 'Unauthorized. User not found.'], 403);
        }


        $taskId = $request->input('taskId');

        $task = Task::findOrFail($taskId);
        $project = Project::findOrFail($task->projectID);

        $field = $request->input('field');
        $value = $request->input('value');

        // Check if the project status is "Completed"
        if ($project->projectStatus === 'Completed') {
            return response()->json(['error' => 'Task updates are not allowed for completed projects.'], 403);
        }

        // Log the request payload for debugging
        \Log::info('Update Task Request:', [
            'taskId' => $taskId,
            'field' => $field,
            'value' => $value,
        ]);

        // Define allowed fields for each role
        $allowedFields = [
            'homeowner' => ['remarks'], // Homeowners can only update remarks
            'worker' => ['status', 'startDate', 'endDate', 'priority', 'remarks', 'duration', 'durationUnit', 'start', 'end', 'due'], // Workers can update specific fields
            'contractor' => ['status', 'startDate', 'endDate', 'priority', 'remarks', 'qty', 'uom', 'unitPrice', 'budget', 'duration', 'durationUnit', 'start', 'end', 'due'], // Contractors can update all fields
        ];

        // Get the user's role
        $userRole = $user->role;

        // Log the user's role
        \Log::info('User Role:', ['role' => $userRole]);

        // Check if the field is allowed for the user's role
        if (!in_array($field, $allowedFields[$userRole])) {
            \Log::error('Unauthorized Field Update Attempt:', [
                'userRole' => $userRole,
                'field' => $field,
                'allowedFields' => $allowedFields[$userRole],
            ]);
            return response()->json(['error' => 'Unauthorized. You are not allowed to update this field.'], 403);
        }

        // Validate date fields
        if ($field === 'startDate' || $field === 'endDate') {
            $request->validate([
                'value' => 'nullable|date_format:Y-m-d',
            ]);
        }


        if (!$task) {
            \Log::error('Task not found:', ['taskId' => $taskId]);
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Update the field in the task
        $task->$field = $value;
        $task->save();

        \Log::info('Task updated successfully:', ['taskId' => $taskId, 'field' => $field, 'value' => $value]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request) {
        try {
            $user = Auth::user();

            // Check if the user is authorized (not a homeowner)
            if (!$user || $user->role === 'homeowner') {
                return response()->json(['error' => 'Unauthorized. Homeowners cannot update tasks.'], 403);
            }

            \Log::info('Update request received', $request->all());
            $task = Task::findOrFail($request->taskId);
            $task->{$request->field} = $request->value;
            $task->save();

            return response()->json(['message' => 'Task updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating task', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating task', 'error' => $e->getMessage()], 500);
        }
    }

    public function assignOwner(Request $request) {
        try {
            \Log::info('Assign Owner Request Received:', $request->all()); // Log the request payload

            $user = Auth::user();

            // Restrict homeowners
            if (!$user || $user->role === 'homeowner') {
                \Log::warning('Unauthorized access attempt by homeowner.'); // Log unauthorized access
                return response()->json(['error' => 'Unauthorized. Homeowners cannot assign owners.'], 403);
            }

            $validatedData = $request->validate([
                'taskId' => 'required|exists:tasks,taskID',
                'owner' => 'required|string', // This is the userName
                'ownerType' => 'required|string|in:worker,contractor',
            ]);

            $taskId = $request->input('taskId');
            $task = Task::where('taskID', $validatedData['taskId'])->firstOrFail();
            $project = Project::findOrFail($task->projectID);

            // Check if the project status is "Completed"
            if ($project->projectStatus === 'Completed') {
                return response()->json(['error' => 'Owner assignment is not allowed for completed projects.'], 403);
            }




            \Log::info('Validated Data:', $validatedData); // Log validated data


            \Log::info('Task Found:', ['task' => $task]); // Log the task details

            $data = [
                'owner' => $validatedData['owner'], // Store the userName for display
                'owner_type' => $validatedData['ownerType'],
            ];

            if ($validatedData['ownerType'] === 'worker') {
                // Fetch the workerID from the workers table based on the userName
                $worker = \App\Models\Worker::whereHas('user', function ($query) use ($validatedData) {
                            $query->where('userName', $validatedData['owner']);
                        })->first();

                if (!$worker) {
                    \Log::error('Worker not found for userName: ' . $validatedData['owner']);
                    return response()->json(['success' => false, 'message' => 'Worker not found.'], 404);
                }

                $data['workerID'] = $worker->workerID; // Use the workerID, not the userName
                $data['contractorID'] = null;
            } else {
                $data['workerID'] = null;
                $user = \App\Models\User::where('userName', $validatedData['owner'])->first();
                $contractor = $user ? \App\Models\Contractor::where('userID', $user->userID)->first() : null;
                $data['contractorID'] = $contractor ? $contractor->contractorID : null;
            }

            \Log::info('Assignment Data:', $data); // Log the assignment data

            Assignment::updateOrCreate(['taskID' => $task->taskID], $data);

            \Log::info('Owner assigned successfully.'); // Log success
            return response()->json(['success' => true, 'message' => 'Owner assigned successfully']);
        } catch (\Exception $e) {
            \Log::error("Error in assignOwner: " . $e->getMessage()); // Log the error
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function getTaskDetails($taskId) {
        \Log::info('Fetching task details for taskId: ' . $taskId); // Log the incoming request with taskId

        $task = Task::find($taskId);

        if ($task) {
            \Log::info('Task found: ' . $task->taskID); // Log if task is found
            // Prepare task details for the response
            $taskDetails = [
                'task' => [
                    'name' => $task->taskName,
                    'status' => $task->status,
                    'qty' => $task->qty,
                    'uom' => $task->uom,
                    'unitPrice' => $task->unitPrice,
                    'budget' => $task->budget,
                    'startDate' => $task->startDate ? $task->startDate->format('Y-m-d') : null,
                    'endDate' => $task->endDate ? $task->endDate->format('Y-m-d') : null,
                    'duration' => $task->duration,
                    'remarks' => $task->remarks,
                ]
            ];

            return response()->json($taskDetails);
        } else {
            \Log::error('Task not found for taskId: ' . $taskId); // Log if task is not found
            return response()->json(['error' => 'Task not found'], 404);
        }
    }

    public function updateWarranty(Request $request, $taskId) {
        if (!$request->expectsJson()) {
            \Log::error('Invalid request type. Expected JSON but got: ' . $request->header('Content-Type'));
            return response()->json(['error' => 'Invalid request type'], 400);
        }

        try {
            \Log::info('Received warranty update request', $request->all());

            $user = Auth::user();
            if (!$user || $user->role === 'homeowner') {
                \Log::warning('Unauthorized request. Homeowners cannot update warranties. User role: ' . $user->role);
                return response()->json(['error' => 'Unauthorized. Homeowners cannot update warranties.'], 403);
            }

            $task = Task::findOrFail($taskId);
            $project = Project::findOrFail($task->projectID);

            // Check if the project status is "Completed"
            if ($project->projectStatus === 'Completed') {
                return response()->json(['error' => 'Warranty updates are not allowed for completed projects.'], 403);
            }

            $request->validate([
                'startDate' => 'required|date',
                'duration' => 'required|integer|min:1',
                'durationUnit' => 'required|in:days,months,years',
                'description' => 'nullable|string',
            ]);

            \Log::info('Task found for warranty update: ' . $task->taskID);

            $startDate = Carbon::parse($request->startDate);

            // Handle duration logic
            switch ($request->durationUnit) {
                case 'days':
                    $endDate = $startDate->copy()->addDays((int) $request->duration);
                    break;
                case 'months':
                    $endDate = $startDate->copy()->addMonths((int) $request->duration);
                    break;
                case 'years':
                    $endDate = $startDate->copy()->addYears((int) $request->duration);
                    break;
                default:
                    \Log::error('Invalid duration unit: ' . $request->durationUnit);
                    return response()->json(['success' => false, 'message' => 'Invalid duration unit'], 400);
            }

            // Determine warranty status based on endDate
            $today = Carbon::now();
            $status = $endDate->greaterThanOrEqualTo($today) ? 'Active' : 'Expired';

            // Generate warranty number
            $currentYear = now()->format('y');
            $lastWarranty = Warranty::where('warrantyNo', 'like', "WA$currentYear%")->orderBy('warrantyNo', 'desc')->first();
            $newNumber = $lastWarranty ? str_pad(((int) substr($lastWarranty->warrantyNo, -4)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $warrantyNo = "WA{$currentYear}{$newNumber}";

            \Log::info('Generated new warranty number: ' . $warrantyNo);

            // Create and save the warranty
            $warranty = new Warranty();
            $warranty->warrantyNo = $warrantyNo;
            $warranty->startDate = $startDate;
            $warranty->endDate = $endDate;
            $warranty->duration = $request->duration;
            $warranty->durationUnit = $request->durationUnit;
            $warranty->status = $status; // Set status dynamically
            $warranty->description = $request->description;
            $warranty->save();

            // Assign warranty to the task
            $task->warrantyNo = $warrantyNo;
            $task->save();

            return response()->json(['success' => true, 'warrantyNo' => $warrantyNo, 'task' => $task]);
        } catch (\Exception $e) {
            \Log::error('Error updating warranty: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating warranty details.'], 500);
        }
    }

    public function removeWarranty(Request $request) {
        try {
            \Log::info('Received request to remove warranty for taskID: ' . $request->taskID);

            $user = Auth::user();

            if (!$user || $user->role === 'homeowner') {
                \Log::warning('Unauthorized request. Homeowners cannot remove warranties. User role: ' . $user->role);
                return response()->json(['error' => 'Unauthorized. Homeowners cannot remove warranties.'], 403);
            }

            $taskId = $request->input('taskId');
            $task = Task::findOrFail($taskId);
            $project = Project::findOrFail($task->projectID);

            // Check if the project status is "Completed"
            if ($project->projectStatus === 'Completed') {
                return response()->json(['error' => 'Warranty removal is not allowed for completed projects.'], 403);
            }


            // Log task found
            \Log::info('Task found for warranty removal: ' . $task->taskID);

            // Set warrantyNo to null to remove the warranty
            $task->warrantyNo = null;
            $task->save();

            \Log::info('Warranty removed successfully for taskID: ' . $task->taskID);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error removing warranty: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while removing the warranty.'], 500);
        }
    }

    public function viewWarrantyDetails($taskID) {
        try {
            \Log::info('Fetching task details for taskID: ' . $taskID);

            // Fetch task with warranty relationship
            $task = Task::with('warranty')->where('taskID', $taskID)->first();

            if (!$task) {
                \Log::warning('Task not found with taskID: ' . $taskID);
                return response()->json(['error' => 'Task not found.']);
            }

            if (!$task->warranty) {
                \Log::warning('No warranty found for taskID: ' . $taskID);
                return response()->json(['error' => 'No warranty available for this task.']);
            }

            $warranty = $task->warranty;

            \Log::info('Found warranty details for taskID: ' . $taskID);
            return response()->json([
                        'task' => $task,
                        'warranty' => $warranty
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching warranty details: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching warranty details.'], 500);
        }
    }

    public function closeProject($projectID, Request $request) {
        $project = Project::find($projectID);

        // Check if the project status is already "Completed"
        if ($project->projectStatus === 'Completed') {
            return response()->json(['error' => 'Project is already completed.'], 403);
        }


        if ($project) {
            $project->update(['projectStatus' => 'Completed']);

            return response()->json(['success' => true, 'message' => 'Project closed successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Project not found']);
    }

    public function uploadDocument(Request $request) {
        // Define valid file extensions
        $validFileTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];

        // Validate input
        $request->validate([
            'file' => 'required|file|max:2048', // Max size (2MB in this case)
            'documentName' => 'required|string|max:255',
            'description' => 'nullable|string|max:100',
            'projectID' => 'required|exists:projects,projectID',
        ]);

        // Get the file from the request
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Get the file extension and validate it
        $fileType = strtolower(trim($file->getClientOriginalExtension()));
        if (!in_array($fileType, $validFileTypes)) {
            return response()->json(['message' => 'Invalid file type. Please upload a valid document.'], 400);
        }

        // Store the file
        $filePath = $file->storeAs('documents', $fileName, 'public');

        // Generate the URL for the uploaded file
        $fileUrl = Storage::url($filePath);  // This generates the public URL
        // Generate Document ID
        $year = date('y');
        $lastDocument = Documents::latest('documentID')->first();
        $lastNumber = $lastDocument ? intval(substr($lastDocument->documentID, 3)) : 0;
        $nextNumber = $lastNumber + 1;
        $formattedNumber = sprintf('%04d', $nextNumber);
        $documentID = 'D' . $year . $formattedNumber;

        // Save document in the database
        Documents::create([
            'documentID' => $documentID,
            'documentName' => $request->documentName,
            'fileType' => $fileType,
            'description' => $request->description,
            'projectID' => $request->projectID,
            'fileContent' => $fileUrl, // Store the URL in fileContent
        ]);

        return response()->json(['message' => 'Document uploaded successfully', 'fileUrl' => $fileUrl]);
    }

    public function documentByProject($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $role = $user->role;

        // Check if user is a contractor
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        // Check if user is a homeowner
        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;
        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        // Check if user is a worker assigned to the project
        $worker = $user->worker;
        $workerProject = $worker ? Task::where('projectID', $projectID)
                        ->whereHas('assignments', function ($query) use ($worker) {
                            $query->where('workerID', $worker->workerID);
                        })
                        ->exists() : null;

        // Allow access if the user is a contractor, homeowner, or worker for the project
        if (!$contractorProject && !$homeownerProject && !$workerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        // Get the project (either from contractor check, homeowner check, or worker check)
        $project = $contractorProject ?? $homeownerProject ?? Project::find($projectID);
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Fetch documents specific to the given projectID
        $documents = Documents::where('projectID', $projectID)->get();

        return view('document', compact('documents', 'role', 'project', 'recentProjects', 'startDate', 'endDate'))->with('currentProject', $project);
    }

    public function getDocumentContent($documentID) {
        $document = Documents::where('documentID', $documentID)->first();

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        return response()->json([
                    'documentName' => $document->documentName,
                    'fileType' => $document->fileType,
                    'fileContent' => $document->fileContent, // Return the URL from fileContent
        ]);
    }

    public function downloadDocument($documentID) {
        // Retrieve the document from the database
        $document = Documents::where('documentID', $documentID)->firstOrFail();

        // Ensure the file is in the correct storage path
        $filePath = public_path($document->fileContent);

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, $document->documentName);
    }

    public function deleteDocuments(Request $request) {
        $documentIDs = $request->input('documentIDs');

        if (empty($documentIDs)) {
            return response()->json(['success' => false, 'message' => 'No documents selected.']);
        }

        try {
            // Delete documents from the database
            Documents::whereIn('documentID', $documentIDs)->delete();
            return response()->json(['success' => true, 'message' => 'Documents deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete documents.']);
        }
    }

    public function report($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        // Check if user is a contractor for the project
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        // Get ownerID from homeowner table based on userID
        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;

        // Check if user is the homeowner of the project
        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        // Allow access if the user is either a contractor for the project or the homeowner
        if (!$contractorProject && !$homeownerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        // Get the project (either from contractor check or homeowner check)
        $project = $contractorProject ?? $homeownerProject;

        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Fetch business (contractor) information
        $businessInfo = $contractor ? [
            'businessName' => $contractor->companyName ?? '',
            'businessNo' => $contractor->registerNo ?? '',
            'businessAddress' => $contractor->businessAddress ?? '',
            'contractorName' => $user->userName ?? '',
            'contractorPhone' => $user->userPhone ?? '',
            'contractorEmail' => $user->email ?? '',
            'companyLogo' => $contractor->companyLogo ?? '',
                ] : [];

        // Fetch client (homeowner) information
        $client = $project->ownerID ? Homeowner::where('ownerID', $project->ownerID)->first() : null;
        $clientUser = $client ? User::where('userID', $client->userID)->first() : null;

        $clientInfo = $clientUser ? [
            'clientName' => $clientUser->userName ?? '',
            'clientAddress' => $client->homeAddress ?? '',
            'clientEmail' => $clientUser->email ?? '',
            'clientPhone' => $clientUser->userPhone ?? '',
                ] : [];

        // Fetch project information
        $projectInfo = [
            'projectID' => $project->projectID ?? '',
            'projectName' => $project->projectName ?? '',
            'projectAddress' => $project->projectAddress ?? '',
            'contactName' => $clientUser->userName ?? '', // Can be modified
            'contactPhone' => $clientUser->userPhone ?? '', // Can be modified
        ];

        // Get the current year in two digits (e.g., "25" for 2025)
        $year = date("y");

        // Find the last quotation for the current year only
        $lastQuotation = Quotation::where('quotationNo', 'like', "QT-{$year}%")
                ->orderBy('quotationNo', 'desc')
                ->first();

        // Default quotation number if no quotations exist for this year
        $quotationNumber = "QT-" . $year . "0001";

        if ($lastQuotation) {
            // Extract the numeric part dynamically
            $lastQuotationNumber = (int) substr($lastQuotation->quotationNo, 5);
            $nextQuotationNumber = str_pad($lastQuotationNumber + 1, 4, '0', STR_PAD_LEFT);
            $quotationNumber = "QT-" . $year . $nextQuotationNumber;
        }

        // Create invoice data (optional)
        $quotationDate = date('Y-m-d'); // You can modify the format as needed
        // Group invoice-related details under invoiceInfo
        $quotationInfo = [
            'quotationNumber' => $quotationNumber,
            'quotationDate' => $quotationDate,
            'paymentTerm' => null,
            'dueDate' => null,
        ];

        // Get recent projects for sidebar navigation
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Fetch tasks without invoiceNo and quotationNo
        $tasks = Task::where('projectID', $projectID)
                ->select('taskID', 'taskName', 'qty', 'uom', 'unitPrice', 'budget')
                ->get();

        // Fetch tasks without invoiceNo and quotationNo
        $tasks = Task::where('projectID', $projectID)
                ->select('taskID', 'taskName', 'qty', 'uom', 'unitPrice', 'budget')
                ->get();

// Fetch TaskReport data for each task
        foreach ($tasks as $task) {
            $taskReports = TaskReport::where('taskID', $task->taskID)->get(); // Get all TaskReports for the task
            $task->isPaid = false; // Default to false

            foreach ($taskReports as $taskReport) {
                if ($taskReport->invoiceNo) {
                    $invoice = Invoice::find($taskReport->invoiceNo);
                    if ($invoice) {
                        $payment = Payment::where('invoiceNo', $invoice->invoiceNo)->first();
                        if ($payment && $payment->paymentType === 'Full Payment') {
                            $task->isPaid = true;
                            break; // If any invoice is fully paid, mark the task as paid
                        }
                    }
                } elseif ($taskReport->quotationNo) {
                    $quotation = Quotation::find($taskReport->quotationNo);
                    if ($quotation) {
                        $payment = Payment::where('quotationNo', $quotation->quotationNo)->first();
                        if ($payment && $payment->paymentType === 'Full Payment') {
                            $task->isPaid = true;
                            break; // If any quotation is fully paid, mark the task as paid
                        }
                    }
                }
            }
        }
        // Check if tasks exist
        $hasTasks = $tasks->isNotEmpty();

        // Fetch previous payment details
        $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
        $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
        $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

        // Fetch the last invoice or quotation to check the deposit amount
        $lastPaymentRecord = DB::table('reports')
                ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                ->where('reports.projectID', $projectID)
                ->orderBy('reports.created_at', 'desc')
                ->first();

        $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

        \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
        \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

        // In the backend (report function)
        if ($previousPaymentAmount != $lastDepositAmount) {
            $previousPaymentAmount = 0;
            \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
        }

        return view('report', compact(
                                'project', 'role', 'recentProjects', 'businessInfo', 'clientInfo',
                                'projectInfo', 'quotationInfo', 'tasks', 'hasTasks',
                                'previousPaymentAmount', 'previousPayments', 'lastDepositAmount', 'startDate', 'endDate'
                        ))->with('currentProject', $project);
    }

    public function report2($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        // Check if user is a contractor for the project
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        // Get ownerID from homeowner table based on userID
        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;

        // Check if user is the homeowner of the project
        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        // Allow access if the user is either a contractor for the project or the homeowner
        if (!$contractorProject && !$homeownerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        // Get the project (either from contractor check or homeowner check)
        $project = $contractorProject ?? $homeownerProject;
        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Fetch business (contractor) information
        $businessInfo = $contractor ? [
            'businessName' => $contractor->companyName ?? '',
            'businessNo' => $contractor->registerNo ?? '',
            'businessAddress' => $contractor->businessAddress ?? '',
            'contractorName' => $user->userName ?? '',
            'contractorPhone' => $user->userPhone ?? '',
            'contractorEmail' => $user->email ?? '',
            'companyLogo' => $contractor->companyLogo ?? '',
                ] : [];

        // Fetch client (homeowner) information
        $client = $project->ownerID ? Homeowner::where('ownerID', $project->ownerID)->first() : null;
        $clientUser = $client ? User::where('userID', $client->userID)->first() : null;

        $clientInfo = $clientUser ? [
            'clientName' => $clientUser->userName ?? '',
            'clientAddress' => $client->homeAddress ?? '',
            'clientEmail' => $clientUser->email ?? '',
            'clientPhone' => $clientUser->userPhone ?? '',
                ] : [];

        // Fetch project information
        $projectInfo = [
            'projectID' => $project->projectID ?? '',
            'projectName' => $project->projectName ?? '',
            'projectAddress' => $project->projectAddress ?? '',
            'contactName' => $clientUser->userName ?? '', // Can be modified
            'contactPhone' => $clientUser->userPhone ?? '', // Can be modified
        ];

        // Get the current year in two digits (e.g., "25" for 2025)
        $year = date("y");

        // Find the last invoice for the **current year** only
        $lastInvoice = Invoice::where('invoiceNo', 'like', "INV-{$year}%")
                ->orderBy('invoiceNo', 'desc')
                ->first();

        // Default invoice number if no invoices exist for this year
        $invoiceNumber = "INV-" . $year . "0001";

        if ($lastInvoice) {
            // Extract the last invoice number (e.g., INV-250001 -> 0001)
            $lastInvoiceNumber = substr($lastInvoice->invoiceNo, 6); // Change from 5 to 6
            $nextInvoiceNumber = str_pad(intval($lastInvoiceNumber) + 1, 4, '0', STR_PAD_LEFT); // Increment and pad with zeros
            $invoiceNumber = "INV-" . $year . $nextInvoiceNumber;
        }

        // Create invoice data (optional)
        $invoiceDate = date('Y-m-d'); // You can modify the format as needed
        // Group invoice-related details under invoiceInfo
        $invoiceInfo = [
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $invoiceDate,
            'paymentInstruction' => null, // You can adjust this as per your requirement
            'dueDate' => null, // Same as above
        ];

        // Get recent projects for sidebar navigation
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Fetch tasks without invoiceNo and quotationNo
        $tasks = Task::where('projectID', $projectID)
                ->select('taskID', 'taskName', 'qty', 'uom', 'unitPrice', 'budget')
                ->get();

        foreach ($tasks as $task) {
            $taskReports = TaskReport::where('taskID', $task->taskID)->get(); // Get all TaskReports for the task
            $task->isPaid = false; // Default to false

            foreach ($taskReports as $taskReport) {
                if ($taskReport->invoiceNo) {
                    $invoice = Invoice::find($taskReport->invoiceNo);
                    if ($invoice) {
                        $payment = Payment::where('invoiceNo', $invoice->invoiceNo)->first();
                        if ($payment && $payment->paymentType === 'Full Payment') {
                            $task->isPaid = true;
                            break; // If any invoice is fully paid, mark the task as paid
                        }
                    }
                } elseif ($taskReport->quotationNo) {
                    $quotation = Quotation::find($taskReport->quotationNo);
                    if ($quotation) {
                        $payment = Payment::where('quotationNo', $quotation->quotationNo)->first();
                        if ($payment && $payment->paymentType === 'Full Payment') {
                            $task->isPaid = true;
                            break; // If any quotation is fully paid, mark the task as paid
                        }
                    }
                }
            }
        }

        // Check if tasks exist
        $hasTasks = $tasks->isNotEmpty();
        // Fetch previous payment details
        $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
        $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
        $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

        // Fetch the last invoice or quotation to check the deposit amount
        $lastPaymentRecord = DB::table('reports')
                ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                ->where('reports.projectID', $projectID)
                ->orderBy('reports.created_at', 'desc')
                ->first();

        $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

        \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
        \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

        // In the backend (report function)
        if ($previousPaymentAmount != $lastDepositAmount) {
            $previousPaymentAmount = 0;
            \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
        }

        return view('report2', compact(
                                'project', 'role', 'recentProjects', 'businessInfo', 'clientInfo', 'projectInfo', 'invoiceInfo', 'tasks', 'hasTasks', 'previousPaymentAmount', 'previousPayments', 'lastDepositAmount', 'startDate', 'endDate'
                        ))->with('currentProject', $project);
    }

    public function updateCompanyLogo(Request $request) {
        // Log the incoming request for the logo update
        Log::info('Update company logo request received', [
            'request_data' => $request->all(),
        ]);

        // Validate the uploaded file (image type, allowed extensions, and max file size of 5MB)
        $validated = $request->validate([
            'businessLogo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB max file size
        ]);

        // Get the authenticated user ID (userID)
        $userID = Auth::id();

        // Log the authenticated userID
        Log::info('Authenticated userID', ['userID' => $userID]);

        // Find the contractor ID associated with the user ID in the Project model
        $contractor = Contractor::whereHas('projects', function ($query) use ($userID) {
                    $query->where('userID', $userID);
                })->first();

        // Log contractor retrieval
        if ($contractor) {
            Log::info('Contractor found', [
                'contractor_id' => $contractor->id,
                'contractor' => $contractor,
            ]);
        } else {
            Log::error('Contractor not found', [
                'userID' => $userID,
            ]);
            return response()->json(['success' => false, 'message' => 'Contractor not found.']);
        }

        // Check if the request contains a file
        if ($request->hasFile('businessLogo')) {
            $file = $request->file('businessLogo');
            $fileName = time() . '_' . $file->getClientOriginalName(); // Ensure unique file names

            Log::info('File received for upload', [
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
            ]);

            // Store the file in the "contractor_logos" directory within the "public" disk
            try {
                $filePath = $file->storeAs('contractor_logos', $fileName, 'public');
                Log::info('File stored successfully', [
                    'file_path' => $filePath,
                ]);
            } catch (\Exception $e) {
                Log::error('File storage error', [
                    'error_message' => $e->getMessage(),
                ]);
                return response()->json(['success' => false, 'message' => 'Error storing file.']);
            }

            // Generate the public URL for the uploaded file
            $fileUrl = Storage::url($filePath);
            Log::info('File URL generated', [
                'file_url' => $fileUrl,
            ]);

            // Delete the existing logo if present
            if ($contractor->companyLogo) {
                $oldFilePath = str_replace('/storage/', '', $contractor->companyLogo);
                try {
                    Storage::disk('public')->delete($oldFilePath);
                    Log::info('Old logo deleted', [
                        'old_file_path' => $oldFilePath,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting old logo', [
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            // Update the contractor's companyLogo field with the new public URL
            $contractor->companyLogo = $fileUrl;
            $contractor->save();
            Log::info('Contractor logo updated', [
                'contractor_id' => $contractor->id,
                'new_logo_url' => $fileUrl,
            ]);

            // Return success response with feedback message
            return response()->json(['success' => true, 'logoUrl' => $fileUrl]);
        }

        // If no file was uploaded, log the error and return a failure response
        Log::warning('No file uploaded');
        return response()->json(['success' => false, 'message' => 'No logo uploaded. Please select a valid file.']);
    }

    public function generateInvoice(Request $request, $projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is a contractor for the project
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        if (!$contractorProject) {
            return redirect()->back()->with('error', 'You are not authorized for this project.');
        }

        // Get project details
        $project = $contractorProject;

        // Get the report data from the frontend
        $data = $request->input('reportData');

        // Assign project details
        $data['projectInfo']['projectName'] = $project->projectName ?? 'N/A';
        $data['projectInfo']['projectAddress'] = $project->projectAddress ?? 'N/A';

        // Check if a logo was uploaded
        if ($request->hasFile('companyLogo')) {
            $logoPath = $request->file('companyLogo')->store('contractor_logos', 'public');
            $data['businessInfo']['companyLogo'] = asset($logoPath);
        } else {
            // Retrieve the contractor's logo from the database
            $contractorLogo = $contractor ? $contractor->companyLogo : null;

            if ($contractorLogo) {
                $data['businessInfo']['companyLogo'] = public_path($contractorLogo);
            } else {
                $data['businessInfo']['companyLogo'] = public_path('images/AlloymontLogo.png');
            }
        }

        // Optionally, log the data for debugging
        \Log::info('Invoice Data:', $data);

        // Fetch previous payment details
        $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
        $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
        $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

        // Fetch the last invoice or quotation to check the deposit amount
        $lastPaymentRecord = DB::table('reports')
                ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                ->where('reports.projectID', $projectID)
                ->orderBy('reports.created_at', 'desc')
                ->first();

        $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

        \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
        \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

        if ($previousPaymentAmount == $lastDepositAmount) {
            $data['paymentDetails']['previousPaymentAmount'] = $previousPaymentAmount;
            $data['paymentDetails']['previousPayments'] = $previousPayments;
            \Log::info('Previous amount matches last deposit amount. Using previous amount:', ['previousAmount' => $previousPaymentAmount]);
        } else {
            $data['paymentDetails']['previousPaymentAmount'] = 0;
            \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
        }

        $pdf = PDF::loadView('invoice', [
                    'businessInfo' => $data['businessInfo'],
                    'clientInfo' => $data['clientInfo'],
                    'projectInfo' => $data['projectInfo'],
                    'invoiceInfo' => $data['invoiceInfo'],
                    'tasks' => $data['tasks'],
                    'paymentDetails' => $data['paymentDetails'],
                    'remarks' => $data['remarks'],
                    'customerSignature' => $data['customerSignature'],
                    'totalAmount' => $data['paymentDetails']['amountDue'],
        ]);

        return $pdf->download('invoice.pdf');
    }

    public function generateQuotation(Request $request, $projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is a contractor for the project
        $contractor = $user->contractor;
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        if (!$contractorProject) {
            return redirect()->back()->with('error', 'You are not authorized for this project.');
        }

        // Get project details
        $project = $contractorProject;

        // Get the report data from the frontend
        $data = $request->input('reportData');

        // Assign project details
        $data['projectInfo']['projectName'] = $project->projectName ?? 'N/A';
        $data['projectInfo']['projectAddress'] = $project->projectAddress ?? 'N/A';

        // Check if a logo was uploaded
        if ($request->hasFile('companyLogo')) {
            $logoPath = $request->file('companyLogo')->store('contractor_logos', 'public');
            $data['businessInfo']['companyLogo'] = asset($logoPath);
        } else {
            // Retrieve the contractor's logo from the database
            $contractorLogo = $contractor ? $contractor->companyLogo : null;

            if ($contractorLogo) {
                $data['businessInfo']['companyLogo'] = public_path($contractorLogo);
            } else {
                $data['businessInfo']['companyLogo'] = public_path('images/AlloymontLogo.png');
            }
        }

        // Optionally, log the data for debugging
        \Log::info('Quotation Data:', $data);

        // Fetch previous payment details
        $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
        $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
        $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

        // Fetch the last invoice or quotation to check the deposit amount
        $lastPaymentRecord = DB::table('reports')
                ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                ->where('reports.projectID', $projectID)
                ->orderBy('reports.created_at', 'desc')
                ->first();

        $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

        \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
        \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

        if ($previousPaymentAmount == $lastDepositAmount) {
            $data['paymentDetails']['previousPaymentAmount'] = $previousPaymentAmount;
            $data['paymentDetails']['previousPayments'] = $previousPayments;
            \Log::info('Previous amount matches last deposit amount. Using previous amount:', ['previousAmount' => $previousPaymentAmount]);
        } else {
            $data['paymentDetails']['previousPaymentAmount'] = 0;
            \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
        }

        return PDF::loadView('quotation', [
                    'businessInfo' => $data['businessInfo'],
                    'clientInfo' => $data['clientInfo'],
                    'projectInfo' => $data['projectInfo'],
                    'quotationInfo' => $data['quotationInfo'],
                    'tasks' => $data['tasks'],
                    'paymentDetails' => $data['paymentDetails'],
                    'remarks' => $data['remarks'],
                    'customerSignature' => $data['customerSignature'],
                    'totalAmount' => $data['paymentDetails']['amountDue'],
                ])->download('quotation.pdf');
    }

    public function saveInvoice(Request $request, $projectID) {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            \Log::info('Received Save Report Request:', $request->all()); // Log full request data
            // Check if user is a contractor for the project
            $contractor = $user->contractor;
            $contractorProject = $contractor ? Project::where('projectID', $projectID)
                            ->where('contractorID', $contractor->contractorID)
                            ->first() : null;

            if (!$contractorProject) {
                return redirect()->back()->with('error', 'You are not authorized for this project.');
            }

            // Get project details
            $project = $contractorProject;

            // Assign project details
            $data['projectInfo']['projectName'] = $project->projectName ?? 'N/A';
            $data['projectInfo']['projectAddress'] = $project->projectAddress ?? 'N/A';

            if (!$request->has('reportData')) {
                return response()->json(['success' => false, 'message' => 'Missing report data'], 400);
            }

            $data = $request->input('reportData');

            if (!isset($data['paymentDetails'])) {
                return response()->json(['success' => false, 'message' => 'Missing payment details'], 400);
            }

            if (!isset($data['paymentDetails']['subtotal'])) {
                return response()->json(['success' => false, 'message' => 'The report data.paymentDetails.subtotal field is required.'], 400);
            }

            $request->validate([
                'reportData.paymentDetails.subtotal' => 'required|numeric|min:0',
                'reportData.paymentDetails.tax' => 'nullable|numeric|min:0',
                'reportData.paymentDetails.amountDue' => 'required|numeric|min:0',
                'reportData.paymentDetails.depositRate' => 'nullable|numeric|min:0|max:100', // Validate deposit rate
                'reportData.paymentDetails.depositAmount' => 'nullable|numeric|min:0', // Validate deposit amount
            ]);

            DB::beginTransaction();

            // Fetch previous payment details
            $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
            $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
            $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

            // Fetch the last invoice or quotation to check the deposit amount
            $lastPaymentRecord = DB::table('reports')
                    ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                    ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                    ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                    ->where('reports.projectID', $projectID)
                    ->orderBy('reports.created_at', 'desc')
                    ->first();

            $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

            \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
            \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

            if ($previousPaymentAmount == $lastDepositAmount) {
                $data['paymentDetails']['previousPaymentAmount'] = $previousPaymentAmount;
                $data['paymentDetails']['previousPayments'] = $previousPayments;
                \Log::info('Previous amount matches last deposit amount. Using previous amount:', ['previousAmount' => $previousPaymentAmount]);
            } else {
                $data['paymentDetails']['previousPaymentAmount'] = 0;
                \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
            }


            // Generate Report ID (R<YY><NNNN>)
            $year = now()->format('y');
            $lastReport = Report::where('reportID', 'like', "R$year%")->orderBy('reportID', 'desc')->first();
            $nextReportNumber = $lastReport ? (int) substr($lastReport->reportID, 3) + 1 : 1;
            $reportID = "R{$year}" . str_pad($nextReportNumber, 4, '0', STR_PAD_LEFT);

            // Save to Report Table
            $report = new Report();
            $report->reportID = $reportID;
            $report->reportDate = now();
            $report->remarks = $data['remarks'] ?? null;
            $report->projectID = $projectID;
            $report->save();

            // Generate Invoice Number (INV-<YY><NNNN>)
            $lastInvoice = Invoice::where('invoiceNo', 'like', "INV-$year%")->orderBy('invoiceNo', 'desc')->first();
            $nextInvoiceNumber = $lastInvoice ? (int) substr($lastInvoice->invoiceNo, 6) + 1 : 1;
            $invoiceNo = "INV-{$year}" . str_pad($nextInvoiceNumber, 4, '0', STR_PAD_LEFT);

            // Save Invoice
            $invoice = new Invoice();
            $invoice->invoiceNo = $invoiceNo;
            $invoice->subtotal = $data['paymentDetails']['subtotal'] ?? 0;
            $invoice->taxRate = $data['paymentDetails']['tax'] ?? 0;
            $invoice->totalAmount = $data['paymentDetails']['amountDue'] ?? 0;
            $invoice->depositRate = $data['paymentDetails']['depositRate'] ?? 0;
            $invoice->depositAmount = $data['paymentDetails']['depositAmount'] ?? 0;
            $invoice->dueDate = $data['paymentDetails']['dueDate'] ?? null;
            $invoice->paymentInstruction = $data['paymentDetails']['paymentInstruction'] ?? null;
            $invoice->reportID = $report->reportID;
            $invoice->previousAmount = $data['paymentDetails']['previousPaymentAmount'];
            $invoice->balance = $data['paymentDetails']['amountDue'] - $data['paymentDetails']['previousPaymentAmount'];
            $invoice->save();

            $selectedTasks = $data['tasks'] ?? [];
            foreach ($selectedTasks as $taskData) {
                $taskReport = new TaskReport();
                $taskReport->taskID = $taskData['taskID'];
                $taskReport->invoiceNo = $invoiceNo; // Save the invoiceNo
                $taskReport->save();
            }

            // Generate Payment ID (PY<YY><NNN>)
            $lastPayment = Payment::where('paymentID', 'like', "PY$year%")->orderBy('paymentID', 'desc')->first();
            $nextPaymentNumber = $lastPayment ? (int) substr($lastPayment->paymentID, 4) + 1 : 1;
            $paymentID = "PY{$year}" . str_pad($nextPaymentNumber, 3, '0', STR_PAD_LEFT);

            // Save Payment
            $payment = new Payment();
            $payment->paymentID = $paymentID;
            $payment->paymentDate = null;
            $payment->paymentType = $data['paymentDetails']['paymentOptions'] ?? 'full';
            $payment->paymentStatus = 'pending';
            $payment->paymentAmount = ($invoice->depositAmount != 0) ? $invoice->depositAmount : $invoice->balance;
            $payment->receipt = null;
            $payment->remarks = null;
            $payment->invoiceNo = $invoiceNo;
            $payment->quotationNo = null;
            $payment->serviceNo = null;
            $payment->save();

            // Check if user is a contractor for the project
            $contractor = $user->contractor;
            $contractorProject = $contractor ? Project::where('projectID', $projectID)
                            ->where('contractorID', $contractor->contractorID)
                            ->first() : null;

            if (!$contractorProject) {
                return redirect()->back()->with('error', 'You are not authorized for this project.');
            }

            // Check if a logo was uploaded
            if ($request->hasFile('companyLogo')) {
                $logoPath = $request->file('companyLogo')->store('contractor_logos', 'public');
                $data['businessInfo']['companyLogo'] = asset($logoPath);
            } else {
                // Retrieve the contractor's logo from the database
                $contractorLogo = $contractor ? $contractor->companyLogo : null;

                if ($contractorLogo) {
                    $data['businessInfo']['companyLogo'] = public_path($contractorLogo);
                } else {
                    $data['businessInfo']['companyLogo'] = public_path('images/AlloymontLogo.png');
                }
            }

            // ---- Generate Invoice PDF ----
            $invoiceDate = now()->format('Ymd'); // Format: YYYYMMDD
            $fileName = "{$invoiceNo}_{$invoiceDate}.pdf";

            // Generate PDF
            $pdf = PDF::loadView('invoice', [
                        'businessInfo' => $data['businessInfo'],
                        'clientInfo' => $data['clientInfo'],
                        'projectInfo' => $data['projectInfo'],
                        'invoiceInfo' => $data['invoiceInfo'],
                        'tasks' => $data['tasks'],
                        'paymentDetails' => $data['paymentDetails'],
                        'remarks' => $data['remarks'],
                        'customerSignature' => $data['customerSignature'],
                        'totalAmount' => $data['paymentDetails']['amountDue'],
            ]);

            // Save PDF to storage/documents
            $filePath = "documents/" . $fileName;
            Storage::disk('public')->put($filePath, $pdf->output());
            $fileUrl = Storage::url($filePath);

            // Generate Document ID (D<YY><NNNN>)
            $lastDocument = Documents::latest('documentID')->first();
            $lastNumber = $lastDocument ? intval(substr($lastDocument->documentID, 3)) : 0;
            $nextNumber = $lastNumber + 1;
            $documentID = 'D' . $year . sprintf('%04d', $nextNumber);

            // Save Document Record
            $document = new Documents();
            $document->documentID = $documentID;
            $document->documentName = $fileName;
            $document->fileType = "pdf";
            $document->fileContent = $fileUrl;
            $document->description = "-";
            $document->projectID = $projectID;
            $document->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Invoice saved successfully! It has been added to the project documents for reference.', 'fileUrl' => $fileUrl, 'redirectUrl' => route('document.project', ['projectID' => $projectID])]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Save Report Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving report: ' . $e->getMessage()], 500);
        }
    }

    public function saveQuotation(Request $request, $projectID) {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            \Log::info('Received Save Quotation Request:', $request->all());

            $contractor = $user->contractor;
            $contractorProject = $contractor ? Project::where('projectID', $projectID)
                            ->where('contractorID', $contractor->contractorID)
                            ->first() : null;

            if (!$contractorProject) {
                return redirect()->back()->with('error', 'You are not authorized for this project.');
            }

            // Get project details
            $project = $contractorProject;

            // Assign project details
            $data['projectInfo']['projectName'] = $project->projectName ?? 'N/A';
            $data['projectInfo']['projectAddress'] = $project->projectAddress ?? 'N/A';

            if (!$request->has('reportData')) {
                return response()->json(['success' => false, 'message' => 'Missing report data'], 400);
            }

            $data = $request->input('reportData');

            if (!isset($data['paymentDetails']['subtotal'])) {
                return response()->json(['success' => false, 'message' => 'The report data.paymentDetails.subtotal field is required.'], 400);
            }

            $request->validate([
                'reportData.paymentDetails.subtotal' => 'required|numeric|min:0',
                'reportData.paymentDetails.tax' => 'nullable|numeric|min:0',
                'reportData.paymentDetails.amountDue' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Fetch previous payment details
            $previousPaymentDetails = json_decode(json_encode($this->getPreviousPaymentAmount($projectID)->getData()), true);
            $previousPaymentAmount = $previousPaymentDetails['previousPaymentAmount'] ?? 0;
            $previousPayments = $previousPaymentDetails['previousPayments'] ?? [];

            // Fetch the last invoice or quotation to check the deposit amount
            $lastPaymentRecord = DB::table('reports')
                    ->leftJoin('invoices', 'reports.reportID', '=', 'invoices.reportID')
                    ->leftJoin('quotations', 'reports.reportID', '=', 'quotations.reportID')
                    ->select('invoices.depositAmount', 'quotations.depositAmount', 'reports.created_at')
                    ->where('reports.projectID', $projectID)
                    ->orderBy('reports.created_at', 'desc')
                    ->first();

            $lastDepositAmount = $lastPaymentRecord ? ($lastPaymentRecord->depositAmount ?? $lastPaymentRecord->depositAmount) : 0;

            \Log::info('Previous Payment Amount:', ['previousPaymentAmount' => $previousPaymentAmount]);
            \Log::info('Last Deposit Amount:', ['lastDepositAmount' => $lastDepositAmount]);

            if ($previousPaymentAmount == $lastDepositAmount) {
                $data['paymentDetails']['previousPaymentAmount'] = $previousPaymentAmount;
                $data['paymentDetails']['previousPayments'] = $previousPayments;
                \Log::info('Previous amount matches last deposit amount. Using previous amount:', ['previousAmount' => $previousPaymentAmount]);
            } else {
                $data['paymentDetails']['previousPaymentAmount'] = 0;
                \Log::info('Previous amount does not match last deposit amount. Resetting previous amount to 0.');
            }


            // Generate Report ID (R<YY><NNNN>)
            $year = now()->format('y');
            $lastReport = Report::where('reportID', 'like', "R$year%")
                            ->orderBy('reportID', 'desc')->first();
            $nextReportNumber = $lastReport ? (int) substr($lastReport->reportID, 3) + 1 : 1;
            $reportID = "R{$year}" . str_pad($nextReportNumber, 4, '0', STR_PAD_LEFT);

            // Save Report
            $report = new Report();
            $report->reportID = $reportID;
            $report->reportDate = now();
            $report->remarks = $data['remarks'] ?? null;
            $report->projectID = $projectID;
            $report->save();

            // Generate Quotation No (QT-<YY><NNNN>)
            $lastQuotation = Quotation::where('quotationNo', 'like', "QT-$year%")
                            ->orderBy('quotationNo', 'desc')->first();
            $nextQuotationNumber = $lastQuotation ? (int) substr($lastQuotation->quotationNo, 6) + 1 : 1;
            $quotationNo = "QT-{$year}" . str_pad($nextQuotationNumber, 4, '0', STR_PAD_LEFT);

            // Save Quotation
            $quotation = new Quotation();
            $quotation->quotationNo = $quotationNo;
            $quotation->subtotal = $data['paymentDetails']['subtotal'];
            $quotation->taxRate = $data['paymentDetails']['tax'] ?? 0;
            $quotation->estimatedCost = $data['paymentDetails']['amountDue'];
            $quotation->depositRate = $data['paymentDetails']['depositRate'] ?? 0;
            $quotation->depositAmount = $data['paymentDetails']['depositAmount'] ?? 0;
            $quotation->validityStart = isset($data['quotationInfo']['quotationDate']) ? Carbon::parse($data['quotationInfo']['quotationDate']) : now();
            $quotation->validityEnd = isset($data['quotationInfo']['dueDate']) ? Carbon::parse($data['quotationInfo']['dueDate']) : now()->addDays(30);
            $quotation->paymentInstruction = $data['paymentDetails']['paymentInstruction'] ?? null;
            $quotation->reportID = $reportID;
            $quotation->previousAmount = $data['paymentDetails']['previousPaymentAmount'];
            $quotation->balance = $data['paymentDetails']['amountDue'] - $data['paymentDetails']['previousPaymentAmount'];
            $quotation->save();

            // Save to TaskReport Table
            $selectedTasks = $data['tasks'] ?? [];
            foreach ($selectedTasks as $taskData) {
                $taskReport = new TaskReport();
                $taskReport->taskID = $taskData['taskID'];
                $taskReport->quotationNo = $quotationNo; // Save the quotationNo
                $taskReport->save();
            }
            // Generate Payment ID (PY<YY><NNN>)
            $lastPayment = Payment::where('paymentID', 'like', "PY$year%")->orderBy('paymentID', 'desc')->first();
            $nextPaymentNumber = $lastPayment ? (int) substr($lastPayment->paymentID, 4) + 1 : 1;
            $paymentID = "PY{$year}" . str_pad($nextPaymentNumber, 3, '0', STR_PAD_LEFT);

            // Save Payment
            $payment = new Payment();
            $payment->paymentID = $paymentID;
            $payment->paymentDate = null;
            $payment->paymentType = $data['paymentDetails']['paymentOptions'] ?? 'full';
            $payment->paymentStatus = 'pending';
            $payment->paymentAmount = ($quotation->depositAmount != 0) ? $quotation->depositAmount : $quotation->balance;
            $payment->receipt = null;
            $payment->remarks = null;
            $payment->invoiceNo = null;
            $payment->quotationNo = $quotationNo;
            $payment->serviceNo = null;
            $payment->save();

            // Check if user is a contractor for the project
            $contractor = $user->contractor;
            $contractorProject = $contractor ? Project::where('projectID', $projectID)
                            ->where('contractorID', $contractor->contractorID)
                            ->first() : null;

            if (!$contractorProject) {
                return redirect()->back()->with('error', 'You are not authorized for this project.');
            }

            // Assign project details
            $data['projectInfo']['projectName'] = $project->projectName ?? 'N/A';
            $data['projectInfo']['projectAddress'] = $project->projectAddress ?? 'N/A';

            // Check if a logo was uploaded
            if ($request->hasFile('companyLogo')) {
                $logoPath = $request->file('companyLogo')->store('contractor_logos', 'public');
                $data['businessInfo']['companyLogo'] = asset($logoPath);
            } else {
                // Retrieve the contractor's logo from the database
                $contractorLogo = $contractor ? $contractor->companyLogo : null;

                if ($contractorLogo) {
                    $data['businessInfo']['companyLogo'] = public_path($contractorLogo);
                } else {
                    $data['businessInfo']['companyLogo'] = public_path('images/AlloymontLogo.png');
                }
            }

            // ---- Generate Quotation PDF ----
            $quotationDate = now()->format('Ymd');

            $fileName = "{$quotationNo}_{$quotationDate}.pdf";

            $pdf = PDF::loadView('quotation', [
                        'businessInfo' => $data['businessInfo'],
                        'clientInfo' => $data['clientInfo'],
                        'projectInfo' => $data['projectInfo'],
                        'quotationInfo' => $data['quotationInfo'],
                        'tasks' => $data['tasks'],
                        'paymentDetails' => $data['paymentDetails'],
                        'remarks' => $data['remarks'],
                        'customerSignature' => $data['customerSignature'],
                        'totalAmount' => $data['paymentDetails']['amountDue'],
            ]);

            // Save PDF to storage/documents
            $filePath = "documents/" . $fileName;
            Storage::disk('public')->put($filePath, $pdf->output());
            $fileUrl = Storage::url($filePath);

            // Generate Document ID (D<YY><NNNN>)
            $lastDocument = Documents::latest('documentID')->first();
            $lastNumber = $lastDocument ? intval(substr($lastDocument->documentID, 3)) : 0;
            $nextNumber = $lastNumber + 1;
            $documentID = 'D' . $year . sprintf('%04d', $nextNumber);

            // Save Document Record
            $document = new Documents();
            $document->documentID = $documentID;
            $document->documentName = $fileName;
            $document->fileType = "pdf";
            $document->fileContent = $fileUrl;
            $document->description = "-";
            $document->projectID = $projectID;
            $document->save();

            DB::commit();

            return response()->json([
                        'success' => true,
                        'message' => 'Quotation saved successfully! It has been added to the project documents for reference.',
                        'quotationNo' => $quotationNo,
                        'reportID' => $reportID,
                        'fileUrl' => $fileUrl,
                        'redirectUrl' => route('document.project', ['projectID' => $projectID]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Save Quotation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving quotation: ' . $e->getMessage()], 500);
        }
    }

    public function getPreviousPaymentAmount($projectID) {
        $reports = Report::with(['invoices.payments', 'quotations.payments'])
                ->where('projectID', $projectID)
                ->get();

        $previousPaymentAmount = 0;
        $previousPayments = [];
        $hasDeposit = false;

        foreach ($reports as $report) {
            foreach ($report->invoices as $invoice) {
                // Check if previousAmount is not zero and set it to zero
                if ($invoice->previousAmount != 0) {
                    $previousPaymentAmount += $invoice->previousAmount; // Add to previousPaymentAmount
                    $invoice->previousAmount = 0; // Reset to 0
                    $invoice->save();
                }

                foreach ($invoice->payments as $payment) {
                    if ($payment->paymentStatus === 'completed' || $payment->paymentStatus === 'pending') {
                        $previousPaymentAmount += $payment->paymentAmount;
                        $previousPayments[] = [
                            'paymentID' => $payment->paymentID,
                            'paymentAmount' => $payment->paymentAmount,
                            'paymentDate' => $payment->paymentDate,
                            'paymentType' => $payment->paymentType,
                        ];

                        if ($payment->paymentType === 'Deposit') {
                            $hasDeposit = true;
                        }
                    }
                }
            }

            foreach ($report->quotations as $quotation) {
                // Check if previousAmount is not zero and set it to zero
                if ($quotation->previousAmount != 0) {
                    $previousPaymentAmount += $quotation->previousAmount; // Add to previousPaymentAmount
                    $quotation->previousAmount = 0; // Reset to 0
                    $quotation->save();
                }

                foreach ($quotation->payments as $payment) {
                    if ($payment->paymentStatus === 'completed' || $payment->paymentStatus === 'pending') {
                        $previousPaymentAmount += $payment->paymentAmount;
                        $previousPayments[] = [
                            'paymentID' => $payment->paymentID,
                            'paymentAmount' => $payment->paymentAmount,
                            'paymentDate' => $payment->paymentDate,
                            'paymentType' => $payment->paymentType,
                        ];

                        if ($payment->paymentType === 'Deposit') {
                            $hasDeposit = true;
                        }
                    }
                }
            }
        }

        return response()->json([
                    'previousPaymentAmount' => $previousPaymentAmount,
                    'previousPayments' => $previousPayments,
                    'hasDeposit' => $hasDeposit,
        ]);
    }

    public function gantt($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role; // Ensure this is correctly set
        // Log the authenticated user
        \Log::info('Authenticated User:', ['user' => $user]);

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Fetch the project
        $project = Project::findOrFail($projectID);

        $startDate = $project->startDate;
        $endDate = $project->endDate;

        if (!$project) {
            return abort(404, 'Project not found');
        }

        // Check if the user has access to the project
        $isContractor = $user->contractor && $project->contractorID === $user->contractor->contractorID;
        $isHomeowner = $user->homeowner && $project->ownerID === $user->homeowner->ownerID;
        $isWorker = $user->worker && Assignment::where('workerID', $user->worker->workerID)
                        ->join('tasks', 'assignments.taskID', '=', 'tasks.taskID')
                        ->where('tasks.projectID', $projectID)
                        ->exists();

        if (!$isContractor && !$isHomeowner && !$isWorker) {
            return abort(403, 'Unauthorized access to project');
        }

        // Fetch tasks for this project
        $tasks = Task::where('projectID', $projectID)->get();

        return view('gantt', compact('tasks', 'role', 'project', 'recentProjects', 'startDate', 'endDate'))->with('currentProject', $project);
    }

    public function getTasks($projectID) {
        $tasks = Task::where('projectID', $projectID)->get();
        $project = Project::findOrFail($projectID);

        $formattedTasks = $tasks->map(function ($task) {

            $hasTaskReport = $task->taskReports()->exists();
            // Assign different colors based on task status
            $color = match ($task->status) {
                'On-Hold' => '#ffcc00', // Yellow
                'Working' => '#3399ff', // Blue
                'Done' => '#33cc33', // Green
                'Not Started' => '#999999', // Gray
            };

            return [
        'id' => $task->taskID,
        'text' => $task->taskName,
        'start_date' => date('d-m-Y', strtotime($task->startDate)),
        'end_date' => date('d-m-Y', strtotime($task->endDate)),
        'duration' => (strtotime($task->endDate) - strtotime($task->startDate)) / (60 * 60 * 24),
        'parent' => $task->parentTaskID ?? "0",
        'status' => $task->status,
        'color' => $color,
        'hasTaskReport' => $hasTaskReport,
            ];
        });

        return response()->json(['data' => $formattedTasks]);
    }

    public function updateTaskInGantt(Request $request) {
        \Log::info('Update Task Request:', $request->all()); // Log the request data

        $taskData = $request->all();

        // Validate the input data
        $validatedData = $request->validate([
            'id' => 'required|string', // Ensure taskID is provided
            'text' => 'required|string', // Task Name
            'start_date' => 'required|date', // Start Date
            'end_date' => 'required|date', // End Date
            'duration' => 'required|numeric', // Duration
        ]);

        // Find the task by ID
        $task = Task::findOrFail($validatedData['id']);

        // Fetch the project associated with the task
        $project = $task->project;

        if (!$project) {
            return response()->json(['error' => 'Project not found for this task'], 404);
        }

        // Check if the project status is "Completed"
        if ($project->projectStatus === 'Completed') {
            return response()->json(['error' => 'Task updates are not allowed for completed projects.'], 403);
        }

        // Convert task dates to Carbon instances for comparison
        $startDate = \Carbon\Carbon::parse($validatedData['start_date'])->setTimezone(config('app.timezone'));
        $endDate = \Carbon\Carbon::parse($validatedData['end_date'])->setTimezone(config('app.timezone'));

        // Convert project dates to Carbon instances for comparison
        $projectStartDate = \Carbon\Carbon::parse($project->startDate);
        $projectEndDate = \Carbon\Carbon::parse($project->endDate);

        // Validate the task dates against the project dates
        if ($startDate->lt($projectStartDate)) {
            return response()->json(['error' => 'Start date cannot be earlier than the project start date.'], 400);
        }

        if ($endDate->gt($projectEndDate)) {
            return response()->json(['error' => 'End date cannot exceed the project end date.'], 400);
        }

        if ($startDate->gt($endDate)) {
            return response()->json(['error' => 'Start date cannot be later than the end date.'], 400);
        }

        if ($endDate->lt($startDate)) {
            return response()->json(['error' => 'End date cannot be earlier than the start date.'], 400);
        }

        // Update only the allowed fields
        $task->update([
            'taskName' => $validatedData['text'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'duration' => $validatedData['duration'],
        ]);

        \Log::info('Task Updated:', $task->toArray()); // Log the updated task

        return response()->json(['success' => true]);
    }

    public function destroy($taskID) {
        // Find the task by ID
        $task = Task::find($taskID);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Fetch the project associated with the task
        $project = $task->project;

        // Check if the project status is "Completed"
        if ($project->projectStatus === 'Completed') {
            return response()->json(['error' => 'Task deletion is not allowed for completed projects.'], 403);
        }

        // Delete the task
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function getProjectCost($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        // Check contractor access
        $contractor = $user->contractor;

        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        // Get ownerID for homeowner check
        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;

        // Check if user is a homeowner for this project
        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        // Restrict access if the user is neither the contractor nor homeowner
        if (!$contractorProject && !$homeownerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        // Get project details
        $project = $contractorProject ?? $homeownerProject;
        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Get reports associated with this project
        $reportIDs = Report::where('projectID', $projectID)->pluck('reportID');

        // Get invoices and quotations linked to those reports
        $invoiceNos = Invoice::whereIn('reportID', $reportIDs)->pluck('invoiceNo');
        $quotationNos = Quotation::whereIn('reportID', $reportIDs)->pluck('quotationNo');

        // Fetch payments with contractor information
        $allPayments = Payment::with(['invoice.report.project.contractor.user', 'quotation.report.project.contractor.user'])
                ->whereIn('invoiceNo', $invoiceNos)
                ->orWhereIn('quotationNo', $quotationNos)
                ->get()
                ->map(function ($payment) {
            $payment->referenceNo = $payment->invoiceNo ?? $payment->quotationNo;

            // Fetch contractor name from the associated project
            if ($payment->invoice) {
                $payment->contractorName = $payment->invoice->report->project->contractor->user->userName ?? 'N/A';
            } elseif ($payment->quotation) {
                $payment->contractorName = $payment->quotation->report->project->contractor->user->userName ?? 'N/A';
            } else {
                $payment->contractorName = 'N/A';
            }

            return $payment;
        });

        // Calculate monthly paid data
        $monthlyPaid = $allPayments
                ->where('paymentStatus', 'paid')
                ->groupBy(function ($payment) {
                    return \Carbon\Carbon::parse($payment->paymentDate)->format('Y-m'); // Group by Year-Month
                })
                ->map(function ($payments) {
            return $payments->sum('paymentAmount'); // Sum amount per month
        });

        $monthlyPaidData = $monthlyPaid->toArray();

        // Calculate total costs (sum of all payments)
        $totalCost = $allPayments->sum('paymentAmount');

        // Calculate total paid (sum of only paid payments)
        $totalPaid = $allPayments->where('paymentStatus', 'paid')->sum('paymentAmount');

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        return view('projectCost', compact('allPayments', 'role', 'recentProjects', 'project', 'totalCost', 'totalPaid', 'contractor', 'monthlyPaidData', 'startDate', 'endDate'))->with('currentProject', $project);
    }

    public function getCostDetails($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $contractor = $user->contractor;

        $role = $user->role;

        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        $homeowner = Homeowner::where('userID', $user->userID)->first();
        $ownerID = $homeowner ? $homeowner->ownerID : null;

        $homeownerProject = $ownerID ? Project::where('projectID', $projectID)
                        ->where('ownerID', $ownerID)
                        ->first() : null;

        if (!$contractorProject && !$homeownerProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        $project = $contractorProject ?? $homeownerProject;
        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Fetch all tasks under this project
        $tasks = Task::where('projectID', $projectID)->get();

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        return view('costDetails', compact('recentProjects', 'role', 'project', 'contractor', 'tasks', 'startDate', 'endDate'));
    }

    public function updateCost(Request $request, $taskID) {
        $task = Task::findOrFail($taskID);
        $task->unitPrice = $request->unitPrice;
        $task->qty = $request->qty;
        $task->uom = $request->uom;
        $task->save();

        return response()->json(['message' => 'Task updated successfully']);
    }

    public function calendar($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $role = $user->role;
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];
        $project = Project::findOrFail($projectID);
        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Get report IDs related to the project
        $reportIDs = Report::where('projectID', $projectID)->pluck('reportID');

        // Get invoices and quotations linked to reports
        $invoices = Invoice::whereIn('reportID', $reportIDs)->get(['invoiceNo', 'dueDate']);
        $quotations = Quotation::whereIn('reportID', $reportIDs)->get(['quotationNo', 'validityEnd']);

        // Get payments from invoices (Include paymentType)
        $paymentsFromInvoices = Payment::whereIn('invoiceNo', $invoices->pluck('invoiceNo'))
                ->get(['invoiceNo', 'paymentAmount', 'paymentType']);

        // Get payments from quotations (Include paymentType)
        $paymentsFromQuotations = Payment::whereIn('quotationNo', $quotations->pluck('quotationNo'))
                ->get(['quotationNo', 'paymentAmount', 'paymentType']);

        $events = [];

        // Process Invoices (Combine Due Date and Payment into 1 Event)
        foreach ($invoices as $invoice) {
            $payment = $paymentsFromInvoices->firstWhere('invoiceNo', $invoice->invoiceNo);

            if ($payment) {
                $paymentInfo = $payment->paymentType . ' - RM' . number_format($payment->paymentAmount, 2);
            } else {
                $paymentInfo = 'No payment record';
            }

            $events[] = [
                'title' => 'Invoice Due (' . $invoice->dueDate . ') - ' . $paymentInfo,
                'start' => $invoice->dueDate,
                'color' => '#f0320f' // Red for invoice due
            ];
        }

        // Process Quotations (Combine Validity End and Payment into 1 Event)
        foreach ($quotations as $quotation) {
            $payment = $paymentsFromQuotations->firstWhere('quotationNo', $quotation->quotationNo);

            if ($payment) {
                $paymentInfo = $payment->paymentType . ' - RM' . number_format($payment->paymentAmount, 2);
            } else {
                $paymentInfo = 'No payment record';
            }

            $events[] = [
                'title' => 'Quotation Validity (' . $quotation->validityEnd . ') - ' . $paymentInfo,
                'start' => $quotation->validityEnd,
                'color' => '#f0320f' // Red for quotation validity
            ];
        }

        return view('calendar', compact('events', 'role', 'project', 'recentProjects', 'startDate', 'endDate'));
    }

    public function receipt($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role; // Fetch the role from the authenticated user

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];
        $project = Project::findOrFail($projectID);

        // Ensure the user has access to the project
        $contractorID = Contractor::where('userID', $user->userID)->value('contractorID');
        $ownerID = Homeowner::where('userID', $user->userID)->value('ownerID');

        if ($project->contractorID !== $contractorID && $project->ownerID !== $ownerID) {
            return redirect()->route('errorPage')->with('error', 'You do not have access to this project.');
        }

        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Find related reports for the specific project
        $reportIDs = Report::where('projectID', $projectID)->pluck('reportID');

        // Find invoices and quotations related to the specific project
        $invoices = Invoice::whereIn('reportID', $reportIDs)->pluck('invoiceNo');
        $quotations = Quotation::whereIn('reportID', $reportIDs)->pluck('quotationNo');

        // Find payments related to invoices and quotations for the specific project
        $payments = Payment::whereIn('invoiceNo', $invoices)
                ->orWhereIn('quotationNo', $quotations)
                ->get(['paymentID', 'invoiceNo', 'quotationNo', 'paymentAmount', 'paymentDate', 'paymentStatus']);

        // Fetch additional details for each payment
        foreach ($payments as $payment) {
            $invoice = Invoice::where('invoiceNo', $payment->invoiceNo)->first();
            $quotation = Quotation::where('quotationNo', $payment->quotationNo)->first();

            // Get Report ID
            $reportID = $invoice ? $invoice->reportID : ($quotation ? $quotation->reportID : null);

            // Get Project ID
            $project = $reportID ? Project::where('projectID', Report::where('reportID', $reportID)->value('projectID'))->first() : null;

            // Get Contractor Name
            if ($project) {
                $contractorUserID = Contractor::where('contractorID', $project->contractorID)->value('userID');
                $payment->contractorName = User::where('userID', $contractorUserID)->value('userName');
            } else {
                $payment->contractorName = "Unknown";
            }

            // Get Due Date / Validity End
            $payment->dueDate = $invoice ? $invoice->dueDate : ($quotation ? $quotation->validityEnd : "N/A");

            // Get Project Name
            $payment->projectName = $project ? $project->projectName : "Unknown";

            // Get Importance Level (Payment Status)
            $payment->importanceLevel = $payment->paymentStatus;
        }

        $selectedPaymentID = request()->query('paymentID');

        return view('receipt', compact('project', 'role', 'recentProjects', 'payments', 'startDate', 'endDate', 'selectedPaymentID'));
    }

    public function getPaymentDetails($paymentID) {
        // Find Payment Record
        $payment = Payment::where('paymentID', $paymentID)->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found']);
        }

        // Get Invoice or Quotation details
        $invoice = Invoice::where('invoiceNo', $payment->invoiceNo)->first();
        $quotation = Quotation::where('quotationNo', $payment->quotationNo)->first();

        // Get Task IDs from TaskReport table using invoiceNo or quotationNo
        $taskReports = TaskReport::where('invoiceNo', $payment->invoiceNo)
                ->orWhere('quotationNo', $payment->quotationNo)
                ->get(['taskID']);

        // Get Report ID
        $reportID = $invoice ? $invoice->reportID : ($quotation ? $quotation->reportID : null);

        // Log the report ID
        Log::info("Report ID: {$reportID}");

        // Get Project ID and Name
        $project = $reportID ? Project::where('projectID', Report::where('reportID', $reportID)->value('projectID'))->first() : null;
        $projectName = $project ? $project->projectName : "Unknown";

        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Log the project details
        Log::info("Project details:", $project ? $project->toArray() : ['message' => 'Project not found']);

        // Get Contractor Name
        if ($project) {
            $contractorUserID = Contractor::where('contractorID', $project->contractorID)->value('userID');
            $contractorName = User::where('userID', $contractorUserID)->value('userName');
        } else {
            $contractorName = "Unknown";
        }

        // Log the contractor name
        Log::info("Contractor Name: {$contractorName}");

        // Get Due Date / Validity End
        $dueDate = $invoice ? $invoice->dueDate : ($quotation ? $quotation->validityEnd : "N/A");

        // Log the due date
        Log::info("Due Date: {$dueDate}");

        // Get Importance Level (Payment Status)
        $importanceLevel = $payment->paymentStatus;

        // Log the importance level
        Log::info("Importance Level (Payment Status): {$importanceLevel}");

        // Extract Task IDs
        $taskIDs = $taskReports->pluck('taskID');

        // Get Task Details from Task table
        $tasks = Task::whereIn('taskID', $taskIDs)
                ->get(['taskID', 'taskName', 'qty', 'uom', 'unitPrice']);

        // Calculate Budget for Each Task
        $tasksWithBudget = $tasks->map(function ($task) {
            $task->budget = (float) ($task->unitPrice * $task->qty); // Ensure budget is a number
            return $task;
        });

        // Return JSON Response
        return response()->json([
                    'success' => true,
                    'contractorName' => $contractorName,
                    'dueDate' => $dueDate,
                    'projectName' => $projectName,
                    'importanceLevel' => $importanceLevel,
                    'receipt' => $payment->receipt,
                    'remarks' => $payment->remarks,
                    'paymentAmount' => $payment->paymentAmount,
                    'tasks' => $tasksWithBudget,
                    'invoiceNo' => $payment->invoiceNo,
                    'quotationNo' => $payment->quotationNo,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
        ]);
    }

    public function uploadReceipt(Request $request) {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'receipt' => 'required|mimes:pdf,png|max:2048',
            'paymentID' => 'required|exists:payments,paymentID',
            'remarks' => 'nullable|string|max:300'
        ]);

        $payment = Payment::findOrFail($request->paymentID);

        // Save the file to storage/receipts
        $originalName = pathinfo($request->file('receipt')->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $request->file('receipt')->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $fileName = $originalName . '_' . $timestamp . '.' . $extension;
        $filePath = $request->file('receipt')->storeAs('receipts', $fileName, 'public');

        // Update the payment record
        $payment->update([
            'receipt' => '/storage/' . $filePath,
            'remarks' => $request->input('remarks'),
            'paymentStatus' => 'pending' // Set payment status to pending
        ]);

        // Send email notification to contractor
        $this->sendReceiptUploadNotification($payment);

        return response()->json([
                    'success' => true,
                    'message' => 'Receipt uploaded successfully',
                    'fileName' => $fileName,
                    'remarks' => $request->input('remarks')
        ]);
    }

    private function sendReceiptUploadNotification($payment) {
        $contractor = $payment->invoice->report->project->contractor->user ?? $payment->quotation->report->project->contractor->user;

        if ($contractor) {
            $invoiceNo = $payment->invoiceNo ?? $payment->quotationNo;
            $projectName = $payment->invoice->report->project->projectName ?? $payment->quotation->report->project->projectName;

            $data = [
                'invoiceNo' => $invoiceNo,
                'projectName' => $projectName,
                'receiptUrl' => url($payment->receipt),
                'checkUrl' => route('projectCost', ['projectID' => $payment->invoice->report->project->projectID ?? $payment->quotation->report->project->projectID]) . '?paymentID=' . $payment->paymentID
            ];

            Mail::to($contractor->email)->send(new ReceiptUploadNotification($data));
        }
    }

    public function viewReceipt($paymentID) {
        $payment = Payment::findOrFail($paymentID);

        return view('view_receipt', compact('payment'));
    }

    public function confirmReceipt($paymentID) {
        $payment = Payment::findOrFail($paymentID);
        $payment->update(['paymentStatus' => 'paid']);

        return response()->json(['success' => true]);
    }

    public function rejectReceipt($paymentID) {
        $payment = Payment::findOrFail($paymentID);

        // Remove the receipt file
        if ($payment->receipt) {
            $filePath = public_path($payment->receipt);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Update payment status to unpaid
        $payment->update([
            'paymentStatus' => 'unpaid',
            'receipt' => null,
            'remarks' => null
        ]);

        // Send email notification to homeowner
        $homeowner = $payment->invoice->report->project->homeowner->user ?? $payment->quotation->report->project->homeowner->user;

        if ($homeowner) {
            $data = [
                'invoiceNo' => $payment->invoiceNo ?? $payment->quotationNo,
                'projectName' => $payment->invoice->report->project->projectName ?? $payment->quotation->report->project->projectName
            ];

            Mail::to($homeowner->email)->send(new ReceiptRejectionNotification($data));
        }

        return response()->json(['success' => true]);
    }

    public function getLabourCost($projectID) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $contractor = $user->contractor;
        $role = $user->role;

        // Ensure the user has access to the project
        $contractorProject = $contractor ? Project::where('projectID', $projectID)
                        ->where('contractorID', $contractor->contractorID)
                        ->first() : null;

        if (!$contractorProject) {
            return redirect()->route('errorPage')->with('error', 'Project not found or you do not have access.');
        }

        $project = $contractorProject;
        $startDate = $project->startDate;
        $endDate = $project->endDate;

        // Retrieve tasks for the specific project
        $tasks = Task::where('projectID', $projectID)->get();

        $workers = [];

        foreach ($tasks as $task) {
            $assignment = Assignment::where('taskID', $task->taskID)->first();

            if ($assignment) {
                $worker = ContractorWorker::where('workerID', $assignment->workerID)->first();

                if ($worker) {
                    // Debugging: Check if the worker and user relationships exist
                    if (!$worker->worker) {
                        \Log::error("Worker not found for assignment: " . $assignment->workerID);
                        continue;
                    }
                    if (!$worker->worker->user) {
                        \Log::error("User not found for worker: " . $worker->workerID);
                        continue;
                    }

                    $daysWorked = $task->duration; // Assuming duration is in days

                    if (isset($workers[$worker->workerID])) {
                        $workers[$worker->workerID]['daysWorked'] += $daysWorked;
                    } else {
                        $workers[$worker->workerID] = [
                            'workerID' => $worker->workerID,
                            'userName' => $worker->worker->user->userName,
                            'ratePerDay' => $worker->dailyPay,
                            'daysWorked' => $daysWorked,
                        ];
                    }
                }
            }
        }

        // Convert workers array to collection
        $workers = collect($workers)->values();

        // Calculate total labour cost
        $totalLabourCost = $workers->sum(function ($worker) {
            return $worker['daysWorked'] * $worker['ratePerDay'];
        });

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        // Pass $totalLabourCost to the view
        return view('labourCost', compact('recentProjects', 'role', 'project', 'contractor', 'workers', 'totalLabourCost', 'startDate', 'endDate'));
    }

    public function updateWorkerRate(Request $request, $workerID) {
        // Validate the request
        $request->validate([
            'ratePerDay' => 'required|numeric|min:0',
        ]);

        // Find the worker
        $worker = ContractorWorker::where('workerID', $workerID)->first();

        if (!$worker) {
            return response()->json(['success' => false, 'message' => 'Worker not found'], 404);
        }

        // Update the worker's daily pay rate
        $worker->dailyPay = $request->ratePerDay;
        $worker->save();

        // Return a success response
        return response()->json(['success' => true, 'message' => 'Worker rate updated successfully']);
    }

    public function getDocument(Request $request) {
        $invoiceNo = $request->query('invoiceNo');
        $quotationNo = $request->query('quotationNo');

        Log::info("Fetching document for invoiceNo: {$invoiceNo}, quotationNo: {$quotationNo}");

        if (!$invoiceNo && !$quotationNo) {
            Log::error("Both invoiceNo and quotationNo are missing.");
            return response()->json([
                        'success' => false,
                        'message' => 'Both invoiceNo and quotationNo are missing.'
            ]);
        }

        $document = Documents::where('documentName', 'like', "{$invoiceNo}%")
                ->orWhere('documentName', 'like', "{$quotationNo}%")
                ->first();

        if ($document) {
            return response()->json([
                        'success' => true,
                        'fileURL' => asset($document->fileContent)
            ]);
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Document not found'
            ]);
        }
    }

    public function work() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        // Get the authenticated user
        $user = Auth::user();

        $role = $user->role;

        // Log the authenticated user for debugging
        Log::info('Authenticated User:', ['user' => $user]);

        // Initialize variables
        $tasks = collect();
        $recentProjects = [];

        // Check if the user is a contractor
        $contractor = $user->contractor;
        if ($contractor) {
            Log::info('Contractor found:', ['contractor' => $contractor]);

            // Fetch tasks assigned to the contractor from the assignments table
            $tasks = Task::whereHas('assignments', function ($query) use ($contractor) {
                        $query->where('contractorID', $contractor->contractorID);
                    })
                    ->with(['assignments.worker', 'assignments.contractor.user', 'project'])  // Eager load contractor's user relationship
                    ->orderBy('endDate', 'asc')
                    ->get();

            // Log the retrieved tasks and assignments
            Log::info('Retrieved Tasks for Contractor:', ['tasks' => $tasks]);

            // Get recent projects for the contractor
            $recentProjects = $this->getRecentProjects($user->userID) ?? [];
        }

        // Check if the user is a worker
        $worker = $user->worker;
        if ($worker) {
            Log::info('Worker found:', ['worker' => $worker]);

            // Fetch tasks assigned to the worker from the assignments table
            $tasks = Task::whereHas('assignments', function ($query) use ($worker) {
                        $query->where('workerID', $worker->workerID);
                    })
                    ->with(['assignments.worker', 'assignments.contractor.user', 'project'])  // Eager load contractor's user relationship
                    ->orderBy('endDate', 'asc')
                    ->get();

            // Log the retrieved tasks and assignments
            Log::info('Retrieved Tasks for Worker:', ['tasks' => $tasks]);

            // Get recent projects for the worker
            $recentProjects = $this->getRecentProjects($user->userID) ?? [];
        }

        // If neither contractor nor worker, redirect with an error
        if (!$contractor && !$worker) {
            Log::warning('User is neither a contractor nor a worker.');
            return redirect()->route('errorPage')->with('error', 'You do not have access to this page.');
        }

        // Return the view with tasks and recent projects
        return view('work', compact('tasks', 'role', 'recentProjects'));
    }

    public function team() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;
        $recentProjects = $this->getRecentProjects($user->userID) ?? [];

        \Log::info('User details:', ['user' => $user]);

        $team = collect(); // Initialize an empty collection for workers
        $contractors = collect(); // Initialize an empty collection for contractors

        if ($user->contractor) {
            // User is a contractor
            $contractor = $user->contractor;

            if (!$contractor) {
                \Log::error('Contractor not found for user:', ['user' => $user]);
                return redirect()->route('errorPage')->with('error', 'Contractor not found for the user.');
            }

            // Fetch workers linked to this contractor
            $team = ContractorWorker::with(['worker.user']) // Eager load worker and user
                    ->where('contractorID', $contractor->contractorID)
                    ->get();
        } elseif ($user->worker) {
            // User is a worker
            $worker = $user->worker;

            if (!$worker) {
                \Log::error('Worker not found for user:', ['user' => $user]);
                return redirect()->route('errorPage')->with('error', 'Worker not found for the user.');
            }

            // Find the contractor(s) this worker is linked to
            $contractorWorkers = ContractorWorker::with(['contractor.user']) // Eager load contractor and user
                    ->where('workerID', $worker->workerID)
                    ->get();

            if ($contractorWorkers->isNotEmpty()) {
                // Extract contractors from the contractorWorkers collection
                $contractors = $contractorWorkers->map(function ($contractorWorker) {
                    return $contractorWorker->contractor;
                });

                // Fetch all workers under the same contractor(s)
                $contractorIDs = $contractors->pluck('contractorID')->toArray();
                $team = ContractorWorker::with(['worker.user'])
                        ->whereIn('contractorID', $contractorIDs)
                        ->get();
            }
        }

        return view('team', compact('team', 'contractors', 'role', 'recentProjects', 'user'));
    }

    public function getWorkerInfo($workerID) {
        $worker = Worker::with('user')->where('workerID', $workerID)->first();

        if (!$worker) {
            return response()->json(['success' => false, 'message' => 'Worker not found']);
        }

        return response()->json(['success' => true, 'worker' => $worker]);
    }

    public function inviteWorker(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'dailyPay' => 'nullable|numeric|min:0'
        ]);

        $dailyPay = $request->input('dailyPay');

        $user = Auth::user();
        $contractor = $user->contractor;

        if (!$contractor) {
            return response()->json(['message' => 'Contractor not found.'], 400);
        }

        // Check if the worker is already invited
        if (ContractorWorker::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Worker already invited.'], 400);
        }

        // Check if the email exists in the users table
        $existingUser = User::where('email', $request->email)->first();

        // If the user exists, find the corresponding worker
        $workerID = null;
        if ($existingUser) {
            $worker = Worker::where('userID', $existingUser->userID)->first();
            if ($worker) {
                $workerID = $worker->workerID;
            }
        }


        // Create the invitation record
        $invitation = new ContractorWorker();
        $invitation->contractorID = $contractor->contractorID;
        $invitation->email = $request->email;
        $invitation->status = 'pending';
        $invitation->dailyPay = $dailyPay;
        $invitation->workerID = $workerID; // Populate workerID if found
        $invitation->save();

        try {
            // Fetch the contractor's name
            $contractorName = $contractor->user->userName; // Assuming the contractor's name is stored in the users table
            // Send invitation email
            Mail::to($request->email)->send(new WorkerInvitationMail($invitation, $contractorName, $dailyPay));

            return response()->json(['message' => 'Invitation sent successfully!']);
        } catch (\Exception $e) {
            // If email fails, delete the invitation record
            $invitation->delete();

            // Log the error and return a failure message
            \Log::error('Failed to send invitation email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send invitation. Please try again.'], 500);
        }
    }

    public function accept($token) {
        \Log::info('Accepting invitation with token:', ['token' => $token]);

        try {
            // Find the invitation by token
            $invitation = ContractorWorker::findOrFail($token);
            \Log::info('Invitation found:', ['invitation' => $invitation]);

            // Check if the user already exists
            $user = User::where('email', $invitation->email)->first();

            if ($user) {
                \Log::info('User found:', ['user' => $user]);
                // If the user exists, log them in
                Auth::login($user);

                // Verify the user is authenticated
                if (Auth::check()) {
                    \Log::info('User authenticated successfully.');

                    // Update the invitation status to 'approved'
                    $invitation->status = 'accepted';
                    $invitation->save();

                    \Log::info('Invitation status updated to accepted.');

                    return redirect()->route('team')->with('success', 'Invitation accepted!');
                } else {
                    \Log::error('User authentication failed.');
                    return redirect()->route('errorPage')->with('error', 'Failed to authenticate user.');
                }
            } else {
                \Log::info('User not found, redirecting to registration page.');
                // If the user does not exist, redirect to the invitedRegister page with the token
                return redirect()->route('InvitedRegister', ['token' => $token])->with('info', 'Please register to accept the invitation.');
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Invalid or expired invitation token:', ['token' => $token]);
            return redirect()->route('errorPage')->with('error', 'Invalid or expired invitation link.');
        } catch (\Exception $e) {
            \Log::error('Error accepting invitation:', ['error' => $e->getMessage()]);
            return redirect()->route('errorPage')->with('error', 'An error occurred while processing your request.');
        }
    }

    public function removeWorker(Request $request) {
        // Validate the request
        $request->validate([
            'worker_id' => 'nullable|exists:contractor_worker,workerID', // worker_id can be null
            'email' => 'required_if:worker_id,null|email', // email is required if worker_id is null
        ]);

        // Get the authenticated contractor
        $contractor = Auth::user()->contractor;

        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action. Only contractors can remove workers.'], 403);
        }

        // Find the worker invitation record
        $workerInvitation = null;

        if ($request->worker_id) {
            // If worker_id is provided, find the record by worker_id and contractorID
            $workerInvitation = ContractorWorker::where('workerID', $request->worker_id)
                    ->where('contractorID', $contractor->contractorID)
                    ->first();
        } else {
            // If worker_id is null, find the record by email and contractorID
            $workerInvitation = ContractorWorker::where('email', $request->email)
                    ->where('contractorID', $contractor->contractorID)
                    ->first();
        }

        if (!$workerInvitation) {
            return response()->json(['success' => false, 'message' => 'Worker invitation not found.'], 404);
        }

        // Delete the worker invitation record
        $workerInvitation->delete();

        return response()->json(['success' => true, 'message' => 'Worker removed successfully.']);
    }

    public function resendInvitation(Request $request) {
        // Validate the request
        $request->validate([
            'worker_id' => 'nullable|exists:contractor_worker,workerID', // worker_id can be null
            'email' => 'required_if:worker_id,null|email', // email is required if worker_id is null
        ]);

        // Get the authenticated contractor
        $contractor = Auth::user()->contractor;

        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action. Only contractors can re-send invitations.'], 403);
        }

        // Find the worker invitation record
        $workerInvitation = null;

        if ($request->worker_id) {
            // If worker_id is provided, find the record by worker_id and contractorID
            $workerInvitation = ContractorWorker::where('workerID', $request->worker_id)
                    ->where('contractorID', $contractor->contractorID)
                    ->first();
        } else {
            // If worker_id is null, find the record by email and contractorID
            $workerInvitation = ContractorWorker::where('email', $request->email)
                    ->where('contractorID', $contractor->contractorID)
                    ->first();
        }

        if (!$workerInvitation) {
            return response()->json(['success' => false, 'message' => 'Worker invitation not found.'], 404);
        }

        try {
            // Fetch the contractor's name
            $contractorName = $contractor->user->userName; // Assuming the contractor's name is stored in the users table
            // Send the invitation email
            Mail::to($workerInvitation->email)->send(new WorkerInvitationMail($workerInvitation, $contractorName, $workerInvitation->dailyPay));

            return response()->json(['success' => true, 'message' => 'Invitation re-sent successfully!']);
        } catch (\Exception $e) {
            // Log the error and return a failure message
            \Log::error('Failed to re-send invitation email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to re-send invitation. Please try again.'], 500);
        }
    }

    public function issues(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        $recentProjects = $this->getRecentProjects($user->userID) ?? [];
        $contractor = $user->contractor;
        $homeowner = $user->homeowner;

        $projectIDs = collect();

        if ($contractor) {
            $projectIDs = Project::where('contractorID', $contractor->contractorID)->pluck('projectID');
        }

        if ($homeowner) {
            $homeownerProjectIDs = Project::where('ownerID', $homeowner->ownerID)->pluck('projectID');
            $projectIDs = $projectIDs->merge($homeownerProjectIDs);
        }

        $taskIDs = Task::whereIn('projectID', $projectIDs)->pluck('taskID');
        $warrantyNos = Task::whereIn('taskID', $taskIDs)->pluck('warrantyNo')->filter();
        $warrantyRequestIDs = WarrantyRequest::whereIn('warrantyNo', $warrantyNos)->pluck('requestID');

        // Fetch issues with their service reports
        $issues = Issues::with('serviceReport')
                ->whereIn('requestID', $warrantyRequestIDs);

        // Handle sorting by creation time
        if ($request->has('sort')) {
            $sortOrder = $request->input('sort') === 'asc' ? 'asc' : 'desc';
            $issues = $issues->orderBy('created_at', $sortOrder);
        } else {
            $issues = $issues->orderBy('created_at', 'desc'); // Default to descending
        }

        $issues = $issues->get();

        $reportData = null;

        return view('issues', compact('recentProjects', 'role', 'issues', 'reportData', 'contractor'));
    }

    public function deleteIssues(Request $request) {
        $request->validate([
            'selectedIssues' => 'required|array',
            'selectedIssues.*' => 'exists:issues,issuesID',
        ]);

        $selectedIssues = $request->input('selectedIssues');

        // Check if any selected issue has a service report
        $hasServiceReport = Issues::whereIn('issuesID', $selectedIssues)
                ->whereNotNull('serviceNo')
                ->exists();

        if ($hasServiceReport) {
            return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete issues with an existing service report.',
                            ], 400);
        }

        // Delete the selected issues
        Issues::whereIn('issuesID', $selectedIssues)->delete();

        return response()->json([
                    'success' => true,
                    'message' => 'Selected issues deleted successfully.',
        ]);
    }

    public function updateIssues(Request $request, $issueID) {
        $request->validate([
            'field' => 'required|in:issuesStatus,severity,budget,dueDate',
            'value' => 'required',
        ]);

        $issue = Issues::findOrFail($issueID);

        // Prevent updates if a service report exists
        if ($issue->serviceNo) {
            return response()->json([
                        'success' => false,
                        'message' => 'Cannot update issues with an existing service report.',
                            ], 400);
        }

        // Update the specified field
        $issue->{$request->field} = $request->value;
        $issue->save();

        return response()->json(['success' => true]);
    }

    public function saveReport(Request $request) {
        try {
            // Validate the request
            $request->validate([
                'content' => 'required|string', // HTML content of the report
                'reportData' => 'required|array', // Report data containing all necessary fields
            ]);

            // Extract report data from the request
            $reportData = $request->input('reportData');

            // Validate required fields in reportData
            if (
                    !isset($reportData['personInCharge']) ||
                    !isset($reportData['personTel']) ||
                    !isset($reportData['totalAmount']) ||
                    !isset($reportData['selectedIssues'])
            ) {
                return response()->json([
                            'success' => false,
                            'message' => 'Required fields are missing in report data.',
                                ], 400);
            }

            // Generate a unique service report number
            $year = now()->format('y');
            $lastServiceReport = ServiceReport::where('serviceNo', 'like', "CS-$year%")
                    ->orderBy('serviceNo', 'desc')
                    ->first();

            $nextNumber = $lastServiceReport ? (int) substr($lastServiceReport->serviceNo, 5) + 1 : 1;
            $serviceNo = "CS-$year" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Generate the PDF from the HTML content
            $pdf = Pdf::loadHTML($request->input('content'));

            // Save the PDF to storage
            $fileName = $serviceNo . '-service-report.pdf';
            $filePath = "serviceReport/" . $fileName;
            Storage::disk('public')->put($filePath, $pdf->output());

            // Get the public URL for the saved file
            $fileUrl = Storage::url($filePath);

            // Create a new ServiceReport record
            $serviceReport = new ServiceReport();
            $serviceReport->serviceNo = $serviceNo;
            $serviceReport->serviceDate = now()->toDateString();
            $serviceReport->contactPerson = $reportData['personInCharge']; // From reportData
            $serviceReport->contactNo = $reportData['personTel']; // From reportData
            $serviceReport->totalAmount = $reportData['totalAmount']; // From reportData
            $serviceReport->paymentInstruction = null; // Can be updated later
            $serviceReport->remarks = null; // Can be updated later
            $serviceReport->reportContent = $filePath; // Store the file path
            $serviceReport->save();

            // Update the selected issues with the serviceNo
            Issues::whereIn('issuesID', $reportData['selectedIssues'])
                    ->update(['serviceNo' => $serviceNo]);

            return response()->json([
                        'success' => true,
                        'filePath' => $fileUrl, // Public URL of the saved file
                        'serviceNo' => $serviceNo, // Generated service number
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save report: ' . $e->getMessage());
            return response()->json([
                        'success' => false,
                        'message' => 'Failed to save report. Please try again.',
                            ], 500);
        }
    }

    public function generateServiceReport(Request $request) {
        try {
            // Validate the request
            $request->validate([
                'selectedIssues' => 'required|array',
                'selectedIssues.*' => 'string', // Ensure each issue ID is a string
            ]);

            // Fetch the selected issues
            $selectedIssues = $request->input('selectedIssues');
            $issues = Issues::whereIn('issuesID', $selectedIssues)->get();

            // Check if any of the selected issues already have a service report
            $hasExistingReport = $issues->some(function ($issue) {
                return !is_null($issue->serviceNo); // Check if serviceNo is not null
            });

            if ($hasExistingReport) {
                return response()->json([
                            'success' => false,
                            'message' => 'One or more selected issues already have a service report.',
                                ], 400);
            }

            if ($issues->isEmpty()) {
                return response()->json([
                            'success' => false,
                            'message' => 'No issues found for the selected IDs.',
                                ], 404);
            }

            // Get the authenticated user
            $user = Auth::user();
            $contractor = $user->contractor;

            if (!$contractor) {
                return response()->json([
                            'success' => false,
                            'message' => 'Contractor not found for the user.',
                                ], 404);
            }

            // Get the contractor's user details
            $contractorUser = User::find($contractor->userID);
            if (!$contractorUser) {
                return response()->json([
                            'success' => false,
                            'message' => 'User details not found for the contractor.',
                                ], 404);
            }

            // Use the first issue to trace back to the project
            $firstIssue = $issues->first();
            $warrantyRequest = WarrantyRequest::where('requestID', $firstIssue->requestID)->first();

            if (!$warrantyRequest) {
                return response()->json([
                            'success' => false,
                            'message' => 'Warranty request not found for the issue.',
                                ], 404);
            }

            $task = Task::where('warrantyNo', $warrantyRequest->warrantyNo)->first();
            if (!$task) {
                return response()->json([
                            'success' => false,
                            'message' => 'Task not found for the warranty request.',
                                ], 404);
            }

            $project = Project::find($task->projectID);
            if (!$project) {
                return response()->json([
                            'success' => false,
                            'message' => 'Project not found for the task.',
                                ], 404);
            }

            $homeowner = $project->homeowner;
            if (!$homeowner) {
                return response()->json([
                            'success' => false,
                            'message' => 'Homeowner not found for the project.',
                                ], 404);
            }

            $homeownerUser = User::find($homeowner->userID);
            if (!$homeownerUser) {
                return response()->json([
                            'success' => false,
                            'message' => 'Homeowner user details not found.',
                                ], 404);
            }

            // Generate a unique service number
            $year = now()->format('y');
            $lastServiceReport = ServiceReport::where('serviceNo', 'like', "CS-$year%")
                    ->orderBy('serviceNo', 'desc')
                    ->first();

            $nextNumber = $lastServiceReport ? (int) substr($lastServiceReport->serviceNo, 5) + 1 : 1;
            $serviceNo = "CS-$year" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Fetch warranty expiry date
            $warrantyExpiry = null;
            $warranty = Warranty::where('warrantyNo', $warrantyRequest->warrantyNo)->first();
            if ($warranty) {
                $warrantyExpiry = \Carbon\Carbon::parse($warranty->endDate)->format('d M Y');
            }



            // Set company logo path
            $companyLogo = asset('images/AlloymontLogo.png'); // Default logo
            if ($contractor->companyLogo && file_exists(public_path($contractor->companyLogo))) {
                $companyLogo = asset($contractor->companyLogo);
            }

            // Prepare report data
            $reportData = [
                'businessId' => $contractor->registerNo,
                'companyLogo' => $companyLogo,
                'companyName' => $contractor->companyName,
                'businessAddress' => $contractor->businessAddress,
                'businessTel' => $contractorUser->userPhone,
                'businessEmail' => $contractorUser->email,
                'clientName' => $homeownerUser->userName,
                'clientTel' => $homeownerUser->userPhone,
                'clientEmail' => $homeownerUser->email,
                'projectName' => $project->projectName,
                'projectAddress' => $project->projectAddress,
                'serviceNo' => $serviceNo,
                'warrantyExpiry' => $warrantyExpiry,
                'reportDate' => now()->format('d M Y'),
                'personInCharge' => $firstIssue->issueHandler,
                'personTel' => $contractorUser->userPhone,
                'issueName' => $firstIssue->issuesName,
                'quotationRows' => $issues->map(function ($issue) {
                    return [
                'description' => $issue->issuesName,
                'quantity' => 1,
                'unitPrice' => $issue->budget,
                'total' => $issue->budget,
                    ];
                }),
                'totalAmount' => $issues->sum('budget'),
            ];

            // Render the HTML content
            $html = view('serviceReport', compact('reportData'))->render();

            return response()->json([
                        'success' => true,
                        'html' => $html,
                        'reportData' => $reportData,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate report: ' . $e->getMessage());
            return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate report. Please try again.',
                            ], 500);
        }
    }

    public function downloadReport(Request $request) {
        try {
            // Validate the request
            $request->validate([
                'content' => 'required|string', // HTML content of the report
            ]);

            // Generate the PDF
            $pdf = Pdf::loadHTML($request->input('content'));

            // Download the PDF
            return $pdf->download('service-report.pdf');
        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF: ' . $e->getMessage());
            return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate PDF. Please try again.',
                            ], 500);
        }
    }
}
