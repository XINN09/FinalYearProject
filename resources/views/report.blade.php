<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Quotation - {{ $project->projectName }}</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/report2.css') }}">
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
                            <h3>Report</h3>
                        </div>
                        <hr>

                        @if (!$hasTasks)
                        <p class="no-tasks-message">No tasks available for this project to generate report.</p>
                        @else
                        <div class="report-generation">
                            <!-- Report Type Selection -->
                            <div class="filter-section">
                                <div class="slider-container">
                                    <div class="slider"></div>
                                    <div class="btn">
                                        <button class="quotation" style="margin-left: -20px;">Quotation</button>
                                        <button class="invoice">Invoice</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="quotation-form active" id="quotationFields" style="display: block;">
                                    <!-- Business and Client Information -->
                                    <div class="two-column-layout">
                                        <div class="filter-section">
                                            <h4>Business Information</h4>
                                            <p style="margin-top: 10px; font-style: italic; color: #2274c2; font-size: 13px;">
                                                Note: Business information is not editable here. You can update it in your profile.
                                            </p>
                                            <label for="businessName">Business Name:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="businessName" name="businessName" value="{{ $businessInfo['businessName'] ?? '' }}" readonly required  title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="businessNo">Business Registration Number(ID):</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="businessNo" name="businessNo" value="{{ $businessInfo['businessNo'] ?? '' }}" readonly required title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="businessAddress">Business Address:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="businessAddress" name="businessAddress" value="{{ $businessInfo['businessAddress'] ?? '' }}" readonly required title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="contractorName" style="display: none;">Contractor Name:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="contractorName" name="contractorName" value="{{ $businessInfo['contractorName'] ?? '' }}" readonly required  style="display: none;"  title="Not-editable">
                                                <span class="lock-icon"  style="display: none;">🔒</span>
                                            </div>
                                            <label for="contractorEmail">Contractor Email:</label>
                                            <div class="input-with-lock">
                                                <input type="email" id="contractorEmail" name="contractorEmail" value="{{ $businessInfo['contractorEmail'] ?? '' }}" readonly required title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="contractorPhone">Contractor Phone Number:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="contractorPhone" name="contractorPhone" value="{{ $businessInfo['contractorPhone'] ?? '' }}" readonly required title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="businessLogo">Business Logo:</label>
                                            @if($businessInfo['companyLogo'])
                                            <!-- Display existing company logo if available -->
                                            <img src="{{ asset($businessInfo['companyLogo']) }}" alt="Business Logo" id="businessLogoPreview" style="max-width: 130px;">
                                            @else
                                            <!-- Option to upload a new logo or use default -->
                                            <div>
                                                <p class="logo-instruction" id="UploadPanel5">You can either upload your company logo or use the default Alloymont logo:</p>

                                                <!-- Radio buttons to choose whether to upload or use default -->
                                                <label id="UploadPanel3">
                                                    <input type="radio" name="logoOption" value="upload" onchange="toggleLogoField(true)" style="width: 10%;"  id="UploadPanel"> 
                                                    Upload your own logo
                                                </label>
                                                <label id="UploadPanel4">
                                                    <input type="radio" name="logoOption" value="default" onchange="toggleLogoField(false)" checked style="width: 10%;"  id="UploadPanel2">
                                                    Use Alloymont logo
                                                </label>
                                                <img id="businessLogoPreview" style="display:none; max-width: 130px;" />

                                                <!-- Upload your own logo option -->
                                                <div id="uploadLogoButtonDiv" style="display:none;">
                                                    <button id="uploadLogoButton" onclick="showUploadLogoPanel()">Upload</button>
                                                </div>

                                                <!-- Upload logo input field (hidden by default) -->
                                                <div id="uploadLogoField" style="display:none;">
                                                    <input type="file" id="businessLogo" name="businessLogo" accept=".jpg, .jpeg, .png" onchange="validateLogoFile(event)">
                                                    <p><small>Only JPG, JPEG, and PNG files are allowed. Maximum file size: 5MB.</small></p>
                                                </div>

                                                <!-- Display Alloymont logo -->
                                                <div id="defaultLogoField">
                                                    <img src="{{ asset('images/AlloymontLogo.png') }}" alt="Default Alloymont Logo" style="max-width: 130px; margin: 10px 0 0 20px;" id="defaultLogo">
                                                </div>
                                            </div>
                                            @endif

                                        </div>

                                        <div class="filter-section">
                                            <h4>Client Information</h4>
                                            <label for="clientName">Client Name:</label>
                                            <input type="text" id="clientName" name="clientName" value="{{ $clientInfo['clientName'] ?? '' }}" required placeholder="Please Fill in the client name">
                                            <span id="clientNameError" class="errorMessage"></span>

                                            <label for="clientAddress">Client Address:</label>
                                            <input type="text" id="clientAddress" name="clientAddress" value="{{ $clientInfo['clientAddress'] ?? '' }}" required placeholder="Please Fill in the client address">
                                            <span id="clientAddressError" class="errorMessage"></span>

                                            <label for="clientEmail">Client Email:</label>
                                            <input type="email" id="clientEmail" name="clientEmail" value="{{ $clientInfo['clientEmail'] ?? '' }}" required placeholder="Please Fill in the client email">
                                            <span id="clientEmailError" class="errorMessage"></span>

                                            <label for="clientPhone">Client Phone Number:</label>
                                            <input type="text" id="clientPhone" name="clientPhone" value="{{ $clientInfo['clientPhone'] ?? '' }}" required placeholder="Please Fill in the client phone number">
                                            <span id="clientPhoneError" class="errorMessage"></span>
                                        </div>
                                    </div>

                                    <!-- Project and Invoice/Quotation Information -->
                                    <div class="two-column-layout">
                                        <div class="filter-section">
                                            <h4>Project Information</h4>
                                            <p style="margin-top: 10px; font-style: italic; color: #2274c2; font-size: 13px;">
                                                Note: Project name and project address are not editable here. You can update them in the project settings (click the angle-down icon beside the project name in the main table).
                                            </p>

                                            <label for="projectName">Project Name:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="projectName" name="projectName" value="{{ $projectInfo['projectName'] ?? '' }}" readonly required  title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="projectAddress">Project Address:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="projectAddress" name="projectAddress" value="{{ $projectInfo['projectAddress'] ?? '' }}" readonly required  title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <input type="hidden" id="projectID" value="{{ $projectInfo['projectID'] }}"> 

                                            <label for="contactName">Contact Name:</label>
                                            <input type="text" id="contactName" name="contactName" value="{{ $projectInfo['contactName'] ?? '' }}" required placeholder="Please Fill in the contact person name">
                                            <span id="contactNameError" class="errorMessage"></span>

                                            <label for="contactPhone">Contact Phone Number:</label>
                                            <input type="text" id="contactPhone" name="contactPhone" value="{{ $projectInfo['contactPhone'] ?? '' }}" required placeholder="Please Fill in the contact person phone">
                                            <span id="contactPhoneError" class="errorMessage"></span>
                                        </div>



                                        <div class="filter-section">
                                            <h4>Quotation Details</h4>

                                            <label for="quotationNumber">Quote Number:</label>
                                            <div class="input-with-lock">
                                                <input type="text" id="quotationNumber" name="quotationNumber" readonly value="{{ $quotationInfo['quotationNumber'] }}"  title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="invoiceDate">Date:</label>
                                            <div class="input-with-lock">
                                                <input type="date" id="quotationDate" name="quotationDate" readonly value="{{ isset($quotationInfo['quotationDate']) ? $quotationInfo['quotationDate'] : '' }}" required  title="Not-editable">
                                                <span class="lock-icon">🔒</span>
                                            </div>
                                            <label for="paymentTerm">Payment Term (Days):</label>
                                            <input type="number" id="paymentTerm" name="paymentTerm" min="1" placeholder="Enter days (e.g., 30, 60)" value="{{ isset($quotationInfo['paymentTerm']) ? $quotationInfo['paymentTerm'] : '' }}" required>
                                            <span id="paymentTermError" class="errorMessage"></span>

                                            <label for="calculatedDueDate">Due Date:</label>
                                            <input type="text" id="calculatedDueDate" name="calculatedDueDate" readonly value="{{ isset($quotationInfo['dueDate']) ? $quotationInfo['dueDate'] : '' }}" id="dueDate" name="dueDate">
                                        </div>

                                    </div>

                                    <div class="filter-section">
                                        <h4>Listing Item Details</h4>

                                        <table class="task-table">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <label class="checkbox-container">
                                                            <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"> 
                                                            <span class="custom-checkbox"></span>
                                                        </label>
                                                    </th>
                                                    <th>Task Name</th>
                                                    <th>Quantity</th>
                                                    <th>UOM</th>
                                                    <th>Unit Price (RM)</th>
                                                    <th>Cost (RM)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tasks as $task)
                                                <tr>
                                                    <td>
                                                        <label class="checkbox-container">
                                                            <input type="checkbox" class="task-checkbox" value="{{ $task->taskID }}" 
                                                                   data-taskName="{{ $task->taskName}}" 
                                                                   data-qty="{{ $task->qty }}" 
                                                                   data-uom="{{ $task->uom }}" 
                                                                   data-unitPrice="{{ $task->unitPrice }}" 
                                                                   data-budget="{{ $task->budget }}"
                                                                   @if($task->isPaid) disabled @endif>
                                                            <span class="custom-checkbox"></span>
                                                        </label>
                                                    </td>
                                                    <td>{{ $task->taskName }}</td>
                                                    <td>{{ $task->qty }}</td>
                                                    <td>{{ $task->uom ?: 'NO' }}</td>
                                                    <td>{{ number_format($task->unitPrice, 2) }}</td>
                                                    <td>{{ number_format($task->budget, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <p style="margin-top: 10px; font-style: italic; color: #2274c2; font-size: 13px;">
                                            * Tasks that have already been included in a payment cannot be selected.
                                        </p>
                                    </div>

                                    <!-- Payment Details -->
                                    <div class="filter-section">
                                        <h4>Payment Details</h4>
                                        <label for="subtotal">Subtotal:</label>
                                        <div class="input-with-lock">
                                            <input type="number" id="subtotal" name="subtotal" placeholder="Subtotal amount" step="0.01" readonly title="Auto-calculated">
                                            <span class="lock-icon">🔒</span>
                                        </div>
                                        <label for="tax">Tax:</label>
                                        <input type="number" id="tax" name="tax" placeholder="Tax amount" step="0.01">

                                        <label for="amountDue">Total Amount:</label>
                                        <div class="input-with-lock">
                                            <input type="number" id="amountDue" name="amountDue" placeholder="Total amount" step="0.01" readonly title="Auto-calculated">
                                            <span class="lock-icon">🔒</span>
                                        </div>

                                        <!-- Previous Payment Amount Field -->
                                        <label for="previousPaymentAmount">Previous Payment Amount:</label>
                                        <div class="input-with-lock">
                                            <input type="number" id="previousPaymentAmount" name="previousPaymentAmount" value="{{ $previousPaymentAmount }}" placeholder="Previous payment amount" step="0.01" readonly title="Auto-calculated">
                                            <span class="lock-icon">🔒</span>
                                        </div>

                                        <label for="balance">Balance:</label>
                                        <div class="input-with-lock">
                                            <input type="number" id="balance" name="balance" placeholder="Balance amount" step="0.01" readonly title="Auto-calculated">
                                            <span class="lock-icon">🔒</span>
                                        </div>
                                        <label for="paymentOptions">Payment Options:</label>
                                        <select id="paymentOptions" name="paymentOptions" onchange="toggleDepositFields()">
                                            <option value="Full Payment">Full Payment</option>
                                            <option value="Deposit">Deposit</option>
                                        </select>

                                        <div id="depositFields" style="display: none;">
                                            <label for="depositPercentage">Deposit Percentage:</label>
                                            <select id="depositPercentage" name="depositPercentage" onchange="updateDepositAmount()">
                                                <option value="0" style="display: none;">0%</option>
                                                <option value="10">10%</option>
                                                <option value="20">20%</option>
                                                <option value="30">30%</option>
                                                <option value="40">40%</option>
                                                <option value="50">50%</option>
                                                <option value="60">60%</option>
                                                <option value="70">70%</option>
                                            </select>

                                            <label for="depositAmount">Deposit Amount:</label>
                                            <input type="number" id="depositAmount" name="depositAmount" placeholder="Deposit amount" step="0.01" readonly>
                                        </div>

                                        <label for="paymentInstruction">Payment Instruction:</label>
                                        <textarea id="paymentInstruction" name="paymentInstruction" placeholder="e.g., Balance payment to be made upon project completion"></textarea>
                                    </div>


                                    <!-- Notes/Remarks Area -->
                                    <div class="filter-section">
                                        <h4>Notes/Remarks</h4>
                                        <textarea id="remarks" name="remarks" placeholder="Enter any notes or remarks..."></textarea>
                                    </div>


                                    <!-- Customer Signature -->
                                    <div class="filter-section">
                                        <h4>Customer Signature</h4>
                                        <label class="radio-container">
                                            <input type="radio" name="customerSignature" checked disabled>
                                            <span class="custom-radio"></span>
                                            Signed
                                        </label>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <button id="previewReport" class="button">Preview Quotation</button>
                                        <button id="saveReport" class="button">Save Quotation</button>
                                        <button id="downloadReport" class="button">Download Quotation</button>
                                        <button id="resetReport" class="button">Reset Quotation</button>
                                    </div>
                                    <div id="errorMessageContainer" class="error-message-container" style="color: red; margin-top: 10px; text-align: center;"></div>

                                </div>

                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="uploadLogoPanel" class="modalLogo">
                    <div id="logoUploadPanelContent" class="modal-content-logo">
                        <h4>Upload Business Logo</h4>
                        <input type="file" id="logoFileInput" name="logoFile" accept=".jpg, .jpeg, .png" onchange="validateLogoFile(event)">
                        <p class="upload-instructions">Only JPG, JPEG, and PNG files are allowed. Maximum file size: 5MB.</p>
                        <!-- Logo Preview -->
                        <div id="logoPreviewContainer">
                            <img id="logoPreview" src="" alt="Logo Preview">
                        </div>
                        <!-- Buttons -->
                        <div class="button-group">
                            <button onclick="confirmLogoUpload()" id="confirmLogoBtn" class="btn btn-primary">Confirm Upload</button>
                            <button onclick="cancelLogoUpload()" class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>

                <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center;">
                    <div class="spinner"></div>
                </div>


                <div id="previewReportPanel" style="display:none;">
                    <div id="previewReportContent">
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button onclick="closePreviewPanel()" class="button">Close Preview</button>
                            <button onclick="downloadReport()" class="button">Download Report</button>
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
            const tasks = @json($tasks);
                    document.addEventListener("DOMContentLoaded", function () {
                        const invoiceBtn = document.querySelector(".invoice");
                        const quotationBtn = document.querySelector(".quotation");
                        const slider = document.querySelector(".slider");


                        function getProjectID() {
                            const projectElement = document.getElementById("projectID");
                            return projectElement ? projectElement.value : null;
                        }

                        quotationBtn.addEventListener("click", () => {
                            slider.style.transform = "translateX(0%)";
                            const projectID = getProjectID();

                            if (projectID) {
                                setTimeout(() => {
                                    window.location.href = `/report/${projectID}`;
                                }, 300);
                            } else {
                                console.error("Project ID not found!");
                            }
                        });

                        invoiceBtn.addEventListener("click", () => {
                            slider.style.transform = "translateX(100%)";
                            const projectID = getProjectID();

                            if (projectID) {
                                setTimeout(() => {
                                    window.location.href = `/report2/${projectID}`;
                                }, 300);
                            } else {
                                console.error("Project ID not found!");
                            }
                        });
                    });

            document.addEventListener("DOMContentLoaded", function () {
                const previousPaymentAmount = document.getElementById("previousPaymentAmount").value;
                console.log("Initial previousPaymentAmount:", previousPaymentAmount);
            });

            document.addEventListener("DOMContentLoaded", function () {
                const previousPaymentAmountElement = document.getElementById("previousPaymentAmount");
                const previousPaymentAmount = parseFloat(previousPaymentAmountElement.value) || 0;
                console.log("Previous Payment Amount from HTML:", previousPaymentAmount);

                // Update the total amount based on the previous payment amount
                updateTotal();

                // Fetch previous payment amount (if needed)
                fetchPreviousPaymentAmount();
            });

            function toggleFields(reportType) {
                const quotationFields = document.getElementById("quotationFields");

                if (quotationFields) {
                    if (reportType === "quotation") {
                        quotationFields.style.display = "block";
                    }
                } else {
                    console.error("One or more elements (invoiceFields, quotationFields, dueDateField) not found in DOM.");
                }
            }

            function toggleDepositFields() {
                const paymentOptions = document.getElementById("paymentOptions");
                const depositFields = document.getElementById("depositFields");

                if (paymentOptions.value === "Deposit") {
                    depositFields.style.display = "block";
                } else {
                    depositFields.style.display = "none";
                }
            }

            document.addEventListener("DOMContentLoaded", function () {
                toggleDepositFields(); // Initialize the fields based on the default payment option
            });


// Initialize default values and display settings
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("quotationDate").value = new Date().toISOString().split("T")[0];

                // Check if the elements exist before calling toggleFields
                if (document.getElementById("quotationFields") && document.getElementById("quotationFields")) {
                    toggleFields("quotation"); // Initialize with "invoice"
                } else {
                    console.error("Invoice/Quotation fields not found in DOM.");
                }

                document.querySelector(".quotation").addEventListener("click", function () {
                    toggleFields("quotation");
                });

                document.querySelector(".quotation").addEventListener("click", function () {
                    toggleFields("quotation");
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                const taskCheckboxes = document.querySelectorAll(".task-checkbox");

                // Disable checkboxes for tasks that are already paid
                taskCheckboxes.forEach(checkbox => {
                    const taskID = checkbox.value;
                    const task = tasks.find(t => t.taskID === taskID);

                    if (task && task.isPaid) {
                        checkbox.disabled = true;
                        checkbox.parentElement.style.opacity = 0.5; // Visual indication that the task is paid
                        checkbox.title = "This task has already been included in the payment."; // Tooltip message
                    }
                });

                const selectAll = document.getElementById("selectAll");

                // Add event listener to "Select All"
                selectAll.addEventListener("change", function () {
                    toggleAllCheckboxes(this);
                });

                // Add event listeners to individual task checkboxes
                taskCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener("change", function () {
                        // Update "Select All" checkbox state based on individual checkboxes
                        selectAll.checked = Array.from(taskCheckboxes).every(cb => cb.checked || cb.disabled);
                        updateSubtotal(); // Update subtotal when any checkbox is toggled
                    });
                });
            });


            // Function to toggle all checkboxes (skip disabled ones)
            function toggleAllCheckboxes(source) {
                const checkboxes = document.querySelectorAll(".task-checkbox");
                checkboxes.forEach(checkbox => {
                    // Only toggle checkboxes that are not disabled
                    if (!checkbox.disabled) {
                        checkbox.checked = source.checked;
                    }
                });
                updateSubtotal(); // Update the subtotal after toggling checkboxes
            }

            // Function to calculate subtotal
            function updateSubtotal() {
                let subtotal = 0;
                const selectAll = document.getElementById("selectAll");

                // Sum budgets of selected tasks
                document.querySelectorAll(".task-checkbox:checked").forEach(checkbox => {
                    let taskBudget = parseFloat(checkbox.getAttribute("data-budget")) || 0;
                    subtotal += taskBudget;
                });

                document.getElementById("subtotal").value = subtotal.toFixed(2);
                updateTotal(); // Update the total after updating subtotal
            }

// Function to calculate total
            function updateTotal() {
                let subtotal = parseFloat(document.getElementById("subtotal").value) || 0;
                let tax = parseFloat(document.getElementById("tax").value) || 0;
                let previousPaymentAmount = parseFloat(document.getElementById("previousPaymentAmount").value) || 0;

                if (tax < 0) {
                    alert("Tax must be a positive number or 0.");
                    document.getElementById("tax").value = 0;
                    tax = 0;
                }

                // Calculate total including tax
                let total = subtotal + (subtotal * tax / 100);
                document.getElementById("amountDue").value = total.toFixed(2);

                // Calculate balance
                let balance = total - previousPaymentAmount;
                document.getElementById("balance").value = balance.toFixed(2);
            }

            function fetchPreviousPaymentAmount() {
                const projectID = document.getElementById("projectID").value;

                fetch(`/getPreviousPaymentAmount/${projectID}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Previous payment data received:', data);

                            const previousPaymentAmountElement = document.getElementById("previousPaymentAmount");
                            const backendValue = parseFloat(previousPaymentAmountElement.value) || 0;

                            // Only update the field if the backend value is not 0
                            if (backendValue !== 0) {
                                if (data.previousPaymentAmount) {
                                    previousPaymentAmountElement.value = data.previousPaymentAmount;
                                    updateTotal(); // Update the total amount after setting the previous payment amount
                                }
                            }

                            if (data.hasDeposit) {
                                const depositOption = document.querySelector('option[value="Deposit"]');
                                if (depositOption) {
                                    depositOption.disabled = true;
                                    depositOption.textContent = depositOption.textContent.replace(" (Already Collected)", "");
                                    depositOption.textContent += " (Already Collected)";
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching previous payment amount:', error));
            }

            // Initialize the page
            updateSubtotal(); // Set subtotal when page loads
            updateTotal(); // Set total when page loads
            fetchPreviousPaymentAmount(); // Fetch and set previous payment amount

            // Add event listeners for input fields
            document.getElementById("tax").addEventListener("input", updateTotal);
            document.getElementById("previousPaymentAmount").addEventListener("input", updateTotal);


// Function to calculate deposit amount
            function updateDepositAmount() {
                let totalAmount = parseFloat(document.getElementById("amountDue").value) || 0;
                let depositPercentage = parseFloat(document.getElementById("depositPercentage").value) || 0;

                let depositAmount = (totalAmount * depositPercentage) / 100;
                document.getElementById("depositAmount").value = depositAmount.toFixed(2);
            }




            // Ensure the function is also triggered on page load
            document.addEventListener("DOMContentLoaded", function () {
                updateDepositAmount(); // Set deposit amount when page loads
                document.getElementById("depositPercentage").addEventListener("change", updateDepositAmount);
                document.getElementById("amountDue").addEventListener("input", updateDepositAmount);
            });


            function toggleLogoField(isUploadSelected) {
                const uploadField = document.getElementById('uploadLogoField');
                const defaultLogoField = document.getElementById('defaultLogoField');
                const uploadLogoButtonDiv = document.getElementById('uploadLogoButtonDiv');

                if (isUploadSelected) {
                    uploadLogoButtonDiv.style.display = 'block';
                    uploadField.style.display = 'none';
                    defaultLogoField.style.display = 'none';
                } else {
                    uploadLogoButtonDiv.style.display = 'none';
                    uploadField.style.display = 'none';
                    defaultLogoField.style.display = 'block';
                }
            }


            function validateLogoFile(event) {
                const fileInput = event.target;
                const file = fileInput.files[0];

                if (file) {
                    const fileType = file.type;
                    const fileSize = file.size;
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    const maxSize = 5 * 1024 * 1024; // 5MB

                    if (!allowedTypes.includes(fileType)) {
                        alert("Invalid file type! Only JPG, JPEG, and PNG files are allowed.");
                        fileInput.value = '';
                        return;
                    }

                    if (fileSize > maxSize) {
                        alert("File size is too large. Maximum allowed size is 5MB.");
                        fileInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const logoPreview = document.getElementById('logoPreview');
                        logoPreview.src = e.target.result;
                        logoPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }

            function confirmLogoUpload() {
                const fileInput = document.getElementById('logoFileInput');
                const file = fileInput.files[0];

                if (file) {
                    updateCompanyLogo(file);
                    document.getElementById('uploadLogoPanel').style.display = 'none';
                } else {
                    alert("Please select a logo file to upload.");
                }
            }

            function cancelLogoUpload() {
                document.getElementById('uploadLogoPanel').style.display = 'none';
                document.getElementById('logoFileInput').value = '';
                document.getElementById('logoPreview').style.display = 'none';
            }

            function updateCompanyLogo(file) {
                const formData = new FormData();
                formData.append('businessLogo', file);

                fetch('/updateCompanyLogo', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Check if the logo preview element exists
                                const logoPreview = document.getElementById('businessLogoPreview');
                                if (logoPreview) {
                                    logoPreview.src = data.logoUrl;  // Update the logo preview
                                    logoPreview.style.display = 'block';  // Show the preview
                                } else {
                                    console.error('Logo preview element not found.');
                                }
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error uploading logo:', error);
                        });
            }




            document.getElementById('paymentTerm').addEventListener('input', function () {
                let days = parseInt(this.value);
                if (!isNaN(days) && days > 0) {
                    let today = new Date();
                    today.setDate(today.getDate() + days); // Add days to today's date
                    let formattedDate = today.toISOString().split('T')[0]; // Format as YYYY-MM-DD
                    document.getElementById('calculatedDueDate').value = formattedDate;
                } else {
                    document.getElementById('calculatedDueDate').value = ''; // Clear if input is invalid
                }
            });

            document.addEventListener("DOMContentLoaded", function () {

                function validateDepositPercentage() {
                    const paymentOptions = document.getElementById("paymentOptions");
                    const depositPercentage = parseFloat(document.getElementById("depositPercentage").value) || 0;

                    if (paymentOptions.value === "Deposit" && depositPercentage < 10) {
                        alert("Error: Deposit percentage must be 10% or higher.");
                        return false;
                    }
                    return true;
                }
                // List of required fields
                const requiredFields = [
                    "clientName",
                    "clientAddress",
                    "clientEmail",
                    "clientPhone",
                    "contactName",
                    "contactPhone",
                    "quotationDate",
                    "paymentTerm"
                ];

                // Function to validate the form
                function validateForm() {
                    let isValid = true;

                    requiredFields.forEach((fieldId) => {
                        const field = document.getElementById(fieldId);
                        const errorSpan = document.getElementById(`${fieldId}Error`);

                        if (field && errorSpan) {
                            if (!field.value.trim()) {
                                errorSpan.innerText = "This field is required.";
                                errorSpan.style.color = "red"; // Make it noticeable
                                isValid = false;
                            } else {
                                errorSpan.innerText = ""; // Clear error if field is filled
                            }
                        }
                    });

                    // Email validation
                    const emailField = document.getElementById("clientEmail");
                    const emailError = document.getElementById("clientEmailError");
                    const balance = parseFloat(document.getElementById("balance").value) || 0;
                    if (balance < 0) {
                        alert("Error: The balance cannot be negative. Please adjust the payment amount.");
                        isValid = false;
                    }

                    return isValid;
                    if (emailField.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
                        emailError.innerText = "Invalid email format.";
                        emailError.style.color = "red";
                        isValid = false;
                    } else {
                        emailError.innerText = "";
                    }

                    if (!validateDepositPercentage()) {
                        isValid = false;
                    }

                    return isValid;
                }

                function validateTaskSelection() {
                    const taskCheckboxes = document.querySelectorAll(".task-checkbox:checked");
                    if (taskCheckboxes.length === 0) {
                        displayErrorMessage("Please select at least one task to proceed.");
                        return false;
                    }
                    return true;
                }

                function validateTaskSelection() {
                    const taskCheckboxes = document.querySelectorAll(".task-checkbox:checked");
                    if (taskCheckboxes.length === 0) {
                        displayErrorMessage("Please select at least one task to proceed.");
                        return false;
                    }
                    return true;
                }

                function displayErrorMessage(message) {
                    const errorMessageContainer = document.getElementById("errorMessageContainer");
                    errorMessageContainer.innerText = message;
                    errorMessageContainer.style.display = "block"; // Ensure the container is visible
                }

                function clearErrorMessage() {
                    const errorMessageContainer = document.getElementById("errorMessageContainer");
                    errorMessageContainer.innerText = "";
                    errorMessageContainer.style.display = "none"; // Hide the container when no errors
                }

// Attach event listeners to clear errors when user starts typing
                function attachEventListeners() {
                    requiredFields.forEach((fieldId) => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.addEventListener("input", function () {
                                document.getElementById(`${fieldId}Error`).innerText = "";
                                clearErrorMessage(); // Clear the error message when user starts typing
                            });
                        }
                    });
                }



// Handle Preview Button Click
                document.getElementById("previewReport").addEventListener("click", function (e) {
                    e.preventDefault(); // Prevent form submission

                    if (!validateTaskSelection()) {
                        return; // Stop further execution if no tasks are selected
                    }

                    if (validateForm()) {
                        // If validation passes, proceed with preview logic
                        const projectID = document.getElementById("projectID").value;
                        const reportData = collectReportData();
                        generateQuotation(projectID, reportData);
                    } else {
                        displayErrorMessage("Please correct the errors above before proceeding.");
                    }
                });

// Handle Save Button Click
                document.getElementById("saveReport").addEventListener("click", function (e) {
                    e.preventDefault();

                    if (!validateTaskSelection()) {
                        return; // Stop further execution if no tasks are selected
                    }

                    if (validateForm()) {
                        const projectID = document.getElementById("projectID").value;
                        const reportData = collectReportData();

                        console.log("Final reportData to be sent:", reportData);

                        saveQuotation(projectID, reportData);
                    } else {
                        displayErrorMessage("Please correct the errors above before proceeding.");
                    }
                });

// Handle Download Button Click
                document.getElementById("downloadReport").addEventListener("click", function (e) {
                    e.preventDefault();

                    if (!validateTaskSelection()) {
                        return; // Stop further execution if no tasks are selected
                    }

                    if (validateForm()) {
                        // If validation passes, proceed with download logic
                        const projectID = document.getElementById("projectID").value;
                        const reportData = collectReportData();
                        downloadReport(projectID, reportData);
                    } else {
                        displayErrorMessage("Please correct the errors above before proceeding.");
                    }
                });
                // Attach event listeners to clear errors when user starts typing
                attachEventListeners();
            });






            function collectReportData() {
                return {
                    reportType: "quotation",
                    businessInfo: {
                        businessName: document.getElementById("businessName")?.value || "",
                        businessNo: document.getElementById("businessNo")?.value || "",
                        businessAddress: document.getElementById("businessAddress")?.value || "",
                        contractorName: document.getElementById("contractorName")?.value || "",
                        contractorEmail: document.getElementById("contractorEmail")?.value || "",
                        contractorPhone: document.getElementById("contractorPhone")?.value || "",
                    },
                    clientInfo: {
                        clientName: document.getElementById("clientName")?.value || "",
                        clientAddress: document.getElementById("clientAddress")?.value || "",
                        clientEmail: document.getElementById("clientEmail")?.value || "",
                        clientPhone: document.getElementById("clientPhone")?.value || "",
                    },
                    projectInfo: {
                        projectName: document.getElementById("projectName")?.value || "N/A",
                        projectAddress: document.getElementById("projectAddress")?.value || "N/A",
                        contactName: document.getElementById("contactName")?.value || "",
                        contactPhone: document.getElementById("contactPhone")?.value || "",
                    },
                    quotationInfo: {
                        quotationNumber: document.getElementById("quotationNumber")?.value || "",
                        quotationDate: document.getElementById("quotationDate")?.value || "",
                        paymentTerm: document.getElementById("paymentTerm")?.value || "",
                        dueDate: document.getElementById("calculatedDueDate")?.value || "",
                    },
                    tasks: Array.from(document.querySelectorAll(".task-checkbox:checked")).map(checkbox => ({
                            taskID: checkbox.value || "",
                            taskName: checkbox.getAttribute("data-taskName") || "Unnamed Task",
                            qty: parseFloat(checkbox.getAttribute("data-qty")) || 0,
                            uom: checkbox.getAttribute("data-uom") || "",
                            unitPrice: parseFloat(checkbox.getAttribute("data-unitPrice")) || 0,
                            budget: parseFloat(checkbox.getAttribute("data-budget")) || 0,
                        })),
                    paymentDetails: {
                        subtotal: parseFloat(document.getElementById("subtotal")?.value) || 0,
                        tax: parseFloat(document.getElementById("tax")?.value) || 0,
                        amountDue: parseFloat(document.getElementById("amountDue")?.value) || 0,
                        previousPaymentAmount: 0, // Reset to 0 after using it
                        balance: parseFloat(document.getElementById("balance")?.value) || 0,
                        depositRate: parseFloat(document.getElementById("depositPercentage").value) || 0,
                        depositAmount: parseFloat(document.getElementById("depositAmount").value) || 0,
                        paymentOptions: document.getElementById("paymentOptions").value,
                        paymentInstruction: document.getElementById("paymentInstruction")?.value || "",
                        dueDate: document.getElementById("calculatedDueDate")?.value || "",
                        remarks: document.getElementById("remarks")?.value || "",
                    },
                    remarks: document.getElementById("remarks")?.value || "",
                    customerSignature: document.querySelector('input[name="customerSignature"]:checked')?.value || "N/A",
                };
            }


            // Function to generate quotation
            function generateQuotation(projectID, reportData) {
                fetch(`/generate-quotation/${projectID}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({projectID: projectID, reportData: reportData}),
                })
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            window.open(url, '_blank'); // Open PDF in a new window/tab
                        })
                        .catch(error => console.error('Error generating quotation:', error));
            }

            function saveQuotation(projectID, reportData) {
                showLoading();
                console.log("Attempting to save report...");
                console.log("Project ID:", projectID);
                console.log("Report Data Sent:", reportData);

                document.getElementById("subtotal").removeAttribute("readonly");

                fetch(`/saveQuotation/${projectID}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({projectID: projectID, reportData: reportData}),
                })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Server Response:", data);
                            if (data.success) {
                                // Display success message
                                alert(data.message);

                                // Redirect to the document page
                                window.location.href = data.redirectUrl;
                            } else {
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(error => {
                            console.error("Error saving data:", error);
                            alert("Error saving data: " + error);
                        });
            }

            // Function to download report
            function downloadReport(projectID, reportData) {
                fetch(`/generate-quotation/${projectID}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({projectID: projectID, reportData: reportData}),
                })
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'quotation.pdf';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        })
                        .catch(error => console.error('Error downloading report:', error));
            }


            document.getElementById("resetReport").addEventListener("click", function (e) {
                e.preventDefault();

                // Clear only the fillable fields
                document.getElementById("clientName").value = "";
                document.getElementById("clientAddress").value = "";
                document.getElementById("clientEmail").value = "";
                document.getElementById("clientPhone").value = "";
                document.getElementById("contactName").value = "";
                document.getElementById("contactPhone").value = "";
                document.getElementById("paymentTerm").value = "";
                document.getElementById("calculatedDueDate").value = "";
                document.getElementById("subtotal").value = "";
                document.getElementById("tax").value = "";
                document.getElementById("amountDue").value = "";
                document.getElementById("depositAmount").value = "";
                document.getElementById("remarks").value = "";

                // Uncheck all task checkboxes
                document.querySelectorAll(".task-checkbox").forEach(checkbox => checkbox.checked = false);
            });

            document.addEventListener("DOMContentLoaded", function () {
                const uploadLogoPanel = document.getElementById("uploadLogoPanel");
                // Ensure the modal is hidden on page load
                if (uploadLogoPanel) {
                    uploadLogoPanel.style.display = "none";
                }

                const uploadLogoButton = document.getElementById("uploadLogoButton");
                const confirmLogoBtn = document.getElementById("confirmLogoBtn");
                const cancelLogoBtn = document.querySelector(".btn-secondary");
                const logoFileInput = document.getElementById("logoFileInput");
                const logoPreview = document.getElementById("logoPreview");
                const logoPreviewContainer = document.getElementById("logoPreviewContainer");
                const businessLogoPreview = document.getElementById("businessLogoPreview");
                const defaultLogoField = document.getElementById("defaultLogoField");
                const UploadPanel = document.getElementById("UploadPanel");
                const UploadPanel2 = document.getElementById("UploadPanel2");
                const UploadPanel3 = document.getElementById("UploadPanel3");
                const UploadPanel4 = document.getElementById("UploadPanel4");
                const UploadPanel5 = document.getElementById("UploadPanel5");
                const uploadLogoField = document.getElementById("uploadLogoField");
                const uploadLogoButtonDiv = document.getElementById("uploadLogoButtonDiv");
                let selectedFile = null; // Store selected file

                // Ensure the modal is hidden on page load (force hide using inline style)
                if (uploadLogoPanel) {
                    uploadLogoPanel.style.display = "none";
                }

                // Ensure modal is hidden after page reload (fixes persistent modal issue)
                window.addEventListener("load", function () {
                    if (uploadLogoPanel) {
                        uploadLogoPanel.style.display = "none";
                    }
                });
                // Show the upload logo panel when "Upload" button is clicked
                uploadLogoButton.addEventListener("click", function () {
                    uploadLogoPanel.style.display = "flex"; // Show modal
                });
// Hide the panel when cancel is clicked
                cancelLogoBtn.addEventListener("click", function () {
                    uploadLogoPanel.style.display = "none";
                    logoFileInput.value = ""; // Clear file input
                    logoPreview.src = ""; // Clear preview
                    logoPreview.style.display = "none"; // Hide preview
                    logoPreviewContainer.style.display = "none"; // Hide preview container
                });
// Confirm upload and update the UI
                confirmLogoBtn.addEventListener("click", function () {
                    if (selectedFile) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            businessLogoPreview.src = e.target.result;
                            businessLogoPreview.style.display = "block";
                            defaultLogoField.style.display = "none"; // Hide default logo
                            UploadPanel.style.display = "none";
                            UploadPanel2.style.display = "none";
                            UploadPanel3.style.display = "none";
                            UploadPanel4.style.display = "none";
                            UploadPanel5.style.display = "none";
                            uploadLogoField.style.display = "none";
                            uploadLogoButtonDiv.style.display = "none";
                        };
                        reader.readAsDataURL(selectedFile);
                    }
                    uploadLogoPanel.style.display = "none"; // Close panel
                });
                // Show image preview when a file is selected
                logoFileInput.addEventListener("change", function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            logoPreview.src = e.target.result;
                            logoPreview.style.display = "block";
                            logoPreviewContainer.style.display = "flex";
                        };
                        reader.readAsDataURL(file);
                        selectedFile = file; // Store file for confirmation
                    }
                });
            });

            function disableFormElements() {
                // Get the project status from the DOM
                const projectStatus = "{{ $project->projectStatus }}"; // Assuming the project status is available in the Blade template

                // Check if the project status is "completed"
                if (projectStatus.toLowerCase() === "completed") {
                    // Disable all input fields, textareas, selects, and buttons except those in the sidebar and slider container
                    const inputs = document.querySelectorAll('input, textarea, select, button');
                    inputs.forEach(input => {
                        // Check if the element is inside the sidebar or slider container
                        const isSidebarElement = input.closest('.sidebar') !== null;
                        const isSliderContainerElement = input.closest('.slider-container') !== null;

                        // Disable the element only if it's not in the sidebar or slider container
                        if (!isSidebarElement && !isSliderContainerElement) {
                            input.disabled = true;

                            // Optionally, change the appearance of disabled elements
                            input.style.opacity = 0.6;
                            input.style.cursor = 'not-allowed';
                        }
                    });
                }
            }

            // Call the function when the page loads
            document.addEventListener('DOMContentLoaded', disableFormElements);

            // Function to show the loading animation
            function showLoading() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }

// Function to hide the loading animation
            function hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }

        </script>
    </body>
</html>