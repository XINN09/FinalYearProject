<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Warranty Records</title>
        <link rel="stylesheet" href="{{ asset('css/warranty.css') }}">
    </head>
    <body>
        <aside class="sidebar">
            @include('generalComponent.userNav')
        </aside>

        <div class="main-content">
            <h2 class="title">Warranty Records</h2>

            <!-- Filter and Search Section -->
            <div class="filter-section">
                <input type="text" id="searchInput" placeholder="Search by project name..." class="search-input" />
                <button class="filter-btn">Filter by Status</button>
                <select id="statusFilter" class="status-filter">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <div class="warranty-records">
                <div class="no-warranty-container" style="display: none;">
                    <img src="{{ asset('images/notFound.png') }}" alt="No Warranty Records" class="no-warranty-image" />
                    <p class="no-warranty-message">No warranty records found.</p>
                </div>

                @if($warranties->isEmpty())
                <!-- If no warranties exist, show the no-warranty-container -->
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        document.querySelector('.no-warranty-container').style.display = 'block';
                    });
                </script>
                @else
                @foreach ($warranties as $warranty)
                <div class="warranty-card" 
                     data-warranty-no="{{ $warranty->warrantyNo ?? 'N/A' }}"
                     data-homeowner="{{ $warranty->task?->project?->homeownerUser?->userName ?? 'N/A' }}"
                     data-contractor="{{ $warranty->task?->project?->contractorUser?->userName ?? 'N/A' }}"
                     data-contractor-email="{{ $warranty->task?->project?->contractorUser?->email ?? 'N/A' }}"
                     data-task-name="{{ $warranty->task?->taskName ?? 'N/A' }}"
                     data-warranty-start-date="{{ $warranty->startDate ?? 'N/A' }}">


                    <div class="warranty-header">
                        <h3 class="project-title">
                            {{ $warranty->task?->project?->projectName ?? 'No Project Assigned' }}
                        </h3>
                        <span class="status-label {{ $warranty->isExpired() ? 'expired' : 'active' }}">
                            {{ $warranty->isExpired() ? 'Expired' : 'Active' }}
                        </span>
                    </div>
                    <p class="project-description">
                        {{ $warranty->task?->project?->projectDesc ?? 'No Description Available' }}
                    </p>
                    <button class="expand-btn">View Warranty Details</button>
                    <input type="hidden" id="warrantyNo" name="warrantyNo" />
                    <div class="warranty-details" style="display: none;">
                        <div class="warranty-row">
                            <div class="waranty_remarks">
                                <span><strong>Warranty No:</strong> {{ $warranty->warrantyNo }}</span>
                            </div>
                            <div class="warranty_date">
                                <span><strong>Warranty Start Date:</strong> {{ $warranty->startDate }}</span>
                            </div>
                            <div class="warranty_date">
                                <span><strong>Warranty End Date:</strong> {{ $warranty->endDate }}</span>
                            </div>
                        </div>
                        <div class="warranty_remarks">
                            <span><strong>Warranty Remarks:</strong> {{ $warranty->description }}</span>
                        </div>

                        <p><strong>Covered Tasks:</strong></p>
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    @if ($isHomeowner && optional(auth()->user()->homeowner)->ownerID == $warranty->task?->project?->homeowner?->ownerID)
                                    <th></th>
                                    @endif
                                    <th>Task</th>
                                    <th>Duration</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if ($warranty->task)
                                <tr>
                                    @if ($isHomeowner && optional(auth()->user()->homeowner)->ownerID == $warranty->task?->project?->homeowner?->ownerID)
                                    <td>
                                        <input type="checkbox" class="task-checkbox" {{ $warranty->isExpired() ? 'disabled' : '' }}>
                                    </td>
                                    @endif
                                    <td>{{ $warranty->task->taskName }}</td>
                                    <td>{{ $warranty->task->duration }} {{ $warranty->task->durationUnit }}</td>
                                    <td>{{ $warranty->task->startDate }}</td>
                                    <td>{{ $warranty->task->endDate }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="5">No tasks linked to this warranty.</td>
                                </tr>
                                @endif
                            </tbody>

                        </table>
                    </div>
                    @if($isHomeowner && auth()->user()->homeowner->ownerID == $warranty->task?->project?->homeowner?->ownerID)
                    <button class="request-btn" {{ $warranty->isExpired() ? 'disabled' : '' }}>
                        Request Warranty Service
                    </button>
                    @endif

                    @if($warranty->isExpired())
                    <p class="contact-notice">Warranty has expired. Please contact the contractor for further assistance.</p>
                    @endif
                </div>
                @endforeach
                @endif
            </div>
        </div>

        <div id="warrantyRequestModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>Request Warranty Service</h3>

                <div class="section">
                    <h4 style="margin: 5px 10px;">Warranty Details</h4>
                    <table>
                        <tr>
                            <td>Task Name</td>
                            <td><span id="taskName"></span></td>
                        </tr>
                        <tr>
                            <td>Warranty Start Date</td>
                            <td><span id="warrantyStartDate"></span></td>
                        </tr>
                        <tr>
                            <td>Warranty Status</td>
                            <td><span id="warrantyStatus"></span></td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <h4 style="margin: 5px 10px;">Homeowner & Contractor Details</h4>
                    <table>
                        <tr>
                            <td>Homeowner Name</td>
                            <td><span id="homeownerName"></span></td>
                        </tr>
                        <tr>
                            <td>Contractor Name</td>
                            <td><span id="contractorName"></span></td>
                        </tr>
                        <tr>
                            <td>Contractor Email</td>
                            <td><span id="contractorEmail"></span></td>
                        </tr>
                    </table>
                </div>

                <div class="form-section">
                    <form id="warrantyRequestForm">
                        <input type="hidden" id="warrantyNo" name="warrantyNo" />

                        <label for="requestName">Request Title:</label>
                        <input type="text" id="requestTitle" name="requestTitle" required />

                        <label for="requestName">Requester Name:</label>
                        <input type="text" id="requesterName" name="requesterName" required />

                        <label for="requestDate">Request Date:</label>
                        <input type="date" id="requestDate" name="requestDate" required readonly />

                        <label for="requestDesc">Description:</label>
                        <textarea id="requestDesc" name="requestDesc"></textarea>

                        <button type="submit">Submit Request</button>
                    </form>

                    <p id="requestErrorMsg" class="error-message">Please fill in all required fields.</p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Expand/Collapse Warranty Details
                document.querySelectorAll(".expand-btn").forEach(btn => {
                    btn.addEventListener("click", function () {
                        let details = this.closest(".warranty-card").querySelector(".warranty-details");
                        details.style.display = details.style.display === "block" ? "none" : "block";
                    });
                });

                document.getElementById("searchInput").addEventListener("input", function () {
                    let searchValue = this.value.toLowerCase();
                    document.querySelectorAll(".warranty-card").forEach(card => {
                        let projectTitle = card.querySelector(".project-title").innerText.toLowerCase();
                        card.style.display = projectTitle.includes(searchValue) ? "block" : "none";
                    });
                    checkVisibility();
                });

                document.getElementById("statusFilter").addEventListener("change", function () {
                    let filterValue = this.value;
                    document.querySelectorAll(".warranty-card").forEach(card => {
                        let isExpired = card.querySelector(".status-label").classList.contains("expired");
                        if (filterValue === "all" || (filterValue === "active" && !isExpired) || (filterValue === "expired" && isExpired)) {
                            card.style.display = "block";
                        } else {
                            card.style.display = "none";
                        }
                    });
                    checkVisibility();
                });

                // Enable/Disable Request Warranty Button
                document.querySelectorAll(".task-checkbox").forEach(checkbox => {
                    checkbox.addEventListener("change", function () {
                        let card = this.closest(".warranty-card");
                        let requestBtn = card.querySelector(".request-btn");
                        let anyChecked = card.querySelectorAll(".task-checkbox:checked").length > 0;
                        requestBtn.disabled = !anyChecked;
                        requestBtn.style.backgroundColor = anyChecked ? "#28a745" : "#ccc";
                    });
                });
            });

            document.addEventListener("DOMContentLoaded", function () {
                let filterToggle = false; // Track toggle state

                document.querySelector(".filter-btn").addEventListener("click", function () {
                    filterToggle = !filterToggle; // Toggle state
                    let filterValue = filterToggle ? "expired" : "active";

                    document.querySelectorAll(".warranty-card").forEach(card => {
                        let isExpired = card.querySelector(".status-label").classList.contains("expired");
                        if ((filterValue === "active" && !isExpired) || (filterValue === "expired" && isExpired)) {
                            card.style.display = "block";
                        } else {
                            card.style.display = "none";
                        }
                    });

                    // Update button text based on filter state
                    this.innerText = filterToggle ? "Show Active" : "Show Expired";
                });
            });
            document.addEventListener("DOMContentLoaded", function () {
                const modal = document.getElementById("warrantyRequestModal");
                const closeBtn = document.querySelector(".close-btn");
                const requestForm = document.getElementById("warrantyRequestForm");
                const errorMsg = document.getElementById("requestErrorMsg");

                // Function to get text safely
                function getText(card, selector) {
                    let element = card.querySelector(selector);
                    return element ? element.innerText.trim() : "N/A";
                }

                // Function to get data attributes safely
                function getData(card, selector, attr) {
                    let element = card.querySelector(selector);
                    return element ? element.getAttribute(attr) : "";
                }

                document.querySelectorAll(".request-btn").forEach(btn => {
                    btn.addEventListener("click", function () {
                        let card = this.closest(".warranty-card");

                        // Retrieve data attributes
                        let warrantyNo = card.getAttribute("data-warranty-no");
                        let taskName = card.getAttribute("data-task-name");
                        let warrantyStartDate = card.getAttribute("data-warranty-start-date");
                        let warrantyStatus = card.querySelector(".status-label")?.innerText || "N/A";
                        let homeownerName = card.getAttribute("data-homeowner");
                        let contractorName = card.getAttribute("data-contractor");
                        let contractorEmail = card.getAttribute("data-contractor-email");


                        if (warrantyNo === "N/A" || !warrantyNo) {
                            alert("Error: No warranty number available!");
                            return;
                        }

                        // Ensure the correct hidden input is updated
                        let modalWarrantyNoInput = modal.querySelector("input[name='warrantyNo']");
                        modalWarrantyNoInput.value = warrantyNo;

                        // Set modal fields
                        document.getElementById("taskName").innerText = taskName;
                        document.getElementById("warrantyStartDate").innerText = warrantyStartDate;
                        document.getElementById("warrantyStatus").innerText = warrantyStatus;
                        document.getElementById("homeownerName").innerText = homeownerName;
                        document.getElementById("contractorName").innerText = contractorName;
                        document.getElementById("contractorEmail").innerText = contractorEmail;

                        document.getElementById("warrantyRequestModal").style.display = "block";
                    });
                });



                // Close modal on button click
                closeBtn.addEventListener("click", () => {
                    modal.style.display = "none";
                });

                // Handle form submission
                requestForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    errorMsg.style.display = "none"; // Hide previous errors

                    let formData = new FormData(requestForm);

                    fetch("{{ route('storeWarrantyRequest') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: formData
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Warranty request submitted successfully!");
                                    modal.style.display = "none";
                                    requestForm.reset();
                                } else {
                                    errorMsg.textContent = data.message;
                                    errorMsg.style.display = "block";
                                }
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                errorMsg.textContent = "An error occurred. Please try again.";
                                errorMsg.style.display = "block";
                            });
                });
            });


            document.addEventListener("DOMContentLoaded", function () {
                // Function to check if any checkbox is selected
                function updateRequestButton(card) {
                    let requestBtn = card.querySelector(".request-btn");
                    let anyChecked = card.querySelectorAll(".task-checkbox:checked").length > 0;

                    requestBtn.disabled = !anyChecked;
                    requestBtn.style.backgroundColor = anyChecked ? "#28a745" : "#ccc";
                }


                // Attach event listener to each task checkbox
                document.querySelectorAll(".task-checkbox").forEach(checkbox => {
                    checkbox.addEventListener("change", function () {
                        let card = this.closest(".warranty-card");
                        updateRequestButton(card);
                    });
                });

                // Ensure request buttons are initially disabled if no checkbox is checked
                document.querySelectorAll(".warranty-card").forEach(card => {
                    updateRequestButton(card);
                });
            });


            document.addEventListener("DOMContentLoaded", function () {
                let today = new Date().toISOString().split("T")[0];
                document.getElementById("requestDate").value = today;

                // Auto-fill the requester name
                let loggedInUserName = @json(Auth::user()->userName ?? Auth::use->name);
                        if (loggedInUserName) {
                    document.getElementById("requesterName").value = loggedInUserName;
                    document.getElementById("requesterName").readOnly = true;
                } else {
                    console.error("Failed to retrieve logged-in user name.");
                }
            });

            function checkVisibility() {
                let visibleCards = document.querySelectorAll('.warranty-card[style="display: block;"]').length;
                let noWarrantyContainer = document.querySelector('.no-warranty-container');

                // Safely update the visibility of the no-warranty-container
                if (noWarrantyContainer) {
                    if (visibleCards === 0) {
                        noWarrantyContainer.style.display = 'block';
                    } else {
                        noWarrantyContainer.style.display = 'none';
                    }
                }
            }
        </script>
    </body>
</html>
