<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receipt - {{ $project->projectName }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/projectCost.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
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
                                Upload Receipt
                            </h3>
                        </div>
                        <hr style="margin-bottom: 20px;">
                        <div class="document-container">
                            <!-- Invoice Selection Sidebar -->
                            <div class="payment-sidebar">
                                <h3>Select Payment</h3>
                                <div class="payment-record-list">
                                    @if (count($payments) > 0)
                                    @foreach ($payments as $payment)
                                    <button class="payment-record-item" data-payment-id="{{ $payment->paymentID }}" data-payment-status="{{ $payment->paymentStatus }}">
                                        @if($payment->invoiceNo)
                                        Invoice #{{ $payment->invoiceNo }}
                                        @else
                                        Quotation #{{ $payment->quotationNo }}
                                        @endif
                                    </button>
                                    @endforeach
                                    @else
                                    <div class="no-payment-message" style="text-align: center; padding: 20px; color: #777; font-size: 16px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; margin: 10px;">
                                        <p>No payment records found.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div id="no-payment-selected" class="no-payment-message">
                                <img src="{{ asset('images/select.png') }}" alt="Select Payment" style="width: 130px; height: 130px; margin-bottom: 10px;">
                                <p>Please select a payment record from the left-hand side.</p>
                            </div>

                            <!-- Payment Schedule Section -->
                            <div class="payment-schedule" style="display: none;">
                                <h2>Payment schedule details</h2>
                                <button class="close-btn">&times;</button>

                                <!-- Default message when no payment is selected -->

                                <input type="hidden" id="user-role" value="{{ $role }}">

                                <!-- Payment details (hidden by default) -->
                                <div id="payment-details" class="schedule-details" style="display: none;">
                                    <div class="detail-item">
                                        <span class="icon">👤</span>
                                        <span class="label">Contractor</span>
                                        <button class="badge badge-contractor">Unknown</button>
                                    </div>
                                    <div class="detail-item">
                                        <span class="icon">⏰</span>
                                        <span class="label">Due date</span>
                                        <button class="badge badge-dueDate">N/A</button>
                                    </div>
                                    <div class="detail-item">
                                        <span class="icon">📋</span>
                                        <span class="label">Projects name</span>
                                        <button class="badge badge-project">Unknown</button>
                                    </div>
                                    <div class="detail-item">
                                        <span class="icon">❗</span>
                                        <span class="label">Payment Status</span>
                                        <button class="badge badge-importance">N/A</button>
                                    </div>
                                    <div class="detail-item">
                                        <span class="icon">💰</span>
                                        <span class="label">Payment Amount</span>
                                        <button class="badge badge-paymentAmount">N/A</button>
                                    </div>
                                    <div class="detail-item">
                                        <span class="icon">📄</span>
                                        <span class="label">Document</span>
                                        <a id="document-link" class="badge badge-document" href="#">View Document</a>
                                    </div>
                                </div>

                                <!-- Attachments Section -->
                                <div class="attachments">
                                    <h3>Attached receipt</h3>
                                    <div class="attachment-item" style="display: flex; align-items: center; gap: 10px; border: 1px solid #ccc; padding: 8px; border-radius: 5px;">
                                        <span class="icon">📂</span>
                                        <span id="file-name" style="flex-grow: 1; color: #555; cursor: pointer;">No attached files</span>
                                        <input type="file" id="receipt-file" accept=".pdf,.png" style="display: none;">
                                        <button class="action-btn" id="attach-file-btn">Attach file</button>
                                    </div>
                                    <!-- Verification Message -->
                                    <div id="verification-message" style="margin-top: 10px; font-weight: bold;"></div>
                                </div>

                                <!-- Remark Section -->
                                <div class="remarks">
                                    <h3>Remark</h3>
                                    <textarea id="remark-text" placeholder="Enter your remark here..."></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons" style="justify-content: space-between;">
                                    <button class="delete-btn">Exit</button>
                                    <button class="save-btn">Save</button>
                                </div>
                                <div id="errorMessageContainer" class="error-message-container"></div>
                            </div>
                        </div>
                    </div>

                    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center;">
                        <div class="spinner"></div>
                    </div>
                    <!-- Document Viewer Panel (Hidden by Default) -->
                    <div id="document-viewer-panel" class="document-viewer-panel" style="display: none;">
                        <div class="document-viewer-content">
                            <button id="close-document-viewer" class="close-btn">&times;</button>
                            <iframe id="document-viewer" src="" style="width: 90%; height: 500px; border: 1px solid #ccc;"></iframe>
                        </div>
                    </div>
                    <!-- Receipt Viewer Panel (Hidden by Default) -->
                    <div id="receipt-viewer-panel" class="receipt-viewer-panel" style="display: none;">
                        <div class="receipt-viewer-content">
                            <button id="close-receipt-viewer" class="close-btn">&times;</button>
                            <iframe id="receipt-viewer" src="" style="width: 100%; height: 80%; border: 1px solid #ccc;"></iframe>
                            <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-top: 10px;">
                                @if($role === 'contractor')
                                <button id="confirm-receipt" class="confirm-btn">Confirm Receipt</button>
                                <button id="reject-receipt" class="reject-btn">Reject Receipt</button>
                                @endif
                            </div>
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

            document.addEventListener("DOMContentLoaded", function () {
                const paymentButtons = document.querySelectorAll(".payment-record-item");
                const documentLink = document.getElementById("document-link");
                const noPaymentMessage = document.getElementById("no-payment-selected");
                const paymentDetails = document.getElementById("payment-details");
                const paymentSchedule = document.querySelector(".payment-schedule");
                const receiptFileInput = document.getElementById("receipt-file");
                const attachFileBtn = document.getElementById("attach-file-btn");
                const closeBtn = document.querySelector(".close-btn");
                const deleteBtn = document.querySelector(".delete-btn");
                const saveBtn = document.querySelector(".save-btn");
                const remarkText = document.getElementById("remark-text");
                const fileNameSpan = document.getElementById("file-name");
                const verificationMessage = document.getElementById("verification-message");

                // Document Viewer Panel Elements
                const documentViewerPanel = document.getElementById("document-viewer-panel");
                const closeDocumentViewerBtn = document.getElementById("close-document-viewer");
                const documentViewerIframe = document.getElementById("document-viewer");

                let selectedPaymentID = null;
                let uploadedFile = null;
                let isPaymentPaid = false;

                const role = document.getElementById("user-role").value;
                console.log("User Role:", role);
                if (role !== "homeowner") {
                    console.log("Hiding attachment button for role:", role); // Debugging: Confirm the logic is executed
                    attachFileBtn.style.display = "none";
                } else {
                    console.log("Showing attachment button for role:", role); // Debugging: Confirm the logic is executed
                }

                // Ensure file input is clickable and works correctly
                attachFileBtn.addEventListener("click", function () {
                    if (!isPaymentPaid) {
                        receiptFileInput.click(); // Trigger file selection when button is clicked
                    }
                });

                paymentButtons.forEach(button => {
                    button.addEventListener("click", function () {
                        // Remove active class from all payment records
                        paymentButtons.forEach(btn => btn.classList.remove("active"));

                        // Add active class to the selected payment record
                        this.classList.add("active");

                        // Show the payment-schedule div
                        paymentSchedule.style.display = "block";

                        // Hide the "no payment selected" message
                        noPaymentMessage.style.display = "none";

                        // Show the payment details
                        paymentDetails.style.display = "block";

                        // Set the selectedPaymentID
                        selectedPaymentID = this.getAttribute("data-payment-id");
                        const paymentStatus = this.getAttribute("data-payment-status");

                        console.log("Selected Payment ID:", selectedPaymentID); // Debugging

                        fetch(`/get-payment-details/${selectedPaymentID}`, {
                            method: "GET",
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update payment details
                                        document.querySelector(".badge-contractor").innerText = data.contractorName ?? "Unknown";
                                        document.querySelector(".badge-dueDate").innerText = data.dueDate ?? "N/A";
                                        document.querySelector(".badge-project").innerText = data.projectName ?? "Unknown";
                                        document.querySelector(".badge-importance").innerText = data.importanceLevel ?? "N/A";
                                        document.querySelector(".badge-paymentAmount").innerText = `RM${parseFloat(data.paymentAmount).toFixed(2)}`;


                                        // Handle receipt and remarks
                                        if (data.receipt) {
                                            fileNameSpan.textContent = data.receipt.split('/').pop();
                                        } else {
                                            fileNameSpan.textContent = "No attached files";
                                        }
                                        remarkText.value = data.remarks ?? "";
                                        // Ensure the attachment button is hidden for non-homeowners
                                        if (role !== "homeowner") {
                                            console.log("Hiding attachment button for role:", role); // Debugging
                                            attachFileBtn.style.display = "none";
                                        } else {
                                            console.log("Showing attachment button for role:", role); // Debugging
                                        }

                                        // Add event listener to the "View Document" hyperlink
                                        documentLink.addEventListener("click", function (e) {
                                            e.preventDefault(); // Prevent default link behavior

                                            // Fetch the document based on invoiceNo or quotationNo
                                            const invoiceNo = data.invoiceNo;
                                            const quotationNo = data.quotationNo;

                                            fetch(`/get-document?invoiceNo=${invoiceNo}&quotationNo=${quotationNo}`, {
                                                method: "GET",
                                                headers: {
                                                    "X-Requested-With": "XMLHttpRequest"
                                                }
                                            })
                                                    .then(response => response.json())
                                                    .then(documentData => {
                                                        if (documentData.success) {
                                                            // Display the PDF in the document viewer panel
                                                            documentViewerIframe.src = documentData.fileURL;
                                                            documentViewerPanel.style.display = "flex"; // Show the panel
                                                        } else {
                                                            alert("Document not found.");
                                                        }
                                                    })
                                                    .catch(error => console.error("Error fetching document:", error));
                                        });
                                    } else {
                                        alert("Failed to retrieve payment details.");
                                    }
                                })
                                .catch(error => console.error("Error fetching payment details:", error));
                    });
                });

                // Close the document viewer panel
                closeDocumentViewerBtn.addEventListener("click", function () {
                    documentViewerPanel.style.display = "none"; // Hide the panel
                    documentViewerIframe.src = ""; // Clear the iframe
                });

                // Handle file upload
                receiptFileInput.addEventListener("change", function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.type === "application/pdf" || file.type === "image/png") {
                            if (file.size <= 5 * 1024 * 1024) { // 5MB limit
                                fileNameSpan.textContent = file.name;
                                uploadedFile = file;
                                alert("File ready to be uploaded. Click Save to confirm.");
                            } else {
                                alert("File size exceeds 5MB.");
                                receiptFileInput.value = ""; // Clear the input
                            }
                        } else {
                            alert("Only PDF and PNG files are allowed.");
                            receiptFileInput.value = ""; // Clear the input
                        }
                    }
                });

                // Handle close button click
                closeBtn.addEventListener("click", function () {
                    paymentSchedule.style.display = "none";
                    selectedPaymentID = null;
                    uploadedFile = null;
                    remarkText.value = "";
                    verificationMessage.textContent = ""; // Clear verification message
                });

                // Handle delete button click
                deleteBtn.addEventListener("click", function () {
                    paymentSchedule.style.display = "none";
                    selectedPaymentID = null;
                    uploadedFile = null;
                    remarkText.value = "";
                    verificationMessage.textContent = ""; // Clear verification message
                });

                saveBtn.addEventListener("click", function () {
                    if (!selectedPaymentID) {
                        displayErrorMessage("No payment selected.");
                        return;
                    }

                    if (!uploadedFile) {
                        displayErrorMessage("No file uploaded.");
                        return;
                    }

                    // Show the loading overlay
                    loadingOverlay.style.display = "flex";

                    const formData = new FormData();
                    formData.append("receipt", uploadedFile);
                    formData.append("paymentID", selectedPaymentID); // Ensure paymentID is appended
                    formData.append("remarks", remarkText.value);

                    console.log("Uploading receipt for Payment ID:", selectedPaymentID); // Debugging

                    fetch("/upload-receipt", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (data.verification === "correct") {
                                        verificationMessage.textContent = "✅ Correct receipt amount.";
                                    } else if (data.verification === "wrong") {
                                        verificationMessage.textContent = "❌ Wrong receipt amount.";
                                    } else if (data.verification === "not_found") {
                                        verificationMessage.textContent = "⚠️ Amount not found in receipt.";
                                    }
                                    alert("Receipt uploaded successfully!");
                                    // Hide loading animation
                                    loadingOverlay.style.display = "none";

                                    // Refresh the page
                                    window.location.reload();

                                    // Refresh payment details after upload
                                    fetch(`/get-payment-details/${selectedPaymentID}`, {
                                        method: "GET",
                                        headers: {
                                            "X-Requested-With": "XMLHttpRequest"
                                        }
                                    })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // Update payment details
                                                    document.querySelector(".badge-importance").innerText = data.importanceLevel ?? "N/A";
                                                    fileNameSpan.textContent = data.receipt ? data.receipt.split('/').pop() : "No attached files";
                                                    remarkText.value = data.remarks ?? "";

                                                    // Show/hide action buttons based on payment status
                                                    if (data.importanceLevel === "pending") {
                                                        confirmReceiptBtn.style.display = "inline-block";
                                                        rejectReceiptBtn.style.display = "inline-block";
                                                    } else {
                                                        confirmReceiptBtn.style.display = "none";
                                                        rejectReceiptBtn.style.display = "none";
                                                    }
                                                }
                                            })
                                            .catch(error => console.error("Error fetching payment details:", error));
                                } else {
                                    displayErrorMessage("Failed to upload receipt: " + data.message);
                                    loadingOverlay.style.display = "none";
                                }
                            })
                            .catch(error => {
                                console.error("Error uploading receipt:", error);
                                displayErrorMessage("An error occurred while uploading the receipt.");
                                loadingOverlay.style.display = "none";
                            })
                            .finally(() => {
                                // Hide the loading overlay when the request is complete
                                loadingOverlay.style.display = "none";
                            });
                });

                function displayErrorMessage(message) {
                    const errorMessageContainer = document.getElementById("errorMessageContainer");
                    errorMessageContainer.textContent = message;
                    errorMessageContainer.style.display = "block";
                }

                // Update file name display
                receiptFileInput.addEventListener("change", function () {
                    if (this.files.length > 0) {
                        fileNameSpan.textContent = this.files[0].name; // Display selected file name
                    } else {
                        fileNameSpan.textContent = "No attached files"; // Reset if no file selected
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                const paymentButtons = document.querySelectorAll(".payment-record-item");
                const receiptViewerPanel = document.getElementById("receipt-viewer-panel");
                const closeReceiptViewerBtn = document.getElementById("close-receipt-viewer");
                const receiptViewerIframe = document.getElementById("receipt-viewer");
                const confirmReceiptBtn = document.getElementById("confirm-receipt");
                const rejectReceiptBtn = document.getElementById("reject-receipt");
                const fileNameSpan = document.getElementById("file-name");
                const attachFileBtn = document.getElementById("attach-file-btn");

                // Get the user's role from the hidden input
                const role = document.getElementById("user-role").value;
                console.log("User Role:", role); // Debugging: Confirm the role value

                let selectedPaymentID = null;
                let paymentStatus = null;

                // Hide the attachment button for non-homeowners initially
                if (role !== "homeowner") {
                    console.log("Hiding attachment button for role:", role); // Debugging
                    attachFileBtn.style.display = "none";
                }

                // Handle click on payment record items
                paymentButtons.forEach(button => {
                    button.addEventListener("click", function () {
                        selectedPaymentID = this.getAttribute("data-payment-id");
                        paymentStatus = this.getAttribute("data-payment-status");

                        // Fetch payment details
                        fetch(`/get-payment-details/${selectedPaymentID}`, {
                            method: "GET",
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.receipt) {
                                        // Update the file name in the attachment section
                                        fileNameSpan.textContent = data.receipt.split('/').pop();
                                        fileNameSpan.style.cursor = "pointer";

                                        // Ensure the attachment button is shown only to homeowners
                                        if (role !== "homeowner") {
                                            console.log("Hiding attachment button for role:", role); // Debugging
                                            attachFileBtn.style.display = "none";
                                        } else {
                                            console.log("Showing attachment button for role:", role); // Debugging
                                            // Hide the button if the payment is paid or a receipt exists
                                            if (paymentStatus === "paid" || data.receipt) {
                                                attachFileBtn.style.display = "none";
                                            } else {
                                                attachFileBtn.style.display = "inline-block";
                                            }
                                        }

                                        // Show/hide action buttons based on payment status
                                        if (paymentStatus === "pending") {
                                            confirmReceiptBtn.style.display = "inline-block";
                                            rejectReceiptBtn.style.display = "inline-block";
                                        } else {
                                            confirmReceiptBtn.style.display = "none";
                                            rejectReceiptBtn.style.display = "none";
                                        }
                                    } else {
                                        fileNameSpan.textContent = "No attached files";
                                        fileNameSpan.style.cursor = "default";

                                        // Ensure the attachment button is shown only to homeowners
                                        if (role !== "homeowner") {
                                            attachFileBtn.style.display = "none";
                                        } else {
                                            attachFileBtn.style.display = "inline-block";
                                        }
                                    }
                                })
                                .catch(error => console.error("Error fetching payment details:", error));
                    });
                });

                // Handle click on file name in attachment section
                fileNameSpan.addEventListener("click", function () {
                    if (fileNameSpan.textContent !== "No attached files") {
                        fetch(`/get-payment-details/${selectedPaymentID}`, {
                            method: "GET",
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.receipt) {
                                        // Open the receipt in the viewer panel
                                        receiptViewerIframe.src = data.receipt;
                                        receiptViewerPanel.style.display = "flex";
                                    }
                                })
                                .catch(error => console.error("Error fetching payment details:", error));
                    }
                });

                // Close the receipt viewer panel
                closeReceiptViewerBtn.addEventListener("click", function () {
                    receiptViewerPanel.style.display = "none";
                    receiptViewerIframe.src = ""; // Clear the iframe
                });

                // Handle confirm receipt button click
                confirmReceiptBtn.addEventListener("click", function () {
                    if (!selectedPaymentID) {
                        alert("No payment selected.");
                        return;
                    }

                    fetch(`/confirm-receipt/${selectedPaymentID}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Receipt confirmed successfully.");
                                    window.location.reload();
                                } else {
                                    alert("Failed to confirm receipt.");
                                }
                            })
                            .catch(error => console.error("Error confirming receipt:", error));
                });

                // Handle reject receipt button click
                rejectReceiptBtn.addEventListener("click", function () {
                    if (!selectedPaymentID) {
                        alert("No payment selected.");
                        return;
                    }

                    fetch(`/reject-receipt/${selectedPaymentID}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Receipt rejected successfully.");
                                    window.location.reload();
                                } else {
                                    alert("Failed to reject receipt.");
                                }
                            })
                            .catch(error => console.error("Error rejecting receipt:", error));
                });
            }
            );

            document.addEventListener("DOMContentLoaded", function () {
                const paymentButtons = document.querySelectorAll(".payment-record-item");
                const noPaymentMessage = document.getElementById("no-payment-selected");
                const paymentDetails = document.getElementById("payment-details");
                const paymentSchedule = document.querySelector(".payment-schedule");

                // Show "no-payment-selected" message by default
                noPaymentMessage.style.display = "block";
                paymentDetails.style.display = "none";
                paymentSchedule.style.display = "none";

                // If there are no payment records, ensure the "no-payment-selected" message is shown
                if (paymentButtons.length === 0) {
                    noPaymentMessage.style.display = "block";
                    paymentDetails.style.display = "none";
                    paymentSchedule.style.display = "none";
                }

                // Handle click on payment record items
                paymentButtons.forEach(button => {
                    button.addEventListener("click", function () {
                        // Hide the "no payment selected" message
                        noPaymentMessage.style.display = "none";

                        // Show the payment details
                        paymentDetails.style.display = "block";

                        // Show the payment schedule section
                        paymentSchedule.style.display = "block";
                    });
                });

                // Handle close button click
                const closeBtn = document.querySelector(".close-btn");
                closeBtn.addEventListener("click", function () {
                    // Hide the payment schedule section
                    paymentSchedule.style.display = "none";

                    // Show the "no payment selected" message
                    noPaymentMessage.style.display = "block";

                    // Clear any selected payment
                    paymentButtons.forEach(button => button.classList.remove("active"));
                });
            });

            // Add the new function to disable buttons if project is completed
            function disableButtonsIfProjectCompleted() {
                const projectStatus = "{{ $project->projectStatus }}"; // Get the project status from the backend
                if (projectStatus.toLowerCase() === 'completed') {
                    // Disable attachment button
                    const attachFileBtn = document.getElementById('attach-file-btn');
                    if (attachFileBtn) {
                        attachFileBtn.disabled = true;
                        attachFileBtn.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                        attachFileBtn.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                    }

                    // Disable confirm button
                    const confirmReceiptBtn = document.getElementById('confirm-receipt');
                    if (confirmReceiptBtn) {
                        confirmReceiptBtn.disabled = true;
                        confirmReceiptBtn.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                        confirmReceiptBtn.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                    }

                    // Disable reject button
                    const rejectReceiptBtn = document.getElementById('reject-receipt');
                    if (rejectReceiptBtn) {
                        rejectReceiptBtn.disabled = true;
                        rejectReceiptBtn.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                        rejectReceiptBtn.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                    }

                    // Disable save button
                    const saveBtn = document.querySelector('.save-btn');
                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.style.opacity = '0.5'; // Optional: Change the appearance to indicate it's disabled
                        saveBtn.style.cursor = 'not-allowed'; // Optional: Change the cursor to indicate it's disabled
                    }
                }
            }

            // Call the function when the DOM is fully loaded
            document.addEventListener('DOMContentLoaded', disableButtonsIfProjectCompleted);

            document.addEventListener("DOMContentLoaded", function () {
                const saveBtn = document.querySelector(".save-btn");
                const loadingOverlay = document.getElementById("loadingOverlay");

                if (saveBtn) {
                    saveBtn.addEventListener("click", function () {
                        // Show the loading overlay
                        loadingOverlay.style.display = "flex";

                        // Simulate a delay for the upload process (you can remove this in production)
                        setTimeout(() => {
                            // Hide the loading overlay (this will be handled by the page reload)
                            loadingOverlay.style.display = "none";
                        }, 5000); // Adjust the timeout as needed
                    });
                }
            });
        </script>
    </body>
</html>