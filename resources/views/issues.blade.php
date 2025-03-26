<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Issues Page</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{ asset('css/issues.css') }}">
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

                            <h3 style="cursor: default;">All Issues</h3>
                        </div>
                        <div class="control-buttons">
                            <div class="generate-report-btn-container">
                                @if($contractor)
                                <button class="generate-report-btn" id="generate-report-btn" disabled>Generate Report</button>
                                @endif
                                <button class="delete-issues-btn" id="delete-issues-btn" disabled>Delete Issues</button>
                            </div>
                        </div>
                    </div>
                    @php
                    $contractor = \App\Models\Contractor::where('userID', auth()->user()->userID)->exists();
                    @endphp
                    <input type="hidden" id="user-role" value="{{ auth()->user()->role }}">

                    <div class="table-container">
                        <!-- Issues Table -->
                        <table class="issues-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all" onclick="toggleCheckboxes()"></th>
                                    <th>ID</th>
                                    <th>Issue Name</th>
                                    <th>Project</th>
                                    <th>Reporter</th>
                                    <th>Created Time</th>
                                    <th>Handle by</th>
                                    <th>Status</th>
                                    <th>Severity</th>
                                    <th>Cost</th>
                                    <th>Due Date</th>
                                    <th>Report</th> <!-- New column for Report -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($issues as $issue)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="issue-checkbox" data-service-no="{{ $issue->serviceNo }}"
                                               @if($issue->serviceNo) disabled @endif>
                                    </td>
                                    <td>{{ $issue->issuesID }}</td>
                                    <td>{{ $issue->issuesName }}</td>
                                    <td>{{ $issue->warrantyRequest->warranty->task->project->projectName ?? 'N/A' }}</td>
                                    <td>
                                        <div class="reporter">
                                            <img src="{{ asset('icon/userProfile.png') }}" alt="User"/>
                                            <span>{{ $issue->warrantyRequest->requesterName }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $issue->created_at->format('m-d-Y') }}</td>
                                    <td>
                                        <div class="reporter">
                                            <img src="{{ asset('icon/userProfile.png') }}" alt="User"/>
                                            <span>{{ $issue->issueHandler ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Status Dropdown -->
                                        <select class="status-dropdown" data-issue-id="{{ $issue->issuesID }}" 
                                                onchange="updateIssueField('{{ $issue->issuesID }}', 'issuesStatus', this.value)"
                                                @if($issue->serviceNo || !$contractor) disabled @endif>
                                            <option value="open" {{ $issue->issuesStatus === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="working" {{ $issue->issuesStatus === 'working' ? 'selected' : '' }}>Working</option>
                                            <option value="completed" {{ $issue->issuesStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </td>
                                    <td>
                                        <!-- Severity Dropdown -->
                                        <select class="severity-dropdown" data-issue-id="{{ $issue->issuesID }}" 
                                                onchange="updateIssueField('{{ $issue->issuesID }}', 'severity', this.value)"
                                                @if($issue->serviceNo || !$contractor) disabled @endif>
                                            <option value="low" {{ $issue->severity === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ $issue->severity === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ $issue->severity === 'high' ? 'selected' : '' }}>High</option>
                                        </select>
                                    </td>
                                    <td>
                                        <!-- Budget Input -->
                                        <input type="number" class="budget-input" data-issue-id="{{ $issue->issuesID }}" 
                                               value="{{ $issue->budget ?? 0.00 }}" 
                                               onchange="updateIssueField('{{ $issue->issuesID }}', 'budget', this.value)"
                                               @if($issue->serviceNo || !$contractor) disabled @endif>
                                    </td>
                                    <td>
                                        <!-- Due Date Input -->
                                        <input type="date" class="due-date-input" data-issue-id="{{ $issue->issuesID }}" 
                                               value="{{ $issue->dueDate ? $issue->dueDate->format('Y-m-d') : '' }}" 
                                               onchange="updateIssueField('{{ $issue->issuesID }}', 'dueDate', this.value)"
                                               @if($issue->serviceNo || !$contractor) disabled @endif>
                                    </td>
                                    <td>
                                        <!-- Report Column -->
                                        @if($issue->serviceNo)
                                        @php
                                        $serviceReport = \App\Models\ServiceReport::where('serviceNo', $issue->serviceNo)->first();
                                        @endphp
                                        @if($serviceReport)
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                                            <img src="{{ asset('images/file2.png') }}" alt="User" id="view-report-img"/>
                                            <a href="#" class="view-report" data-file-path="{{ Storage::url($serviceReport->reportContent) }}">
                                                {{ basename($serviceReport->reportContent) }}
                                            </a>
                                        </div>
                                        @else
                                        N/A
                                        @endif
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" style="text-align: center;">No issues found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Add this panel for displaying the report -->
                    <div id="report-view-overlay"></div>
                    <div id="report-view-panel">
                        <div id="report-view-header">
                            <span>Service Report</span>
                            <button id="close-report-view">Close</button>
                        </div>
                        <iframe id="report-view-iframe" src="" frameborder="0"></iframe>
                    </div>



                    <!-- Report Panel -->
                    <div id="report-overlay"></div>
                    <div id="report-panel">
                        <!-- Report Content -->
                        <div id="report-content">
                            <!-- Dynamically populated report content will go here -->
                        </div>

                        <!-- Buttons on the Right Side -->
                        <div id="report-buttons">
                            <button id="save-report">Save</button>
                            <button id="download-report">Download</button>
                            <button id="cancel-report">Cancel</button>
                        </div>
                    </div>
                </div>


                <div class="footer">
                    <span>Total Count: 1</span>
                </div>
            </div>
        </div>
    </body>

    <script>
        function toggleCheckboxes() {
        const isChecked = document.getElementById('select-all').checked;
        const checkboxes = document.querySelectorAll('.issue-checkbox');
        const userRole = document.getElementById('user-role').value;
        checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const statusDropdown = row.querySelector('.status-dropdown');
        const serviceNo = checkbox.getAttribute('data-service-no');
        if (userRole === 'contractor') {
        // Only contractors can tick/untick checkboxes for completed rows without service reports
        if (statusDropdown.value === 'completed' && !serviceNo) {
        checkbox.checked = isChecked;
        checkbox.disabled = false;
        } else {
        checkbox.checked = false;
        }
        } else if (userRole === 'homeowner') {
        // Homeowners cannot interact with checkboxes at all
        checkbox.checked = false;
        checkbox.disabled = true;
        }
        });
        toggleGenerateButton();
        }

        document.addEventListener('DOMContentLoaded', () => {
        // Add event listeners to checkboxes
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', toggleGenerateButton);
        });
        // Add event listeners to status dropdowns
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', toggleGenerateButton);
        });
        // Disable the button initially
        document.getElementById('generate-report-btn').disabled = true;
        });
        async function updateIssueField(issueID, field, value) {
        try {
        const response = await fetch(`/issues/${issueID}/update`, {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ field, value }),
        });
        const result = await response.json();
        if (!response.ok) {
        console.error(`Failed to update issue: ${result.error}`);
        alert('Failed to update issue. Please try again.');
        } else {
        console.log('Issue updated successfully:', result);
        }
        } catch (error) {
        console.error('Error updating issue:', error);
        alert('An error occurred while updating the issue.');
        }
        }
        function toggleGenerateButton() {
        const checkboxes = document.querySelectorAll('.issue-checkbox');
        const generateReportBtn = document.getElementById('generate-report-btn');
        // Check if any issue is selected
        const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        // Check if all selected issues are completed and don't have a service report
        let allCompleted = true;
        let hasExistingReport = false;
        checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
        const row = checkbox.closest('tr');
        const statusDropdown = row.querySelector('.status-dropdown');
        const serviceNo = checkbox.getAttribute('data-service-no');
        // Check if the issue is completed
        if (statusDropdown && statusDropdown.value !== 'completed') {
        allCompleted = false;
        }

        // Check if the issue already has a service report
        if (serviceNo) {
        hasExistingReport = true;
        }
        }
        });
        // Enable the button only if:
        // 1. At least one issue is selected
        // 2. All selected issues are completed
        // 3. No selected issue has an existing service report
        generateReportBtn.disabled = !(isAnyChecked && allCompleted && !hasExistingReport);
        // Show a message if any selected issue has an existing service report
        if (hasExistingReport) {
        alert('One or more selected issues already have a service report.');
        }
        }

        // Add event listeners to checkboxes to enable/disable button
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        const serviceNo = checkbox.getAttribute('data-service-no');
        if (serviceNo) {
        checkbox.disabled = true; // Disable if service report already exists
        checkbox.checked = false; // Uncheck if somehow checked
        }
        });
        // Add event listeners to status dropdowns to recheck button state when status changes
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', toggleGenerateButton);
        });
        document.getElementById('generate-report-btn').addEventListener('click', () => {
        const selectedIssues = [];
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
        const issueId = checkbox.closest('tr').querySelector('td:nth-child(2)').innerText;
        selectedIssues.push(issueId);
        }
        });
        if (selectedIssues.length === 0) {
        alert('Please select at least one issue.');
        return;
        }

        // Check if any selected issue already has a service report
        const hasExistingReport = Array.from(document.querySelectorAll('.issue-checkbox:checked')).some(checkbox => {
        const row = checkbox.closest('tr');
        const serviceNo = row.querySelector('.view-report')?.getAttribute('data-file-path');
        return !!serviceNo; // If serviceNo exists, return true
        });
        if (hasExistingReport) {
        alert('One or more selected issues already have a service report.');
        return;
        }

        // Send selected issue IDs to the backend
        fetch('/generate/service/report', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ selectedIssues }),
        })
                .then(response => {
                if (!response.ok) {
                return response.json().then(errorData => {
                throw new Error(errorData.message || 'Failed to generate report.');
                });
                }
                return response.json();
                })
                .then(data => {
                if (data.success) {
                // Inject the HTML content into the report panel
                document.getElementById('report-content').innerHTML = data.html;
                // Show the report panel and overlay
                document.getElementById('report-panel').style.display = 'flex'; // Use flexbox
                document.getElementById('report-overlay').style.display = 'block';
                document.body.style.overflow = 'hidden'; // Disable scrolling on the main page
                } else {
                alert('Failed to generate report: ' + data.message);
                }
                })
                .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while generating the report.');
                });
        });
        document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('report-panel').style.display = 'none';
        document.getElementById('report-overlay').style.display = 'none';
        })
                document.getElementById('cancel-report').addEventListener('click', () => {
        document.getElementById('report-panel').style.display = 'none';
        document.getElementById('report-overlay').style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable scrolling on the main page
        });
        // Function to populate the report panel with data
        function populateReportPanel(reportData) {
        // Populate header
        document.getElementById('business-id').innerText = reportData.businessId;
        document.getElementById('business-address').innerText = reportData.businessAddress;
        document.getElementById('business-tel').innerText = reportData.businessTel;
        document.getElementById('business-email').innerText = reportData.businessEmail;
        // Populate client info
        document.getElementById('client-name').innerText = reportData.clientName;
        document.getElementById('project-address').innerText = reportData.projectAddress;
        document.getElementById('attn-name').innerText = reportData.attnName;
        document.getElementById('attn-tel').innerText = reportData.attnTel;
        // Populate service info
        document.getElementById('service-no').innerText = `SERVICE NO: ${reportData.serviceNo}`;
        document.getElementById('warranty-expiry').innerText = reportData.warrantyExpiry;
        document.getElementById('report-date').innerText = reportData.reportDate;
        document.getElementById('person-in-charge').innerText = reportData.personInCharge;
        document.getElementById('person-tel').innerText = reportData.personTel;
        // Populate issue info
        document.getElementById('issue-name').innerText = reportData.issueName;
        // Populate quotation table
        const quotationBody = document.getElementById('quotation-body');
        quotationBody.innerHTML = reportData.quotationRows.map((row, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${row.description}</td>
                <td>${row.quantity}</td>
                <td>${row.unitPrice}</td>
                <td>${row.total}</td>
            </tr>
        `).join('');
        // Populate total amount
        document.getElementById('total-amount').innerText = reportData.totalAmount;
        }

        document.getElementById('cancel-report').addEventListener('click', () => {
        document.getElementById('report-panel').style.display = 'none';
        document.getElementById('report-overlay').style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable scrolling on the main page
        });
        // Save Button Click Handler
        document.getElementById('save-report').addEventListener('click', async () => {
        try {
        // Get the report content
        const reportContent = document.getElementById('report-content').innerHTML;
        // Get selected issue IDs
        const selectedIssues = [];
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
        const issueId = checkbox.closest('tr').querySelector('td:nth-child(2)').innerText;
        selectedIssues.push(issueId);
        }
        });
        // Get additional data from the reportData object
        const reportData = {
        personInCharge: document.getElementById('person-in-charge').innerText,
                personTel: document.getElementById('person-tel').innerText,
                totalAmount: parseFloat(document.getElementById('total-amount').innerText),
                selectedIssues: selectedIssues,
        };
        // Send the data to the backend
        const response = await fetch('/save-report', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                content: reportContent,
                        reportData: reportData,
                }),
        });
        if (response.ok) {
        const data = await response.json();
        alert('Report saved successfully!'); // Show success message
        window.location.reload(); // Refresh the page after the user clicks "OK"
        } else {
        const errorData = await response.json();
        alert('Failed to save report: ' + (errorData.message || 'Unknown error'));
        }
        } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the report: ' + error.message);
        }
        });
        document.getElementById('download-report').addEventListener('click', async () => {
        try {
        // Get the report content
        const reportContent = document.getElementById('report-content').innerHTML;
        // Send the content to the backend to generate the PDF
        const response = await fetch('/download-report', {
        method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ content: reportContent }),
        });
        if (response.ok) {
        // If the response is OK, download the PDF
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'service-report.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        } else {
        // If the response is not OK, show an error message
        const errorData = await response.json();
        alert('Failed to download report: ' + (errorData.message || 'Unknown error'));
        }
        } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while downloading the report.');
        }
        });
        // Handle "View Report" link clicks
        document.querySelectorAll('.view-report').forEach(link => {
        link.addEventListener('click', function (e) {
        e.preventDefault();
        const filePath = this.getAttribute('data-file-path');
        // Set the iframe source to the PDF file
        document.getElementById('report-view-iframe').src = filePath;
        // Show the panel and overlay
        document.getElementById('report-view-overlay').style.display = 'block';
        document.getElementById('report-view-panel').style.display = 'block';
        });
        });
// Handle close button click
        document.getElementById('close-report-view').addEventListener('click', () => {
        // Hide the panel and overlay
        document.getElementById('report-view-overlay').style.display = 'none';
        document.getElementById('report-view-panel').style.display = 'none';
        // Clear the iframe source
        document.getElementById('report-view-iframe').src = '';
        });
        let sortOrder = 'desc'; // Default sort order


        function toggleSort() {
        sortOrder = sortOrder === 'desc' ? 'asc' : 'desc'; // Toggle between ascending and descending

        fetch(`/issues?sort=${sortOrder}`, {
        headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        })
                .then(response => response.text())
                .then(html => {
                // Replace only the table content
                const parser = new DOMParser();
                const newDocument = parser.parseFromString(html, 'text/html');
                const newTable = newDocument.querySelector('.issues-table');
                const currentTable = document.querySelector('.issues-table');
                if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
                }

                // Reattach event listeners after updating the table
                attachEventListeners();
                })
                .catch(error => console.error('Error:', error));
        }

// Enable or disable the delete button based on checkbox selection and service report status
        function toggleDeleteButton() {
        const checkboxes = document.querySelectorAll('.issue-checkbox');
        const deleteButton = document.getElementById('delete-issues-btn');
        const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        const hasExistingReport = Array.from(checkboxes).some(checkbox => {
        if (checkbox.checked) {
        const serviceNo = checkbox.getAttribute('data-service-no');
        return !!serviceNo; // If serviceNo exists, return true
        }
        return false;
        });
        deleteButton.disabled = !(isAnyChecked && !hasExistingReport);
        }

// Add event listeners to checkboxes to enable/disable delete button
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', toggleDeleteButton);
        });
// Handle delete button click
        document.getElementById('delete-issues-btn').addEventListener('click', async () => {
        const selectedIssues = [];
        document.querySelectorAll('.issue-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
        const issueId = checkbox.closest('tr').querySelector('td:nth-child(2)').innerText;
        selectedIssues.push(issueId);
        }
        });
        if (selectedIssues.length === 0) {
        alert('Please select at least one issue.');
        return;
        }

        try {
        const response = await fetch('/delete-issues', {
        method: 'DELETE',
                headers: {
                'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ selectedIssues }),
        });
        const result = await response.json();
        if (response.ok) {
        alert(result.message);
        window.location.reload(); // Reload the page to reflect changes
        } else {
        alert(result.message || 'Failed to delete issues.');
        }
        } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting issues.');
        }
        });
    </script>
</html>
