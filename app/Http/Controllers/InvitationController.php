<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\InviteMail;
use App\Models\Project;
use App\Models\Homeowner;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Str;

class InvitationController extends Controller {

    public function sendInvitation(Request $request) {
        \Log::info('Received invitation request:', ['request' => $request->all()]);

        try {
            // Validate input
            $validatedData = $request->validate([
                'email' => 'required|email',
                'projectID' => 'required|exists:projects,projectID',
            ]);

            \Log::info('Validated data:', ['data' => $validatedData]);

            // Check if the homeowner is already linked to the project
            $project = Project::find($validatedData['projectID']);
            if ($project && $project->ownerID) {
                $homeowner = Homeowner::find($project->ownerID);
                if ($homeowner && $homeowner->user->email === $validatedData['email']) {
                    \Log::warning('Homeowner already linked to project:', ['email' => $validatedData['email'], 'projectID' => $validatedData['projectID']]);
                    return response()->json(['success' => false, 'message' => 'Homeowner is already linked to the project.'], 400);
                }
            }

            // Store invitation in the database
            $invitation = Invitation::create([
                        'email' => $validatedData['email'],
                        'role' => 'Homeowner', // Hardcode role as Homeowner
                        'projectID' => $validatedData['projectID'],
            ]);

            \Log::info('Invitation created:', ['invitation' => $invitation]);

            // Generate the invitation URL
            $acceptUrl = url('/invitation/accept?email=' . urlencode($validatedData['email']) . '&projectID=' . $validatedData['projectID']);

            \Log::info('Invitation URL generated:', ['url' => $acceptUrl]);

            // Send the invitation email
            Mail::to($validatedData['email'])->send(new InviteMail($acceptUrl));

            return response()->json(['success' => true, 'message' => 'Invitation sent successfully.'], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to send invitation:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to send invitation.', 'error' => $e->getMessage()], 500);
        }
    }

    public function accept(Request $request) {
        $email = $request->query('email');
        $projectID = $request->query('projectID');

        \Log::info('Accepting invitation for email and project:', ['email' => $email, 'projectID' => $projectID]);

        try {
            // Find the invitation by email and project ID
            $invitation = Invitation::where('email', $email)->where('projectID', $projectID)->first();

            if (!$invitation) {
                \Log::error('Invitation not found for email and project:', ['email' => $email, 'projectID' => $projectID]);
                return redirect()->route('errorPage')->with('error', 'Invalid or expired invitation link.');
            }

            \Log::info('Invitation found:', ['invitation' => $invitation]);

            // Check if the user already exists
            $user = User::where('email', $email)->first();

            if ($user) {
                \Log::info('User found:', ['user' => $user]);
                // If the user exists, log them in
                Auth::login($user);

                // Verify the user is authenticated
                if (Auth::check()) {
                    \Log::info('User authenticated successfully.');

                    // Find the homeowner record associated with the user
                    $homeowner = Homeowner::where('userID', $user->userID)->first();

                    if (!$homeowner) {
                        \Log::error('Homeowner record not found for user:', ['userID' => $user->userID]);
                        return redirect()->route('errorPage')->with('error', 'Homeowner record not found.');
                    }

                    // Update the project with the homeowner's ID
                    $project = Project::find($projectID);
                    if ($project) {
                        $project->ownerID = $homeowner->ownerID; // Use homeowner's ownerID
                        $project->save();
                    }

                    // Delete the invitation
                    $invitation->delete();

                    return redirect()->route('project.dashboard', ['projectID' => $projectID])
                                    ->with('success', 'Invitation accepted!');
                } else {
                    \Log::error('User authentication failed.');
                    return redirect()->route('errorPage')->with('error', 'Failed to authenticate user.');
                }
            } else {
                \Log::info('User not found, redirecting to registration page.');
                // If the user does not exist, redirect to the ownerInviteRegister page with the email and project ID
                return redirect()->route('ownerInviteRegister', ['email' => $email, 'projectID' => $projectID])
                                ->with('info', 'Please register to accept the invitation.');
            }
        } catch (\Exception $e) {
            \Log::error('Error accepting invitation:', ['error' => $e->getMessage()]);
            return redirect()->route('errorPage')->with('error', 'An error occurred while processing your request.');
        }
    }

    public function ownerInviteRegister(Request $request) {
        $email = $request->query('email');
        $projectID = $request->query('projectID');

        // Validate the invitation
        $invitation = Invitation::where('email', $email)->where('projectID', $projectID)->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'Invalid or expired invitation link.');
        }

        // Pre-fill the email field if the invitation exists
        return view('ownerInviteRegister', [
            'email' => $email,
            'projectID' => $projectID,
        ]);
    }

    public function invitedRegisterSubmit(Request $request) {
        // Validate the form data
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'required',
                'regex:/^(01[0-9]-?\d{7,8}|0[3-9]-?\d{6,8})$/',
                'unique:users,userPhone',
            ],
            'password' => 'required|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
            'projectID' => 'required|exists:projects,projectID', // Ensure the project ID is valid
        ]);

        \Log::info('Form data validated successfully:', ['data' => $validatedData]);

        // Retrieve the invitation
        $invitation = Invitation::where('email', $validatedData['email'])->where('projectID', $validatedData['projectID'])->firstOrFail();

        // Generate a unique user ID
        $currentYear = now()->format('y');
        $latestUser = User::where('userID', 'like', "U{$currentYear}%")
                ->orderBy('userID', 'desc')
                ->first();
        $newUserNumber = $latestUser ? str_pad(((int) substr($latestUser->userID, 3)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $userID = "U{$currentYear}{$newUserNumber}";

        // Create the new user
        $user = User::create([
                    'userID' => $userID,
                    'userName' => $validatedData['full_name'],
                    'email' => $validatedData['email'],
                    'userPhone' => $validatedData['phone'],
                    'password' => Hash::make($validatedData['password']),
                    'role' => 'Homeowner', // Set the role to 'Homeowner'
        ]);

        // Generate a unique owner ID
        $surname = strtoupper(substr($validatedData['full_name'], 0, 2)); // Get first 2 letters of the full name
        $latestOwner = Homeowner::where('ownerID', 'like', "{$surname}{$currentYear}%")
                ->orderBy('ownerID', 'desc')
                ->first();
        $newNumber = $latestOwner ? str_pad(((int) substr($latestOwner->ownerID, 4)) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $ownerID = "{$surname}{$currentYear}{$newNumber}";

        // Create a new homeowner record
        $homeowner = Homeowner::create([
                    'ownerID' => $ownerID,
                    'userID' => $userID,
                    'homeAddress' => null, // Set homeAddress as null initially
        ]);

        // Update the project with the homeowner's ID
        $project = Project::find($validatedData['projectID']);
        if ($project) {
            $project->ownerID = $homeowner->ownerID;
            $project->save();
        }

        // Delete the invitation
        $invitation->delete();

        // Log in the user
        Auth::login($user);

        // Redirect to the project dashboard
        return redirect()->route('project.dashboard', ['projectID' => $validatedData['projectID']])
                        ->with('success', 'Registration successful!');
    }
}
