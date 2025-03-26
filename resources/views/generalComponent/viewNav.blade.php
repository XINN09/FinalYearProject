<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home Renovation Navigation</title>
        <link rel="stylesheet" href="{{ asset('css/viewNav.css') }}">
    </head>
    <body>
        <div class="container">
            <!-- Navigation bar -->
            <div class="nav-bar">
                @isset($project)
                <!-- Main Table (visible to all users) -->
                <a href="{{ route('project.dashboard', ['projectID' => $project->projectID]) }}">Main Table</a>

                <!-- Gantt (visible to all users) -->
                <a href="{{ route('gantt', ['projectID' => $project->projectID]) }}">Gantt</a>

                <!-- Reports (visible to Contractors only) -->
                @if(Auth::user()->contractor && $project->contractorID === Auth::user()->contractor->contractorID)
                <a href="{{ route('report', ['projectID' => $project->projectID]) }}">Reports</a>
                @endif

                <!-- Documents (visible to all users) -->
                <a href="{{ route('document.project', ['projectID' => $project->projectID]) }}">Documents</a>


                <!-- Project Cost (visible to Contractors and Homeowners) -->
                @if((Auth::user()->contractor && $project->contractorID === Auth::user()->contractor->contractorID) || 
                (Auth::user()->homeowner && $project->ownerID === Auth::user()->homeowner->ownerID))
                <a href="{{ route('projectCost', ['projectID' => $project->projectID]) }}">Project Cost</a>
                @endif

                @if(Auth::user()->contractor && $project->contractorID === Auth::user()->contractor->contractorID)
                <a href="#" class="invite-homeowner-btn" style="color: white;" data-project-status="{{ $project->projectStatus ?? '' }}">
                    {{ $project->ownerID ? 'Homeowner Invited ✅' : 'Invite Homeowner' }}
                </a>
                @endif
                @endisset
            </div>

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
        </div>

        <!-- Notification for email sent confirmation -->
        <div id="notification" class="notification">Email has been sent successfully!</div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const navLinks = document.querySelectorAll(".nav-bar a:not(.invite-homeowner-btn)");

                // Function to check if the current URL matches any of the given paths
                function isActiveLink(linkHref, paths) {
                    return paths.some(path => window.location.href.includes(path));
                }

                navLinks.forEach(link => {
                    // Define the paths for "Report" and "Project Cost"
                    const reportPaths = ['/report/', '/report2/'];
                    const projectCostPaths = ['/cost/details', '/calendar', '/receipt', '/labour/cost'];

                    // Check if the link's href matches the current URL and add 'active'
                    if (link.href === window.location.href) {
                        link.classList.add("active");
                    }

                    // Special handling for "Report" and "Project Cost" links
                    if (link.href.includes('/report/') || link.href.includes('/report2/')) {
                        if (isActiveLink(link.href, reportPaths)) {
                            link.classList.add("active");
                        }
                    } else if (link.href.includes('/project/') && link.href.includes('/cost')) {
                        if (isActiveLink(link.href, projectCostPaths)) {
                            link.classList.add("active");
                        }
                    }

                    // Add click event to update the active state
                    link.addEventListener("click", function (event) {
                        // Remove 'active' from all links except the "Invite Homeowner" button
                        navLinks.forEach(navLink => navLink.classList.remove("active"));

                        // Add 'active' to the clicked link
                        this.classList.add("active");
                    });
                });

                // Function to open the invite modal
                function openInviteModal(event) {
                    event.preventDefault(); // Prevent default behavior of the button

                    const inviteModal = document.getElementById('inviteModal');
                    const inviteForm = document.getElementById('inviteForm');
                    const ownerInfo = document.getElementById('ownerInfo');
                    const ownerName = document.getElementById('ownerName');
                    const ownerEmail = document.getElementById('ownerEmail');

                    const projectID = "{{ $project->projectID ?? '' }}";

                    // Hide the modal content initially
                    inviteForm.style.display = 'none';
                    ownerInfo.style.display = 'none';

                    if (projectID) {
                        // Fetch homeowner details
                        fetch(`/project/${projectID}/owner`, {
                            method: "GET",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.owner) {
                                        // If homeowner exists, display their details
                                        ownerName.textContent = data.owner.userName;
                                        ownerEmail.textContent = data.owner.email;
                                        ownerInfo.style.display = 'block';
                                        inviteForm.style.display = 'none'; // Hide the invite form
                                    } else {
                                        // If no homeowner exists, show the invite form
                                        inviteForm.style.display = 'block';
                                        ownerInfo.style.display = 'none';
                                    }
                                })
                                .catch(error => {
                                    console.error("Error fetching homeowner data:", error);
                                    // Fallback: Show the invite form if there's an error
                                    inviteForm.style.display = 'block';
                                    ownerInfo.style.display = 'none';
                                });
                    }

                    // Show the modal
                    inviteModal.style.display = 'block';
                }

                // Attach the openInviteModal function to the invite button
                const inviteButton = document.querySelector('.invite-homeowner-btn');
                const projectStatus = inviteButton.getAttribute('data-project-status');

                // Debugging: Log the project status
                console.log("Project Status:", projectStatus);

                // Disable the button if the project status is 'completed'
                if (projectStatus === 'Completed') {
                    console.log("Project is completed. Disabling invite button.");
                    inviteButton.disabled = true;
                    inviteButton.style.backgroundColor = '#ccc'; // Gray out the button
                    inviteButton.style.cursor = 'not-allowed';
                } else {
                    console.log("Project is not completed. Enabling invite button.");
                    // Attach the event listener only if the project is not completed
                    inviteButton.addEventListener('click', openInviteModal);
                }

                // Close the modal when the close button is clicked
                const closeModal = document.getElementById('closeModal');
                closeModal.addEventListener('click', () => {
                    document.getElementById('inviteModal').style.display = 'none';
                });

                // Close the modal when clicking outside of it
                window.addEventListener('click', (event) => {
                    if (event.target === document.getElementById('inviteModal')) {
                        document.getElementById('inviteModal').style.display = 'none';
                    }
                });
            });

// Function to send the invite
            function sendInvite() {
                const emailInput = document.getElementById('inviteEmail');
                const email = emailInput.value;
                const role = document.querySelector('input[name="role"]:checked').value;
                const projectID = "{{ $project->projectID ?? '' }}";

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

                                    // Update the invite button text
                                    const inviteButton = document.querySelector('.invite-homeowner-btn');
                                    if (inviteButton) {
                                        inviteButton.textContent = 'Homeowner Invited';
                                    }
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
        </script>
    </body>
</html>