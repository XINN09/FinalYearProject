<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\InvitationController;

Route::get('/', function () {
    return view('welcome');
});

// Login & Register Routes (Allow token as an optional parameter)
Route::get('/login', [UserManagementController::class, 'showLoginPage'])->name('login');
Route::post('/login', [UserManagementController::class, 'login']);

Route::get('/register', [UserManagementController::class, 'showRegisterPage'])->name('register');
Route::post('/register', [UserManagementController::class, 'register']);

// Show the invited register page
Route::get('/register/invite/{token?}', [UserManagementController::class, 'showInvitedRegisterPage'])->name('InvitedRegister');
Route::post('/register/invite', [UserManagementController::class, 'invitedRegister'])->name('invitedRegister.submit');

Route::get('/role', [UserManagementController::class, 'showRolePage'])->name('role');
Route::post('/role', [UserManagementController::class, 'handleRoleSelection'])->name('role');

Route::get('/register/contractor', [UserManagementController::class, 'registerContractor'])->name('registerContractor');
Route::post('/register/contractor', [UserManagementController::class, 'handleRegisterContractor']);

Route::get('/register/homeowner', [UserManagementController::class, 'registerHomeowner'])->name('registerOwner');
Route::post('/register/homeowner', [UserManagementController::class, 'handleRegisterHomeowner']);

Route::get('/register/worker', [UserManagementController::class, 'registerWorker'])->name('registerWorker');
Route::post('/register/worker', [UserManagementController::class, 'handleRegisterWorker']);

Route::get('/forgot-password', [UserManagementController::class, 'showForgotPasswordPage'])->name('password.request');
Route::post('/forgot-password', [UserManagementController::class, 'sendPasswordResetLink'])->name('password.email');

Route::get('/reset-password', [UserManagementController::class, 'showResetPasswordPage'])->name('password.reset');
Route::post('/reset-password', [UserManagementController::class, 'handlePasswordReset'])->name('password.update');

// Add route middleware to ensure only authenticated users can access the profile page
Route::middleware(['auth'])->get('/userProfile', [UserManagementController::class, 'profile'])->name('userProfile');
Route::post('/update-gender', [UserManagementController::class, 'updateGender'])->name('updateGender');
Route::post('/update-phone', [UserManagementController::class, 'updatePhone'])->name('updatePhone');
Route::post('/upload-company-logo', [UserManagementController::class, 'uploadCompanyLogo'])->name('uploadCompanyLogo');
Route::post('/update-password', [UserManagementController::class, 'updatePassword'])->name('updatePassword');
Route::post('/update-role-details', [UserManagementController::class, 'updateRoleDetails'])->name('updateRoleDetails');
Route::post('/validate-current-password', [UserManagementController::class, 'validateCurrentPassword'])->name('validateCurrentPassword');

Route::get('/projectHistory', [UserManagementController::class, 'projectHistory'])->name('projectHistory');
Route::post('/toggleWarranty', [DashboardController::class, 'toggleWarranty'])->name('toggleWarranty');
Route::get('/getTaskDetails/{taskId}', [DashboardController::class, 'getTaskDetails'])->name('getTaskDetails');
Route::post('/updateWarranty/{taskId}', [DashboardController::class, 'updateWarranty'])->name('updateWarranty');
Route::post('/removeWarranty', [DashboardController::class, 'removeWarranty'])->name('removeWarranty');
Route::get('/viewWarrantyDetails/{taskId}', [DashboardController::class, 'viewWarrantyDetails'])->name('viewWarrantyDetails');

Route::get('/warranty', [UserManagementController::class, 'getWarrantyRecords'])->name('getWarrantyRecords');
Route::post('/warranty/request', [UserManagementController::class, 'storeWarrantyRequest'])->name('storeWarrantyRequest');
Route::get('/warrantyService', [UserManagementController::class, 'warrantyService'])->name('warrantyService');
Route::post('/warranty/deny/{id}', [UserManagementController::class, 'denyRequest'])->name('denyRequest');
Route::post('/warranty/accept/{id}', [UserManagementController::class, 'acceptRequest'])->name('acceptRequest');

// Home route
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [DashboardController::class, 'home'])->name('home');
});

Route::post('/projects/create', [DashboardController::class, 'createProject'])->name('createProject');

Route::get('/projects', [DashboardController::class, 'getProjects'])->name('getProjects');

Route::post('/projects/update/{projectID}', [DashboardController::class, 'updateProject']);

Route::get('/get-completed-projects', [DashboardController::class, 'getCompletedProjects'])->name('getCompletedProjects');

Route::get('/project/{projectID}/owner', [DashboardController::class, 'getProjectOwner']);

Route::post('/send-invite', [InvitationController::class, 'sendInvitation'])->name('sendOwnerInvite');
Route::get('/invitation/accept', [InvitationController::class, 'accept'])->name('accept.invitation');
Route::get('/owner-invite-register', [InvitationController::class, 'ownerInviteRegister'])->name('ownerInviteRegister');
Route::post('/invited-register-submit', [InvitationController::class, 'invitedRegisterSubmit'])->name('OwnerInvitedRegister.submit');

Route::get('/project/{projectID}/dashboard', [DashboardController::class, 'projectDashboard'])
        ->name('project.dashboard')
        ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});

Route::post('/tasks/create', [DashboardController::class, 'createTask'])->name('createTask');
Route::put('/update-task', [DashboardController::class, 'updateTask'])->name('updateTask');
Route::post('/tasks/updateField', [DashboardController::class, 'update'])->name('updateField');

Route::post('/closeProject/{projectID}', [DashboardController::class, 'closeProject'])->name('closeProject');
Route::post('/assign-owner', [DashboardController::class, 'assignOwner'])->name('assignOwner');

Route::get('/work', [DashboardController::class, 'work'])->name('work');

Route::get('/contractor/team', [DashboardController::class, 'team'])->name('team');
Route::get('/invitation/accept/{token}', [DashboardController::class, 'accept'])->name('acceptWorkerInvitation');
Route::post('/contractor/invite-worker', [DashboardController::class, 'inviteWorker'])->name('inviteWorker');
Route::get('/getWorkerInfo/{workerID}', [DashboardController::class, 'getWorkerInfo']);
Route::post('/removeWorker', [DashboardController::class, 'removeWorker'])->name('removeWorker');
Route::post('/resend-invitation', [DashboardController::class, 'resendInvitation'])->name('resendInvitation');

Route::get('/issues', [DashboardController::class, 'issues'])->name('issues');
Route::post('/issues/{issueID}/update', [DashboardController::class, 'updateIssues'])->name('issuesUpdate');
Route::post('/save-report', [DashboardController::class, 'saveReport']);
Route::post('/download-report', [DashboardController::class, 'downloadReport'])->name('downloadReport');
Route::get('/serviceReport', [DashboardController::class, 'serviceReport'])->name('serviceReport');
Route::post('/generate/service/report', [DashboardController::class, 'generateServiceReport'])->name('generateReport');
Route::delete('/delete-issues', [DashboardController::class, 'deleteIssues'])->name('issues.delete');

Route::post('/document/upload', [DashboardController::class, 'uploadDocument'])->name('uploadDocument');
Route::get('/project/{projectID}/documents', [DashboardController::class, 'documentByProject'])->name('document.project');
Route::get('/getDocumentContent/{documentID}', [DashboardController::class, 'getDocumentContent'])->name('getDocumentContent');
Route::get('/downloadDocument/{documentID}', [DashboardController::class, 'downloadDocument'])->name('downloadDocument');
Route::post('/deleteDocuments', [DashboardController::class, 'deleteDocuments'])->name('deleteDocuments');

// Report route
Route::get('/report/{projectID}', [DashboardController::class, 'report'])->name('report');
Route::get('/report2/{projectID}', [DashboardController::class, 'report2'])->name('report2');
Route::post('/updateCompanyLogo', [DashboardController::class, 'updateCompanyLogo'])->name('updateCompanyLogo');
Route::post('/generate-invoice/{projectID}', [DashboardController::class, 'generateInvoice']);
Route::post('/generate-quotation/{projectID}', [DashboardController::class, 'generateQuotation']);
Route::post('/saveInvoice/{projectID}', [DashboardController::class, 'saveInvoice'])->name('saveInvoice');
Route::post('/saveQuotation/{projectID}', [DashboardController::class, 'saveQuotation'])->name('saveQuotation');
Route::get('/getPreviousPaymentAmount/{projectID}', [DashboardController::class, 'getPreviousPaymentAmount']);

Route::get('/gantt/{projectID}', [DashboardController::class, 'gantt'])->name('gantt');
Route::get('/gantt/tasks/{projectID}', [DashboardController::class, 'getTasks']);
Route::put('/update-task-gantt', [DashboardController::class, 'updateTaskInGantt']);
Route::delete('/delete-task/{taskId}', [DashboardController::class, 'destroy']);

Route::get('/project/{projectID}/cost', [DashboardController::class, 'getProjectCost'])->name('projectCost');
Route::get('/project/{projectID}/cost/details', [DashboardController::class, 'getCostDetails'])->name('costDetails');
Route::post('/task/{taskID}/update', [DashboardController::class, 'updateCost']);
Route::get('/project/{projectID}/calendar', [DashboardController::class, 'calendar'])->name('calendar');
Route::get('/project/{projectID}/receipt', [DashboardController::class, 'receipt'])->name('receipt');
Route::get('/get-payment-details/{paymentID}', [DashboardController::class, 'getPaymentDetails']);
Route::post('/upload-receipt', [DashboardController::class, 'uploadReceipt']);
Route::get('/project/{projectID}/labour/cost', [DashboardController::class, 'getLabourCost'])->name('labourCost');
Route::post('/update-worker-rate/{workerID}', [DashboardController::class, 'updateWorkerRate'])->name('updateWorkerRate');
Route::get('/get-document', [DashboardController::class, 'getDocument'])->name('get.document');
Route::get('/view-receipt/{paymentID}', [DashboardController::class, 'viewReceipt'])->name('view.receipt');
Route::post('/confirm-receipt/{paymentID}', [DashboardController::class, 'confirmReceipt'])->name('confirm.receipt');
Route::post('/reject-receipt/{paymentID}', [DashboardController::class, 'rejectReceipt'])->name('reject.receipt');

Route::get('/logout', [UserManagementController::class, 'showLogout'])->name('logout');
Route::post('/logout', [UserManagementController::class, 'logout'])->name('logout');

Route::get('/error', function () {
    return view('error');
})->name('errorPage');
