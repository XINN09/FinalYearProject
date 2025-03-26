<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/navigationBar.css') }}">
    </head>
    <body>
        <nav class="nav-css">
            <ul>
                <!-- Dynamically set the title based on the current route -->
                <li style="text-align: left;">
                    @if(request()->routeIs('home'))
                    Alloymont Project Management
                    @elseif(request()->routeIs('work'))
                    My Work
                    @elseif(request()->routeIs('team'))
                    Team Management
                    @elseif(request()->routeIs('issues'))
                    Issues Management
                    @elseif(request()->routeIs('project.dashboard'))
                    {{ $currentProject->projectName }} - Dashboard
                    @elseif(request()->routeIs('report'))
                    {{ $currentProject->projectName }} - Report
                    @elseif(request()->routeIs('report2'))
                    {{ $currentProject->projectName }} - Report
                    @elseif(request()->routeIs('document.project'))
                    {{ $currentProject->projectName }} - Documents
                    @elseif(request()->routeIs('gantt'))
                    {{ $currentProject->projectName }} - Gantt View
                    @elseif(request()->routeIs('projectCost'))
                    {{ $currentProject->projectName }} - Cost Management
                    @else
                    Home Renovation - Project Cost
                    @endif
                </li>
                <li class="icon-container">
                    <a href="{{ route('projectHistory') }}" class="nav-link">Project History</a>

                    <!-- Add Warranty link (visible only to homeowner and contractor) -->
                    @if(in_array($role, ['homeowner', 'contractor']))
                    <a href="{{ route('getWarrantyRecords') }}" class="nav-link">Warranty</a>
                    @endif
                    
                    @if(isset($currentProject))
                    <a href="#" class="invite-link">
                        <img src="{{ asset('icon/invite.png') }}" alt="Invite Icon" class="inviteIcon-img"/>
                    </a>
                    @else
                    <a href="#" class="invite-link" style="display:none;">
                        <img src="{{ asset('icon/invite.png') }}" alt="Invite Icon" class="inviteIcon-img"/>
                    </a>
                    @endif

                    <div class="user-icon">
                        <div class="user-avatar" id="userAvatar">
                            {{ substr(Auth::user()->userName, 0, 1) }}
                        </div>
                        <span class="user-role-label {{ $role }}">{{ ucfirst($role) }}</span>


                        <ul class="dropdown">
                            @auth
                            <li><span>Hi, {{ Auth::user()->userName }}</span></li>
                            <li><a href="{{ route('userProfile') }}">Manage Account</a></li>
                            <li><a href="{{ route('projectHistory') }}">Project History</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="logout-button" style="background-color: transparent; color: color:var(--fontgray);">Sign Out</button>
                                </form>
                            </li>
                            @else
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                            @endauth
                        </ul>
                    </div>
                </li>
            </ul>

            <!-- Invite Modal -->
            <!-- Invite Modal -->
            <div class="modal" id="inviteModal">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <h2>Invite Homeowner to Project</h2>

                    <div id="inviteForm">
                        <input type="email" id="inviteEmail" placeholder="Enter email address" /><br>
                        <div class="invite-options" style="display: none;">
                            <label><input type="radio" name="role" value="Homeowner" checked>Homeowner</label>
                            <label><input type="radio" name="role" value="Worker" style="display: none;"></label>
                        </div>
                        <button class="invite-button" onclick="sendInvite()">Invite</button>
                    </div>

                    <div id="ownerInfo" style="display: none;">
                        <p><strong>Owner Name:</strong> <span id="ownerName"></span></p>
                        <p><strong>Owner Email:</strong> <span id="ownerEmail"></span></p>
                        <div class="owner-message">
                            <span>✅ The owner is already invited to this project.</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Notification for email sent confirmation -->
            <div id="notification" class="notification">Email has been sent successfully!</div>
        </nav>

        <script>

            document.addEventListener('DOMContentLoaded', () => {
                const inviteIcon = document.querySelector('.inviteIcon-img');
                const inviteModal = document.getElementById('inviteModal');
                const closeModal = document.getElementById('closeModal');
                const inviteForm = document.getElementById('inviteForm');
                const ownerInfo = document.getElementById('ownerInfo');
                const ownerName = document.getElementById('ownerName');
                const ownerEmail = document.getElementById('ownerEmail');
                const modalContent = document.querySelector('.modal-content');

                inviteIcon.addEventListener('click', async () => {
                    const projectID = "{{ $currentProject->projectID ?? '' }}";

                    // Hide the modal content initially
                    modalContent.style.display = 'none';

                    if (projectID) {
                        try {
                            const response = await fetch(`/project/${projectID}/owner`, {
                                method: "GET",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                }
                            });

                            const data = await response.json();

                            if (data.owner) {
                                ownerName.textContent = data.owner.userName;
                                ownerEmail.textContent = data.owner.email;
                                inviteForm.style.display = 'none';
                                ownerInfo.style.display = 'block';
                            } else {
                                inviteForm.style.display = 'block';
                                ownerInfo.style.display = 'none';
                            }

                            // Show the modal content after the data is fetched
                            modalContent.style.display = 'block';
                        } catch (error) {
                            console.error("Error fetching owner data:", error);
                            // Fallback: Show the invite form if there's an error
                            inviteForm.style.display = 'block';
                            ownerInfo.style.display = 'none';
                            modalContent.style.display = 'block';
                        }
                    }

                    // Show the modal
                    inviteModal.style.display = 'block';
                });

                closeModal.addEventListener('click', () => {
                    inviteModal.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target === inviteModal) {
                        inviteModal.style.display = 'none';
                    }
                });
            });


            document.addEventListener('DOMContentLoaded', () => {
                const userIcon = document.querySelector('.user-icon');
                const dropdown = document.querySelector('.dropdown');
                dropdown.style.display = 'none';
                document.addEventListener('click', (event) => {
                    if (!userIcon.contains(event.target)) {
                        dropdown.style.display = 'none';
                    }
                });
                userIcon.addEventListener('click', () => {
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                });
                const inviteIcon = document.querySelector('.inviteIcon-img');
                const inviteModal = document.getElementById('inviteModal');
                const closeModal = document.getElementById('closeModal');
                inviteIcon.addEventListener('click', () => {
                    inviteModal.style.display = 'block';
                });
                closeModal.addEventListener('click', () => {
                    inviteModal.style.display = 'none';
                });
                window.addEventListener('click', (event) => {
                    if (event.target === inviteModal) {
                        inviteModal.style.display = 'none';
                    }
                });
            });
            function copyLink() {
                const linkInput = document.querySelector('#inviteModal input[type="text"]');
                linkInput.select();
                document.execCommand("copy");
                alert("Link copied to clipboard!");
            }

            function sendInvite() {
                const emailInput = document.getElementById('inviteEmail');
                const email = emailInput.value;
                const role = document.querySelector('input[name="role"]:checked').value;
                const projectID = "{{ $currentProject->projectID ?? '' }}"; // Ensure the project ID is set

                if (email && projectID) {
                    fetch("{{ route('sendOwnerInvite') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            email: email,
                            projectID: projectID
                        })
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Invitation sent successfully!");
                                    emailInput.value = ''; // Clear input
                                    document.getElementById('inviteModal').style.display = 'none'; // Close modal
                                } else {
                                    alert("Failed to send invitation: " + data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                alert("Failed to send invitation. Please try again.");
                            });
                } else {
                    alert("Please enter a valid email address.");
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const userAvatar = document.getElementById('userAvatar');
                const userID = "{{ Auth::user()->userID }}"; // Assuming userID like "U250001"
                const lastDigit = parseInt(userID.slice(-1), 10) || 0;  // Fallback to 0 if anything goes wrong

                const colors = [
                    '#ff6b6b', // 0 - Red
                    '#ffb400', // 1 - Orange
                    '#ffdd57', // 2 - Yellow
                    '#9cd326', // 3 - Lime
                    '#1dd1a1', // 4 - Teal
                    '#48dbfb', // 5 - Light Blue
                    '#5f27cd', // 6 - Purple
                    '#f368e0', // 7 - Pink
                    '#ff9ff3', // 8 - Light Pink
                    '#222f3e'  // 9 - Dark Gray
                ];

                userAvatar.style.backgroundColor = colors[lastDigit];
            });



        </script>

    </body>
</html>