<head>
    <meta charset="UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>


<!-- Sidebar Structure -->
<div class="sidebar" id="sidebar">
    <div id="Workspace">
        <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo-css"/>
        <h2>AlloyMont Workspace</h2>
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    </div>
    <div id="list">
        <hr style="margin: 5px 0">
        <ul>
            <!-- Home link (visible to all users) -->
            <li><img src="{{ asset('icon/home.png') }}" alt="Home" class="icon-css home-icon"/><a href="{{ route('home') }}">Home</a></li>

            <!-- My Work link (visible to Contractors and Workers) -->
            @if(Auth::user()->contractor || Auth::user()->worker)
            <li><img src="{{ asset('icon/work.png') }}" alt="Work" class="icon-css work-icon"/><a href="{{ route('work') }}">My work</a></li>
            @endif

            <!-- My Worker / My Contractor link (visible to Contractors and Workers) -->
            @if(Auth::user()->contractor || Auth::user()->worker)
            <li>
                <img src="{{ asset('icon/team.png') }}" alt="Team" class="icon-css team-icon"/>
                <a href="{{ route('team') }}">
                    @if(Auth::user()->contractor)
                    My Worker
                    @elseif(Auth::user()->worker)
                    My Contractor
                    @endif
                </a>
            </li>
            @endif

            <!-- Issues link (visible to Contractors and Homeowners) -->
            @if(Auth::user()->contractor || Auth::user()->homeowner)
            <li><img src="{{ asset('icon/issues.png') }}" alt="Issues" class="icon-css issues-icon"/><a href="{{ route('issues') }}">Issues</a></li>
            @endif
        </ul>

        <div class="projects-container">
            <!-- Recent Projects Section (visible to all users) -->
            <h4 class="recent-project-header">
                Recent Projects
                <div class="project-actions">
                    <button class="icon-btn search-btn" onclick="toggleSearchBar()" style="background: none;">
                        <img src="{{ asset('icon/search.png') }}" alt="Search"/>
                    </button>
                    @if(Auth::user()->contractor) <!-- Only contractors can see the create project button -->
                    <button class="icon-btn create-project-btn" onclick="openNewProjectPanel()">+</button>
                    @endif
                </div>
            </h4>

            <!-- Hidden Search Bar (initially hidden, shown when search icon clicked) -->
            <input type="text" id="searchInput" class="hidden-search-bar" placeholder="Search project..."/>

            <!-- Scrollable List -->
            <div id="scrollable-section">
                <ul id="recentProjectsList">
                    @forelse ($recentProjects as $project)
                    <li data-project-id="{{ $project['id'] }}">
                        <img src="{{ asset('icon/project.png') }}" alt="Project" class="icon-css project-icon"/>
                        <a href="{{ route('project.dashboard', ['projectID' => $project['id']]) }}">{{ $project['name'] }}</a>
                    </li>
                    @empty
                    <li style="font-size: 14px; justify-content: center;">No recent projects found.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Completed Projects Section -->
            <div id="completed-projects-section">
                <h4 class="completed-project-header" style="padding: 0;">
                    <a href="#" id="completedProjectsLink">
                        <img src="{{ asset('icon/completed.png') }}" alt="Completed Projects" class="icon-css completed-icon"/>
                        Completed Projects
                    </a>
                </h4>
                <div id="completedProjectsList" style="display: none;">
                    <!-- Completed projects will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- New Project Panel -->
    <div id="newProjectPanel" class="panel">
        <div class="panel-content">
            <span class="closeProject" onclick="closeNewProjectPanel()">&times;</span>
            <h2>Create New Project</h2>
            <form id="createNewProjectForm" action="{{ route('createProject') }}" method="POST">
                @csrf
                <!-- Project Name -->
                <div class="form-group">
                    <label for="projectName">Project Name:</label>
                    <input type="text" id="projectName" name="projectName" required />
                    <div id="projectNameError" class="error-message" style="color: red; display: none;"></div>
                </div>

                <!-- Project Owner -->
                <div class="form-group non-editable">
                    <label for="projectOwner">Project Contractor:</label>
                    <div class="readonly-field">
                        <input type="text" id="projectOwner" name="projectOwner" value="{{ Auth::user()->userName }}" readonly title="Project contractor name is non-editable"/>
                        <span class="lock-icon">🔒</span>
                    </div>
                </div>

                <!-- Start and End Dates -->
                <div class="form-group date-group">
                    <div class="form-group">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="startDate" required />
                        <div id="startDateError" class="error-message" style="color: red; display: none;"></div>
                    </div>
                    <div>
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="endDate" />
                        <div id="endDateError" class="error-message" style="color: red; display: none;"></div>
                    </div>
                    <div class="form-group non-editable">
                        <label for="duration">Duration:</label>
                        <div class="readonly-field">
                            <input type="text" id="duration" name="duration" class="duration" readonly title="Auto-calculated"/>
                            <span class="lock-icon">🔒</span>
                        </div>
                    </div>
                </div>

                <!-- Project Address -->
                <div class="form-group">
                    <label for="projectAddress">Project Address:</label>
                    <textarea id="projectAddress" name="projectAddress" rows="4" required></textarea>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="projectDescription">Description:</label>
                    <textarea id="projectDescription" name="projectDescription" rows="3" required></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit">Create Project</button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('../js/sidebar.js') }}"></script>

<script>
                document.addEventListener("DOMContentLoaded", function () {
                    const sidebarItems = document.querySelectorAll(".sidebar li");
                    const homeIcon = document.querySelector(".home-icon");
                    const workIcon = document.querySelector(".work-icon");
                    const teamIcon = document.querySelector(".team-icon");
                    const issuesIcon = document.querySelector(".issues-icon");
                    const projectIcons = document.querySelectorAll(".project-icon");

                    // Define paths for project-related pages
                    const projectPaths = [
                        '/project/', // Main project dashboard
                        '/gantt/', // Gantt chart
                        '/document/', // Documents
                        '/report/', // Reports
                        '/report2/', // Report 2
                        '/cost/', // Project cost
                        '/cost/details', // Cost details
                        '/calendar', // Calendar
                        '/receipt', // Receipt
                        '/labour/cost' // Labour cost
                    ];

                    // Get the current project ID from the URL
                    const currentProjectID = window.location.pathname.split('/')[2]; // Assuming projectID is the third segment in the URL

                    // Highlight the active project link in the sidebar
                    if (projectPaths.some(path => window.location.pathname.includes(path))) {
                        const projectLinks = document.querySelectorAll("#recentProjectsList li");
                        projectLinks.forEach(link => {
                            const projectID = link.getAttribute("data-project-id");
                            if (projectID === currentProjectID) {
                                link.classList.add("active");
                                updateIconState(link);
                            }
                        });
                    }

                    // Update icon state based on the active link
                    function updateIconState(item) {
                        // Reset all icons to default state
                        homeIcon.src = "{{ asset('icon/home.png') }}";
                        workIcon.src = "{{ asset('icon/work.png') }}";
                        issuesIcon.src = "{{ asset('icon/issues.png') }}";
                        projectIcon.src = "{{ asset('icon/project.png') }}";
                        teamIcon.src = "{{ asset('icon/team.png') }}";

                        const link = item.querySelector("a").getAttribute("href");

                        if (link === "{{ route('home') }}") {
                            homeIcon.src = "{{ asset('icon/home2.png') }}";
                        } else if (link === "{{ route('work') }}") {
                            workIcon.src = "{{ asset('icon/work2.png') }}";
                        } else if (link === "{{ route('issues') }}") {
                            issuesIcon.src = "{{ asset('icon/issues2.png') }}";
                        } else if (link === "{{ route('team') }}") {
                            teamIcon.src = "{{ asset('icon/team2.png') }}";
                        } else if (projectPaths.some(path => link.includes(path))) {
                            item.querySelector(".project-icon").src = "{{ asset('icon/project2.png') }}";
                        }
                    }

                    // Toggle sidebar functionality
                    function toggleSidebar() {
                        const sidebar = document.querySelector('.sidebar');
                        const mainContent = document.querySelector('.main-content');

                        // Toggle the collapsed class on the sidebar
                        sidebar.classList.toggle('collapsed');

                        // Adjust the main content width based on sidebar state
                        if (sidebar.classList.contains('collapsed')) {
                            mainContent.style.width = "100%";
                        } else {
                            mainContent.style.width = "calc(100% - 250px)";
                        }
                    }
                });


                document.addEventListener("DOMContentLoaded", function () {
                    const sidebarItems = document.querySelectorAll(".sidebar li");

                    sidebarItems.forEach(item => {
                        const link = item.querySelector("a");

                        // Set active class if link matches current URL
                        if (link && link.href === window.location.href) {
                            item.classList.add("active");
                        }

                        // On click, set clicked item to active and remove active from others
                        item.addEventListener("click", function () {
                            sidebarItems.forEach(i => i.classList.remove("active"));
                            item.classList.add("active");
                        });
                    });
                });

                function openNewProjectPanel() {
                    document.getElementById("newProjectPanel").style.display = "block";
                }

                function closeNewProjectPanel() {
                    document.getElementById("newProjectPanel").style.display = "none";
                }

                document.addEventListener("DOMContentLoaded", function () {
                    const recentProjectsList = document.getElementById("recentProjects");
                    // Fetch and render projects
                    async function fetchAndRenderProjects() {
                        try {
                            const response = await fetch("{{ route('getProjects') }}");
                            if (!response.ok) {
                                throw new Error("Failed to fetch projects");
                            }

                            const projects = await response.json();
                            renderProjects(projects);
                        } catch (error) {
                            console.error("Error fetching projects:", error);
                        }
                    }

                    // Render projects in the sidebar
                    function renderProjects(projects) {
                        // Clear existing projects
                        recentProjectsList.innerHTML = "";
                        // Sort projects alphabetically
                        projects.sort((a, b) => a.projectName.localeCompare(b.projectName));
                        // Populate the sidebar with sorted projects
                        projects.forEach(project => {
                            const listItem = document.createElement("li");
                            listItem.innerHTML = `
                <img src="{{ asset('icon/project.png') }}" alt="Project" class="icon-css project-icon"/>
                <a href="${project.dashboardUrl}">${project.projectName}</a>`;

                            recentProjectsList.appendChild(listItem)
                        });
                    }

                    document.getElementById("createNewProjectForm").addEventListener("submit", async function (e) {
                        e.preventDefault(); // Prevent the default form submission

                        // Clear previous error messages
                        document.querySelectorAll(".error-message").forEach((el) => {
                            el.style.display = "none";
                            el.textContent = "";
                        });
                        // Get form field values
                        const projectName = document.getElementById("projectName").value;
                        const projectAddress = document.getElementById("projectAddress").value;
                        const projectDescription = document.getElementById("projectDescription").value;
                        const startDate = document.getElementById("startDate").value;
                        const endDate = document.getElementById("endDate").value || null; // Allow endDate to be null
                        const projectOwner = document.getElementById("projectOwner").value;
                        // Prepare request data
                        const requestData = {
                            projectName,
                            projectAddress,
                            projectDescription,
                            startDate,
                            endDate,
                            projectOwner,
                            projectStatus: "Active", // Default project status
                        };
                        try {
                            const response = await fetch("{{ route('createProject') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify(requestData),
                            });
                            if (response.ok) {
                                alert("Project created successfully!");
                                window.location.reload(); // Refresh the page
                            } else if (response.status === 422) {
                                // Handle validation errors
                                const errorData = await response.json();
                                for (const field in errorData.errors) {
                                    const errorElement = document.getElementById(`${field}Error`);
                                    if (errorElement) {
                                        errorElement.style.display = "block";
                                        errorElement.textContent = errorData.errors[field].join(", ");
                                    }
                                }
                            } else {
                                alert("An unexpected error occurred. Please try again.");
                            }
                        } catch (error) {
                            console.error("An error occurred:", error);
                            alert("An error occurred while creating the project. Please try again later.");
                        }
                    });

                });


                function toggleSearchBar() {
                    const searchBar = document.getElementById('searchInput');
                    searchBar.classList.toggle('active');
                    if (searchBar.classList.contains('active')) {
                        searchBar.focus();
                    } else {
                        searchBar.value = ''; // clear search when closing
                    }
                }

                document.addEventListener("DOMContentLoaded", function () {
                    const searchBar = document.getElementById('searchInput');

                    // Toggle search bar visibility
                    window.toggleSearchBar = function () {
                        searchBar.classList.toggle('active');
                        if (searchBar.classList.contains('active')) {
                            searchBar.focus();
                        } else {
                            searchBar.value = '';
                            filterProjects('');
                        }
                    };

                    // Live search filtering
                    searchBar.addEventListener('input', function () {
                        filterProjects(this.value);
                    });

                    function filterProjects(query) {
                        const projects = document.querySelectorAll('#recentProjectsList li:not(#noProjectsMessage)');
                        const lowerQuery = query.toLowerCase();
                        let found = false;

                        projects.forEach(project => {
                            const projectName = project.textContent.trim().toLowerCase();
                            if (projectName.includes(lowerQuery)) {
                                project.style.display = '';
                                found = true;
                            } else {
                                project.style.display = 'none';
                            }
                        });

                        let noResultMessage = document.getElementById('noProjectsMessage');

                        if (!found) {
                            if (!noResultMessage) {
                                noResultMessage = document.createElement('li');
                                noResultMessage.id = 'noProjectsMessage';
                                noResultMessage.textContent = 'No projects found.';
                                noResultMessage.style.fontSize = '14px';
                                noResultMessage.style.justifyContent = 'center';
                                noResultMessage.style.display = 'flex';
                                noResultMessage.style.padding = '5px';
                                document.getElementById('recentProjectsList').appendChild(noResultMessage);
                            }
                        } else {
                            if (noResultMessage) {
                                noResultMessage.remove();
                            }
                        }
                    }
                });

                document.addEventListener("DOMContentLoaded", function () {
                    const startDateInput = document.getElementById("startDate");
                    const endDateInput = document.getElementById("endDate");
                    const durationInput = document.getElementById("duration");

                    function calculateDuration() {
                        const startDate = new Date(startDateInput.value);
                        const endDate = new Date(endDateInput.value);

                        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                            durationInput.value = "";
                            return;
                        }

                        const timeDiff = endDate - startDate;
                        if (timeDiff < 0) {
                            durationInput.value = "Invalid Date Range";
                            return;
                        }

                        const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end date
                        durationInput.value = `${days} days`;
                    }

                    startDateInput.addEventListener("change", calculateDuration);
                    endDateInput.addEventListener("change", calculateDuration);
                });

                document.addEventListener("DOMContentLoaded", function () {
                    const completedProjectsLink = document.getElementById('completedProjectsLink');
                    const completedProjectsList = document.getElementById('completedProjectsList');

                    // Toggle the visibility of the completed projects list
                    completedProjectsLink.addEventListener('click', function (e) {
                        e.preventDefault(); // Prevent the default link behavior

                        if (completedProjectsList.style.display === 'none') {
                            fetchCompletedProjects();
                            completedProjectsList.style.display = 'block';
                        } else {
                            completedProjectsList.style.display = 'none';
                        }
                    });

                    // Handle clicks on completed projects
                    completedProjectsList.addEventListener('click', function (e) {
                        const clickedItem = e.target.closest('li');
                        if (clickedItem) {
                            // Remove active class from all items
                            document.querySelectorAll('#completedProjectsList li').forEach(item => {
                                item.classList.remove('active');
                            });

                            // Add active class to the clicked item
                            clickedItem.classList.add('active');
                        }
                    });

                    // Hide completed projects list when navigating to a project-related page
                    const projectLinks = document.querySelectorAll("#recentProjectsList li a, #completedProjectsList li a");
                    projectLinks.forEach(link => {
                        link.addEventListener('click', function () {
                            completedProjectsList.style.display = 'none';
                        });
                    });

                    function fetchCompletedProjects() {
                        fetch("{{ route('getCompletedProjects') }}")
                                .then(response => response.json())
                                .then(data => {
                                    renderCompletedProjects(data);
                                })
                                .catch(error => {
                                    console.error('Error fetching completed projects:', error);
                                });
                    }

                    function renderCompletedProjects(projects) {
                        // Clear the existing list
                        completedProjectsList.innerHTML = '';

                        if (projects.length === 0) {
                            completedProjectsList.innerHTML = '<li style="font-size: 14px; justify-content: center;">No completed projects found.</li>';
                            return;
                        }

                        // Populate the list with completed projects
                        projects.forEach(project => {
                            const listItem = document.createElement('li');
                            listItem.innerHTML = `
                <img src="{{ asset('icon/project.png') }}" alt="Project" class="icon-css project-icon"/>
                <a href="/project/${project.id}/dashboard">${project.name}</a>
            `;
                            completedProjectsList.appendChild(listItem);
                        });
                    }
                });

                document.addEventListener("DOMContentLoaded", function () {
                    // Get all list items in the sidebar
                    const sidebarItems = document.querySelectorAll(".sidebar li");

                    sidebarItems.forEach(item => {
                        // Add a click event listener to each <li>
                        item.addEventListener("click", function (event) {
                            // Prevent default behavior if the click is on a nested element (e.g., an icon)
                            if (event.target.tagName !== "A") {
                                const link = item.querySelector("a");
                                if (link) {
                                    // Navigate to the link's href
                                    window.location.href = link.href;
                                }
                            }
                        });

                        // Highlight the active link based on the current URL
                        const link = item.querySelector("a");
                        if (link && link.href === window.location.href) {
                            item.classList.add("active");
                        }
                    });
                });
</script>


