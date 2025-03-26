<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Team</title>
        <link rel="stylesheet" href="{{ asset('css/team.css') }}">
        <style>
            /* Your existing styles */
        </style>
    </head>
    <body>
        <div class="main-container">
            <aside class="sidebar">
                @include('generalComponent.sidebar')
            </aside>
            <div class="main-content">
                <header class="navigation">
                    @include('generalComponent.navigation')
                </header>

                <div class="content">

                    @if($user->worker)
                    <h2 style="margin: 0px 0px 15px 0px;">My Contractors</h2>
                    @if($contractors->isNotEmpty())
                    <div class="team-container">
                        @foreach ($contractors as $contractor)
                        <div class="team-card">
                            @php
                            $randomIcon = 'userIcon' . rand(3, 8) . '.png';
                            $userName = $contractor->user->userName ?? $contractor->email;
                            @endphp
                            <img src="{{ asset('icon/' . $randomIcon) }}" alt="{{ $userName }}">
                            <h3>{{ $userName }}</h3>
                            <span class="badge badge-accepted">Contractor</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <!-- Center the "No Contractors" message -->
                    <div class="no-team-wrapper">
                        <div class="no-worker-container">
                            <img src="{{ asset('icon/people.png') }}" alt="No contractors found" class="no-worker-image">
                            <p>No contractors found.</p>
                        </div>
                    </div>
                    @endif
                    @endif
                    <!-- Display Workers (Only for Contractors) -->
                    @if($user->contractor)
                    <h2>My Team</h2>
                    <div style="margin: 15px 0;">
                        <button class="action-button" onclick="inviteWorker()">+ Invite Worker</button>
                    </div>

                    <div class="team-container">
                        @if(count($team) > 0)
                        @foreach ($team as $member)
                        <div class="team-card" onclick="showWorkerInfo('{{ $member->workerID }}', '{{ strtolower($member->status) }}')">
                            @php
                            $randomIcon = 'userIcon' . rand(3, 8) . '.png';
                            $userName = $member->worker?->user?->userName ?? $member->email;
                            @endphp
                            <img src="{{ asset('icon/' . $randomIcon) }}" alt="{{ $userName }}">
                            <h3>{{ $userName }}</h3>
                            <span class="badge badge-{{ strtolower($member->status) }}">{{ ucfirst($member->status) }}</span>
                            <div>
                                @if(strtolower($member->status) !== 'accepted')
                                <button class="action-button" onclick="resendInvitation('{{ $member->workerID }}', '{{ $member->email }}')">Re-send</button>
                                @endif
                                <button class="remove-button" onclick="removeWorker('{{ $member->workerID }}', '{{ $member->email }}', event)">Remove</button>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <!-- Center the "No Workers" message -->
                        <div class="no-team-wrapper">
                            <div class="no-worker-container">
                                <img src="{{ asset('icon/people.png') }}" alt="No workers found" class="no-worker-image">
                                <p>No workers found.</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="footer">
                    <span>Total Count: {{ count($team) }}</span>
                </div>
            </div>

            <!-- Modals and other elements -->
            <div id="inviteWorkerModal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <h3>Invite a Worker</h3>
                    <input type="email" id="workerEmail" placeholder="Enter worker's email">
                    <input type="number" step="0.01" id="dailyPay" placeholder="Enter daily pay (optional)">
                    <button onclick="sendInvitation()">Send Invitation</button>
                    <button onclick="closeModal()">Cancel</button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="loading-spinner">
                <div class="spinner"></div>
                <p>Sending invitation...</p>
            </div>

            <div id="workerInfoPanel" class="worker-info-panel" style="display:none;">
                <div class="worker-info-card">
                    <div class="worker-info-header">
                        <h3>Worker Information</h3>
                        <button class="close-button" onclick="closeWorkerInfo()">✕</button>
                    </div>
                    <div id="workerDetails" class="worker-info-details"></div>
                </div>
            </div>
        </div>

        <script>
            function resendInvitation(workerID, email) {
            // Display a confirmation dialog
            if (confirm('Are you sure you want to re-send the invitation?')) {
            // Send a request to the server to re-send the invitation
            fetch('{{ route("resendInvitation") }}', {
            method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                    worker_id: workerID, // Can be null
                            email: email // Email to identify the record if workerID is null
                    })
            })
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                    alert('Invitation re-sent successfully!');
                    } else {
                    alert('Failed to re-send invitation: ' + data.message);
                    }
                    })
                    .catch(error => {
                    console.error('Error re-sending invitation:', error);
                    alert('An error occurred while re-sending the invitation.');
                    });
            }
            }

            function removeWorker(workerID) {
            if (confirm('Are you sure you want to remove this worker?')) {
            // Example AJAX call to remove worker
            alert('Removing Worker ' + workerID);
            // Actual logic can be an axios or fetch call to your backend
            }
            }

            function inviteWorker() {
            document.getElementById('inviteWorkerModal').style.display = 'flex';
            }

            function closeModal() {
            document.getElementById('inviteWorkerModal').style.display = 'none';
            }

            function sendInvitation() {
            const email = document.getElementById('workerEmail').value;
            const dailyPay = document.getElementById('dailyPay').value;
            if (!email) {
            alert('Please fill in all fields.');
            return;
            }

            // Show the loading spinner
            const loadingSpinner = document.getElementById('loadingSpinner');
            loadingSpinner.style.display = 'flex';
            fetch('{{ route("inviteWorker") }}', {
            method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: email, dailyPay: dailyPay })
            })
                    .then(response => response.json())
                    .then(data => {
                    // Hide the loading spinner
                    loadingSpinner.style.display = 'none';
                    alert(data.message);
                    closeModal();
                    location.reload(); // Refresh team list
                    })
                    .catch(error => {
                    // Hide the loading spinner
                    loadingSpinner.style.display = 'none';
                    alert('Failed to send invitation.');
                    console.error(error);
                    });
            }

            function closeWorkerInfo() {
            document.getElementById('workerInfoPanel').style.display = 'none';
            }

            function showWorkerInfo(workerID, status) {
            if (status !== 'accepted') {
            alert('This worker has not been accepted yet.');
            return;
            }

            fetch(`/getWorkerInfo/${workerID}`)
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                    const worker = data.worker;
                    const user = worker.user;
                    document.getElementById('workerDetails').innerHTML = `
                                <p><strong>Name:</strong> ${user.userName}</p>
                                <p><strong>Email:</strong> ${user.email}</p>
                                <p><strong>Phone:</strong> ${user.userPhone ?? 'N/A'}</p>
                                <p><strong>Status:</strong> ${worker.availabilityStatus}</p>
                                <p><strong>Type:</strong> ${worker.workerType}</p>
                            `;
                    document.getElementById('workerInfoPanel').style.display = 'flex';
                    } else {
                    alert('Failed to fetch worker information.');
                    }
                    })
                    .catch(error => {
                    console.error('Error fetching worker info:', error);
                    alert('Error fetching worker info.');
                    });
            }

            function removeWorker(workerID, email, event) {
            event.stopPropagation(); // Prevent triggering the card's click event

            // Display a confirmation dialog
            if (confirm('Are you sure you want to remove this worker?')) {
            // Send a request to the server to delete the worker
            fetch('{{ route("removeWorker") }}', {
            method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                    worker_id: workerID, // Can be null
                            email: email // Email to identify the record if workerID is null
                    })
            })
                    .then(response => response.json())
                    .then(data => {
                    if (data.success) {
                    alert('Worker removed successfully!');
                    location.reload(); // Refresh the page to reflect changes
                    } else {
                    alert('Failed to remove worker: ' + data.message);
                    }
                    })
                    .catch(error => {
                    console.error('Error removing worker:', error);
                    alert('An error occurred while removing the worker.');
                    });
            }
            }
        </script>
    </body>
</html>