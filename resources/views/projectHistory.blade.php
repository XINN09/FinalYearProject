<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project History</title>
        <link rel="stylesheet" href="{{ asset('css/projectHistory.css') }}">
    </head>
    <body>
        <aside class="sidebar">
            @include('generalComponent.userNav')
        </aside>

        <div class="main-content">
            <h2 class="title">Project History</h2>

            <!-- Tabs for categories -->
            <div class="tabs">
                <button class="tab-link active" data-category="all">ALL</button>
                <button class="tab-link" data-category="present">Active</button>
                <button class="tab-link" data-category="past">Past</button>
            </div>

            <div class="project-cards" id="project-container">
                @if($activeProjects->isEmpty() && $pastProjects->isEmpty())
                <div class="no-projects-container">
                    <img src="{{ asset('images/project.png') }}" alt="No Projects" class="no-projects-image" />
                    <p class="no-projects-message">No projects available.</p>
                </div>
                @else
                <!-- Active Projects -->
                @foreach ($activeProjects as $project)
                <div class="project-card ongoing" data-category="present">
                    <a href="{{ route('project.dashboard', ['projectID' => $project->projectID]) }}">
                        <div class="top">
                            <img src="{{ asset('icon/history.png') }}" alt="History Icon" class="history-icon" />
                            <h3 class="project-title">{{ $project->projectName }}</h3>
                        </div>
                        <p class="project-description">{{ $project->projectDesc }}</p>
                        <p class="project-status"><strong>Status:</strong> {{ $project->projectStatus }}</p>
                        <p class="project-dates"><strong>Start Date:</strong> {{ $project->startDate }}</p>
                        <p class="project-contractor"><strong>Contractor:</strong> {{ $project->contractorName ?? 'N/A' }}</p>
                    </a>
                </div>
                @endforeach

                <!-- Past Projects -->
                @foreach ($pastProjects as $project)
                <div class="project-card completed" data-category="past">
                    <a href="{{ route('project.dashboard', ['projectID' => $project->projectID]) }}">
                        <div class="top">
                            <img src="{{ asset('icon/history.png') }}" alt="History Icon" class="history-icon" />
                            <h3 class="project-title">{{ $project->projectName }}</h3>
                        </div>
                        <p class="project-description">{{ $project->projectDesc }}</p>
                        <p class="project-status"><strong>Status:</strong> {{ $project->projectStatus }}</p>
                        <p class="project-dates"><strong>Completion Date:</strong> {{ $project->endDate }}</p>
                        <p class="project-contractor"><strong>Contractor:</strong> {{ $project->contractorName ?? 'N/A' }}</p>
                    </a>
                </div>
                @endforeach
                @endif
            </div>

        </div>

        <script>
            // Tab switching logic
            document.addEventListener("DOMContentLoaded", function () {
                const tabLinks = document.querySelectorAll(".tab-link");
                const projectCards = document.querySelectorAll(".project-card");

                tabLinks.forEach(tab => {
                    tab.addEventListener("click", function () {
                        // Remove active class from all tabs
                        tabLinks.forEach(t => t.classList.remove("active"));
                        this.classList.add("active");

                        const category = this.getAttribute("data-category");

                        // Show or hide projects based on category
                        projectCards.forEach(card => {
                            if (category === "all" || card.getAttribute("data-category") === category) {
                                card.style.display = "block";
                            } else {
                                card.style.display = "none";
                            }
                        });
                    });
                });
            });
        </script>
    </body>
</html>
