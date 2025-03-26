<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Contractor;
use App\Models\Assignment;
use App\Models\Homeowner;
use App\Models\Worker;
use App\Models\ContractorWorker;
use App\Models\Project;
use App\Models\Task;
use App\Models\Warranty;
use App\Models\WarrantyRequest;
use App\Models\Issues;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller {

    public function showLoginPage(Request $request) {
        return view('login');
    }

    public function login(Request $request) {
        // Validate input fields
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            \Log::info('Authenticated User: ', ['user' => Auth::user()]);

            $user = Auth::user();
            session(['userID' => $user->id]);

            return redirect()->intended('home')->with('success', 'Login successful!');
        }

        \Log::error('Login failed for email: ' . $request->email);
        return back()->withErrors([
                    'password' => 'Invalid email or password.',
                ])->withInput();
    }

    public function showInvitedRegisterPage(Request $request, $token = null) {
        // Retrieve the invitation token from the URL or session
        $token = $token ?? $request->query('token');

        // Validate the token
        $invitation = ContractorWorker::where('id', $token)->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'Invalid or expired invitation link.');
        }

        // Pre-fill the email field if the invitation exists
        $email = $invitation->email;

        return view('invitedRegister', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function invitedRegister(Request $request) {
        // Validate the form data
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'required',
                'regex:/^(01[0-9]-?\d{7,8}|0[3-9]-?\d{6,8})$/',
                'unique:users,userPhone',
            ],
            'password' => 'required|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
            'token' => 'required|exists:contractor_worker,id', // Ensure the token is valid
        ]);

        // Retrieve the invitation
        $invitation = ContractorWorker::findOrFail($request->token);

        // Generate a unique user ID
        $currentYear = now()->format('y');
        $latestUser = User::where('userID', 'like', "U{$currentYear}%")
                ->orderBy('userID', 'desc')
                ->first();
        $newUserNumber = $latestUser ? str_pad(((int) substr($latestUser->userID, 3)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $userID = "U{$currentYear}{$newUserNumber}";

        while (User::where('userID', $userID)->exists()) {
            $newUserNumber = str_pad(((int) substr($userID, 3)) + 1, 4, '0', STR_PAD_LEFT);
            $userID = "U{$currentYear}{$newUserNumber}";
        }

        // Generate a unique workerID
        $surname = strtoupper(substr($request->full_name, 0, 2));
        $latestWorker = Worker::where('workerID', 'like', "{$surname}{$currentYear}%")
                ->orderBy('workerID', 'desc')
                ->first();
        $newNumber = $latestWorker ? str_pad(((int) substr($latestWorker->workerID, 4)) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $workerID = "{$surname}{$currentYear}{$newNumber}";

        // Create the new user
        $user = User::create([
                    'userID' => $userID,
                    'userName' => $request->full_name,
                    'email' => $request->email,
                    'userPhone' => $request->phone,
                    'userGender' => null, // Optional field
                    'password' => Hash::make($request->password),
                    'role' => 'worker', // Set the role to 'worker'
        ]);

        // Create a new worker record
        $worker = Worker::create([
                    'workerID' => $workerID,
                    'availabilityStatus' => 'available', // Default status
                    'workerType' => 'Full-time', // Default type
                    'userID' => $userID, // Use the same userID as in the users table
        ]);

        // Update invitation record (link workerID and update status)
        $invitation->workerID = $worker->workerID;
        $invitation->status = 'accepted';
        $invitation->save();

        // Log in the user
        Auth::login($user);

        // Redirect to team page with success message
        return redirect()->route('team')->with('success', 'Registration successful!');
    }

    public function showRegisterPage() {

        return view('register');
    }

    public function register(Request $request) {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'required',
                'regex:/^(01[0-9]-?\d{7,8}|0[3-9]-?\d{6,8})$/',
                'unique:users,userPhone',
            ],
            'password' => 'required|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
        ]);

        // Generate unique user ID
        $currentYear = now()->format('y');
        $latestUser = User::where('userID', 'like', "U{$currentYear}%")
                ->orderBy('userID', 'desc')
                ->first();
        $newNumber = $latestUser ? str_pad(((int) substr($latestUser->userID, 3)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $userID = "U{$currentYear}{$newNumber}";

        while (User::where('userID', $userID)->exists()) {
            $newNumber = str_pad(((int) substr($userID, 3)) + 1, 4, '0', STR_PAD_LEFT);
            $userID = "U{$currentYear}{$newNumber}";
        }

        // Create new user
        $user = User::create([
                    'userID' => $userID,
                    'userName' => $request->full_name,
                    'email' => $request->email,
                    'userPhone' => $request->phone,
                    'userGender' => null,
                    'password' => Hash::make($request->password),
        ]);

        // Store userID in session
        session(['userID' => $userID]);

        // Log in the user
        Auth::loginUsingId($user->userID);

        // Redirect to role selection (preserving token)
        return redirect()->route('role');
    }

    // Show Role Selection page
    public function showRolePage() {
        return view('role');
    }

    public function handleRoleSelection(Request $request) {
        // Validate that one of the roles is selected
        $request->validate([
            'user_type' => 'required|in:c,h,w',
        ]);

        switch ($request->user_type) {
            case 'c':
                return redirect()->route('registerContractor');
            case 'h':
                return redirect()->route('registerOwner');
            case 'w':
                return redirect()->route('registerWorker');
            default:
                return back()->with('error', 'Invalid selection. Please choose a valid role.');
        }
    }

    public function registerContractor(Request $request) {
        return view('registerContractor'); // Pass token to view
    }

    public function handleRegisterContractor(Request $request) {
        $userID = session('userID');
        $user = User::where('userID', $userID)->firstOrFail();
        $surname = strtoupper(substr($user->userName, 0, 2));
        $currentYear = now()->format('y');
        $latestContractor = Contractor::where('contractorID', 'like', "{$surname}{$currentYear}%")
                ->orderBy('contractorID', 'desc')
                ->first();

        $newNumber = $latestContractor ? str_pad(((int) substr($latestContractor->contractorID, 4)) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $contractorID = "{$surname}{$currentYear}{$newNumber}";

        $request->validate([
            'companyName' => 'required|string|max:40',
            'businessAddress' => 'required|string|max:100',
            'registerNo' => ['required', 'regex:/^20\d{10}$/'],
        ]);

        Contractor::create([
            'contractorID' => $contractorID,
            'companyName' => $request->companyName,
            'businessAddress' => $request->businessAddress,
            'registerNo' => $request->registerNo,
            'userID' => $userID,
        ]);

        return redirect()->route('login')->with('message', 'Please login or register to accept the invitation.');
        // Redirect to login page after successful registration
    }

    public function registerHomeowner(Request $request) {
        $token = $request->query('token'); // Get token from query parameter
        return view('registerOwner', compact('token'));  // Pass token to the view
    }

    public function handleRegisterHomeowner(Request $request) {
        $userID = session('userID');
        $user = User::where('userID', $userID)->firstOrFail();
        $surname = strtoupper(substr($user->userName, 0, 2));
        $currentYear = now()->format('y');
        $latestOwner = Homeowner::where('ownerID', 'like', "{$surname}{$currentYear}%")
                ->orderBy('ownerID', 'desc')
                ->first();

        $newNumber = $latestOwner ? str_pad(((int) substr($latestOwner->ownerID, 4)) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $ownerID = "{$surname}{$currentYear}{$newNumber}";

        $request->validate([
            'homeAddress' => 'required|string|max:100',
        ]);

        Homeowner::create([
            'ownerID' => $ownerID,
            'homeAddress' => $request->homeAddress,
            'userID' => $userID,
        ]);

        // Assuming the token is available
        $token = session('pending_invite');

        session(['pending_invite' => $token]);
        return redirect()->route('login')->with('message', 'Please login or register to accept the invitation.');
        // Redirect to login page after successful registration
    }

    public function registerWorker(Request $request) {
        $token = $request->query('token'); // Get token from query parameter
        return view('registerWorker', compact('token'));
    }

    public function handleRegisterWorker(Request $request) {
        $userID = session('userID');
        $user = User::where('userID', $userID)->firstOrFail();
        $surname = strtoupper(substr($user->userName, 0, 2));
        $currentYear = now()->format('y');
        $latestWorker = Worker::where('workerID', 'like', "{$surname}{$currentYear}%")
                ->orderBy('workerID', 'desc')
                ->first();

        $newNumber = $latestWorker ? str_pad(((int) substr($latestWorker->workerID, 4)) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $workerID = "{$surname}{$currentYear}{$newNumber}";

        $request->validate([
            'availabilityStatus' => 'required|string|max:20',
            'workerType' => 'required|string|max:20',
        ]);

        Worker::create([
            'workerID' => $workerID,
            'availabilityStatus' => $request->availabilityStatus,
            'workerType' => $request->workerType,
            'userID' => $userID,
        ]);
        $token = session('pending_invite');

        session(['pending_invite' => $token]);
        return redirect()->route('login')->with('message', 'Please login or register to accept the invitation.');
        // Redirect to login page after successful registration
    }

    // Show forgot password page
    public function showForgotPasswordPage() {
        return view('forgotPwd');
    }

    // Handle forgot password request
    public function sendPasswordResetLink(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink([
                    'email' => $request->email, // Change 'email' to 'userEmail'
        ]);

        return $status === Password::RESET_LINK_SENT ? back()->with('status', 'Password reset link has been sent to your email.') : back()->withErrors(['email' => 'Unable to send reset link. Please try again later.']);
    }

// Show reset password page
    public function showResetPasswordPage(Request $request) {
        return view('resetPassword', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

// Handle reset password request
    public function handlePasswordReset(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email', // Use userEmail instead of email
            'password' => 'required|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
            'token' => 'required',
        ]);

        $status = Password::reset(
                        $request->only('email', 'password', 'password_confirmation', 'token'), // Change 'email' to 'userEmail'
                        function ($user, $password) {
                            $user->forceFill([
                                'password' => Hash::make($password),
                            ])->save();
                        }
        );
        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('success', 'Your password has been reset successfully.') : back()->withErrors(['email' => 'Failed to reset password. Please try again.']);
    }

    public function profile() {
        // Fetch authenticated user details
        $user = Auth::user();

        // Log the user retrieval process
        Log::info('Fetching profile for user ID: ' . $user->userID);

        // Default role
        $role = 'Unknown';

        // Check the user's role based on their userID
        if ($user->contractor()->exists()) {
            $role = 'Contractor';
        } elseif ($user->homeowner()->exists()) {
            $role = 'Homeowner';
        } elseif ($user->worker()->exists()) {
            $role = 'Worker';
        }

        // Log the detected role
        Log::info("User ID: {$user->userID} is identified as a {$role}");

        // Set default values for country and gender if not set
        $user->country = $user->country ?? 'Malaysia';
        $user->language = $user->language ?? 'English';

        // Pass user data and role to the view
        return view('userProfile', compact('user', 'role'));
    }

    public function updateGender(Request $request) {
        $request->validate([
            'gender' => 'required|in:Male,Female',
        ]);

        $user = Auth::user();
        $user->userGender = $request->gender;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Gender updated successfully']);
    }

    public function updatePhone(Request $request) {
        try {
            \Log::info('Phone update request received'); // Log to confirm request arrival
            // Validate phone number
            $request->validate([
                'phone' => [
                    'required',
                    'regex:/^(01[0-9]-?\d{7,8}|0[3-9]-?\d{6,8})$/',
                    'unique:users,userPhone,' . auth()->user()->userID . ',userID',
                ],
            ]);

            \Log::info('Phone number validated'); // Log to confirm validation passed
            // Update the authenticated user's phone number
            $user = auth()->user();
            $user->userPhone = $request->phone;
            $user->save();

            \Log::info('Phone number updated successfully'); // Log to confirm update

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error updating phone number: ' . $e->getMessage()); // Log the error message
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[0-9]/', // At least one digit
                'regex:/[@$!%*?&]/', // At least one special character
            ],
        ]);

        $user = Auth::user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => 'Password updated successfully']);
    }

    public function validateCurrentPassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        if (Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function uploadCompanyLogo(Request $request) {
        $request->validate([
            'company_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('company_logos', $filename, 'public');

            // Save the file path to the user's profile
            $user->contractor->companyLogo = $path;
            $user->contractor->save();

            return response()->json(['success' => true, 'message' => 'Company logo uploaded successfully', 'path' => asset('storage/' . $path)]);
        }

        return response()->json(['error' => 'File upload failed'], 400);
    }

    public function updateRoleDetails(Request $request) {
        $user = Auth::user();

        if ($user->contractor) {
            $request->validate([
                'companyName' => 'required|string|max:255',
                'businessAddress' => 'required|string|max:255',
                'registerNo' => 'required|string|max:255',
                'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user->contractor->update([
                'companyName' => $request->companyName,
                'businessAddress' => $request->businessAddress,
                'registerNo' => $request->registerNo,
            ]);

            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('company_logos', $filename, 'public');
                $user->contractor->companyLogo = $path;
                $user->contractor->save();
            }
        } elseif ($user->worker) {
            $request->validate([
                'workerType' => 'required|string|max:255',
                'availabilityStatus' => 'required|string|max:255',
            ]);

            $user->worker->update([
                'workerType' => $request->workerType,
                'availabilityStatus' => $request->availabilityStatus,
            ]);
        } elseif ($user->homeowner) {
            $request->validate([
                'homeAddress' => 'required|string|max:255',
            ]);

            $user->homeowner->update([
                'homeAddress' => $request->homeAddress,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function projectHistory() {
        // Get the logged-in user
        $user = Auth::user();

        $role = 'Unknown';

        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        if ($user->contractor()->exists()) {
            $role = 'Contractor';
        } elseif ($user->homeowner()->exists()) {
            $role = 'Homeowner';
        } elseif ($user->worker()->exists()) {
            $role = 'Worker';
        }

        // Log the logged-in user ID for debugging
        Log::info('Logged in user ID: ' . $user->userID);

        // Find the contractorID or homeownerID based on the user type
        $contractor = $user->contractor()->first(); // If user is a contractor
        $homeowner = $user->homeowner()->first(); // If user is a homeowner
        $worker = $user->worker()->first(); // If user is a worker

        if ($contractor) {
            // If user is a contractor, get the contractorID
            $contractorID = $contractor->contractorID;
            Log::info('User is a contractor. ContractorID: ' . $contractorID);

            // Retrieve the projects where the logged-in user is the contractor
            $projects = Project::where('contractorID', $contractorID)->get();
        } elseif ($homeowner) {
            // If user is a homeowner, get the homeownerID
            $homeownerID = $homeowner->ownerID;
            Log::info('User is a homeowner. HomeownerID: ' . $homeownerID);

            // Retrieve the projects where the logged-in user is the homeowner
            $projects = Project::where('ownerID', $homeownerID)->get();
        } elseif ($worker) {
            // If user is a worker, get the workerID
            $workerID = $worker->workerID;
            Log::info('User is a worker. WorkerID: ' . $workerID);

            // Retrieve assignments for the worker
            $assignments = Assignment::where('workerID', $workerID)->get();

            // Get unique task IDs from assignments
            $taskIDs = $assignments->pluck('taskID')->unique();

            // Retrieve tasks using the task IDs
            $tasks = Task::whereIn('taskID', $taskIDs)->get();

            // Get unique project IDs from tasks
            $projectIDs = $tasks->pluck('projectID')->unique();

            // Retrieve projects using the project IDs
            $projects = Project::whereIn('projectID', $projectIDs)->get();
        } else {
            // If neither contractor, homeowner, nor worker exists for the user, log a message
            Log::warning('No contractor, homeowner, or worker record found for user ID: ' . $user->userID);
            $projects = collect(); // Return an empty collection
        }

        // Log the query results to see if we fetched any projects
        Log::info('Projects fetched: ' . $projects->count());

        // Check if any projects were fetched, if not, log a message
        if ($projects->isEmpty()) {
            Log::warning('No projects found for user ID: ' . $user->userID);
        }

        // Separate active and past projects
        $activeProjects = $projects->filter(function ($project) {
            return $project->projectStatus === 'Active';
        });

        $pastProjects = $projects->filter(function ($project) {
            return $project->projectStatus === 'Completed';
        });

        // Add contractor user name for each project by eager loading the contractor's userName
        $activeProjects->each(function ($project) {
            $contractorUser = $project->contractor()->first()->user ?? null;
            $project->contractorName = $contractorUser ? $contractorUser->userName : 'N/A';
        });

        $pastProjects->each(function ($project) {
            $contractorUser = $project->contractor()->first()->user ?? null;
            $project->contractorName = $contractorUser ? $contractorUser->userName : 'N/A';
        });

        // Log the count of active and past projects
        Log::info('Active projects: ' . $activeProjects->count());
        Log::info('Past projects: ' . $pastProjects->count());

        // Pass both active and past projects to the view
        return view('projectHistory', [
            'activeProjects' => $activeProjects,
            'pastProjects' => $pastProjects,
            'role' => $role
        ]);
    }

    public function getWarrantyRecords(Request $request) {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not authenticated');
        }

        $role = 'Unknown';

        if ($user->contractor()->exists()) {
            $role = 'Contractor';
        } elseif ($user->homeowner()->exists()) {
            $role = 'Homeowner';
        } elseif ($user->worker()->exists()) {
            $role = 'Worker';
        }



        $contractor = $user->contractor;
        $homeowner = $user->homeowner;

        $isHomeowner = auth()->check() && auth()->user()->homeowner ? true : false;

        if (!$contractor && !$homeowner) {
            return redirect()->back()->with('error', 'User is neither a contractor nor a homeowner');
        }

        // Get projects linked to the contractor or homeowner
        $projects = Project::where(function ($query) use ($contractor, $homeowner) {
                    if ($contractor) {
                        $query->orWhere('contractorID', $contractor->contractorID);
                    }
                    if ($homeowner) {
                        $query->orWhere('ownerID', $homeowner->ownerID);
                    }
                })->pluck('projectID');

        if ($projects->isEmpty()) {
            Log::error("No projects found for user ID: " . $user->userID);
        }

        // Fetch warranties with related project details
        $warranties = Warranty::whereHas('task', function ($query) use ($projects) {
                    $query->whereIn('projectID', $projects);
                })
                ->with([
                    'task.project.homeowner',
                    'task.project.contractor'
                ])
                ->get();

        // Log warranty details if any exist
        if ($warranties->isEmpty()) {
            Log::info("No warranties found for user ID: " . $user->userID);
        }

        // Always return the warranty page, passing the warranties collection (which may be empty)
        return view('warranty', compact('warranties', 'isHomeowner', 'role'));
    }

    public function storeWarrantyRequest(Request $request) {
        \Log::info('Received Request Data:', $request->all());

        try {
            // Validate request input
            $validator = Validator::make($request->all(), [
                        'requestTitle' => 'required|string|max:30',
                        'requesterName' => 'required|string|max:50', // New validation
                        'requestDate' => 'required|date',
                        'requestDesc' => 'nullable|string|max:255',
                        'warrantyNo' => 'required|exists:warranties,warrantyNo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                            'success' => false,
                            'message' => $validator->errors()->first()
                                ], 400);
            }

            // Generate Request ID in the format WR<YY><NNN>
            $year = date('y');
            $latestRequest = WarrantyRequest::where('requestID', 'LIKE', "WR$year%")
                    ->orderBy('requestID', 'desc')
                    ->first();

            $newNumber = $latestRequest ? str_pad((int) substr($latestRequest->requestID, 4) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $requestID = "WR{$year}{$newNumber}";

            // Create Warranty Request with default status "pending"
            $warrantyRequest = WarrantyRequest::create([
                        'requestID' => $requestID,
                        'requestTitle' => $request->requestTitle,
                        'requesterName' => Auth::user()->userName,
                        'requestDate' => $request->requestDate,
                        'requestDesc' => $request->requestDesc,
                        'warrantyNo' => $request->warrantyNo,
                        'requestStatus' => 'pending', // Set default status
            ]);

            return response()->json([
                        'success' => true,
                        'message' => 'Warranty request submitted successfully!',
                        'requestID' => $requestID,
                        'data' => $warrantyRequest
                            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => 'An error occurred while processing the request.',
                        'error' => $e->getMessage()
                            ], 500);
        }
    }

    public function warrantyService() {
        $user = auth()->user();

        $role = 'Unknown';

        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        if ($user->contractor()->exists()) {
            $role = 'Contractor';
        } elseif ($user->homeowner()->exists()) {
            $role = 'Homeowner';
        } elseif ($user->worker()->exists()) {
            $role = 'Worker';
        }


        // Check if the user is a contractor
        $contractor = Contractor::where('userID', $user->userID)->first();
        $homeowner = Homeowner::where('userID', $user->userID)->first(); // Get homeowner if exists

        if ($contractor) {
            // If contractor, get projects assigned to them
            $projects = Project::where('contractorID', $contractor->contractorID)->pluck('projectID');
        } elseif ($homeowner) {
            // If homeowner, get projects they own (Fix: use Homeowner's ownerID, not userID)
            $projects = Project::where('ownerID', $homeowner->ownerID)->pluck('projectID');
        } else {
            // Neither contractor nor homeowner
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        // Get tasks that belong to these projects and have a warranty
        $tasksWithWarranties = Task::whereIn('projectID', $projects)
                ->whereNotNull('warrantyNo')
                ->with(['project', 'warranty'])
                ->get();

        // Find warranty requests related to the tasks' warranties
        $warrantyRequests = WarrantyRequest::whereIn('warrantyNo', $tasksWithWarranties->pluck('warrantyNo'))
                ->with('warranty')
                ->get()
                ->map(function ($request) use ($tasksWithWarranties) {
            $task = $tasksWithWarranties->firstWhere('warrantyNo', $request->warrantyNo);
            $request->projectAddress = $task?->project?->projectAddress ?? 'N/A';
            $request->task = $task;
            return $request;
        });

        return view('warrantyService', compact('warrantyRequests', 'tasksWithWarranties', 'role'));
    }

    public function denyRequest($id) {
        $user = auth()->user();
        $warrantyRequest = WarrantyRequest::find($id);

        if (!$warrantyRequest) {
            return response()->json(['success' => false, 'message' => 'Request not found'], 404);
        }

        // Get contractorID of logged-in user
        $contractor = Contractor::where('userID', $user->userID)->first();

        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        // Verify contractor is assigned to the project
        $task = Task::where('warrantyNo', $warrantyRequest->warrantyNo)->first();
        if (!$task || $task->project->contractorID !== $contractor->contractorID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        $warrantyRequest->requestStatus = 'denied';
        $warrantyRequest->save();

        return response()->json(['success' => true, 'message' => 'Request denied successfully']);
    }

    public function acceptRequest($id) {
        $user = auth()->user();
        $warrantyRequest = WarrantyRequest::find($id);

        if (!$warrantyRequest) {
            return response()->json(['success' => false, 'message' => 'Request not found'], 404);
        }

        $contractor = Contractor::where('userID', $user->userID)->first();
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        // Update request status to "accepted"
        $warrantyRequest->requestStatus = 'accepted';
        $warrantyRequest->save();

        // Generate new Issue ID in format S<YY><NNNN>
        $year = now()->format('y');
        $nextNumber = str_pad(Issues::where('issuesID', 'like', "S{$year}%")->count() + 1, 4, '0', STR_PAD_LEFT);
        $newIssueID = "S{$year}{$nextNumber}";

        // Create new Issue record
        $issue = new Issues();
        $issue->issuesID = $newIssueID;
        $issue->issuesName = $warrantyRequest->requestTitle;
        $issue->issuesStatus = 'Open';
        $issue->severity = null;
        $issue->budget = null;
        $issue->dueDate = null;
        $issue->requestID = $warrantyRequest->requestID;

        // Assign the handler as the logged-in user's name (from users table)
        $issue->issueHandler = $user->userName;

        $issue->save();

        return response()->json(['success' => true, 'message' => 'Request accepted and issue created']);
    }

    public function showLogout() {
        return view('logout');
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully!');
    }

    public function index() {
        
    }
}
