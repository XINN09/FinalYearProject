<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Warranty Service</title>
        <link rel="stylesheet" href="{{ asset('css/warrantyService.css') }}">
    </head>
    <body>
        <aside class="sidebar">
            @include('generalComponent.userNav')
        </aside>

        <div class="main-content">
            <h2 class="title">Warranty Service Requests</h2>

            <div class="request-record">
                @forelse ($warrantyRequests as $request)
                <div class="request-card">
                    <div class="header">
                        <h3> 
                            <i class="icon-tools {{ strtolower($request->requestStatus) }}"></i>
                            {{ $request->requestTitle }}
                        </h3>
                        <span class="status {{ strtolower($request->requestStatus) }}">{{ ucfirst($request->requestStatus) }}</span>
                    </div>
                    <div class="request-details">
                        <p><span class="label">Requested By:</span> {{ $request->requesterName }}</p>
                        <p><span class="label">Submitted On:</span> {{ $request->requestDate }}</p>
                        <p><span class="label">Location:</span> {{ $request->projectAddress }}</p>
                        <p><span class="label">Description:</span> {{ $request->requestDesc }}</p>
                    </div>

                    <div class="action-btns">
                        <button class="details-btn" onclick="toggleDetails('{{ $request->requestID }}')">View Details</button>


                        @if($request->requestStatus == 'pending' && $request->task->warranty && !(auth()->user()->homeowner && auth()->user()->homeowner->ownerID == $request->task->project?->homeowner?->ownerID))
                        <button class="accept-btn" onclick="acceptRequest('{{ $request->requestID }}')">Accept and Create Issue</button>
                        <button class="deny-btn" onclick="denyRequest('{{ $request->requestID }}')">Deny</button>
                        @endif


                    </div>

                    <div class="expand-section" id="details-{{ $request->requestID }}" style="display: none;">
                        <h4>Warranty Details</h4>
                        <div class="expand-content">
                            <p><span class="label">Warranty No:</span> {{ $request->warrantyNo }}</p>
                            <p><span class="label">Expiry Date:</span> 
                                {{ \Carbon\Carbon::parse($request->warranty->endDate)->addDay()->format('Y-m-d') }}
                            </p>
                            <p><span class="label">Description:</span> {{ $request->warranty->description }}</p>
                        </div>

                        <h4>Task Details</h4>
                        <div class="expand-content">
                            <p><span class="label">Project Name:</span> {{ $request->task->project->projectName }}</p>
                            <p><span class="label">Task Name:</span> {{ $request->task->taskName }}</p>
                        </div>
                    </div>

                </div>
                @empty
                <div class="no-warranty-container">
                    <img src="{{ asset('images/notFound.png') }}" alt="No Warranty Records" class="no-warranty-image" />
                    <p class="no-warranty-message">No warranty request records found.</p>
                </div>
                @endforelse
            </div>
        </div>


        <script>
            function acceptRequest(requestId) {
            alert(`Request ${requestId} has been accepted. A new project has been created under "Issues."`);
            window.location.href = "{{ route('issues') }}";
            }

            function toggleDetails(requestId) {
            const detailsSection = document.getElementById(`details-${requestId}`);
            if (detailsSection.classList.contains("active")) {
            // Hide details
            detailsSection.classList.remove("active");
            setTimeout(() => {
            detailsSection.style.display = "none"; // Ensure it's hidden after animation
            }, 300); // Wait for animation to finish
            } else {
            // Show details
            detailsSection.style.display = "block";
            setTimeout(() => {
            detailsSection.classList.add("active");
            }, 10); // Small delay for animation effect
            }
            }

            function denyRequest(requestId) {
            if (confirm(`Are you sure you want to deny request ${requestId}?`)) {
            fetch(`{{ url('/warranty/deny') }}/${requestId}`, {  // Use Laravel route correctly
            method: "POST",
                    headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ status: "denied" })
            })
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                    alert(`Request ${requestId} has been denied.`);
                    location.reload(); // Refresh the page to reflect the changes
                    } else {
                    alert("Failed to update status. Please try again.");
                    }
                    })
                    .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred. Please try again.");
                    });
            }
            }

            function acceptRequest(requestId) {
            if (confirm(`Are you sure you want to accept request ${requestId} and create an issue?`)) {
            fetch(`{{ url('/warranty/accept') }}/${requestId}`, {
            method: "POST",
                    headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ status: "accepted" })
            })
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                    alert(`Request ${requestId} has been accepted and an issue has been created.`);
                    location.reload(); // Refresh to reflect changes
                    } else {
                    alert("Failed to update status. Please try again.");
                    }
                    })
                    .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred. Please try again.");
                    });
            }
            }



        </script>
    </body>
</html>
