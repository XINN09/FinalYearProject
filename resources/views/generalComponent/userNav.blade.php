<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/userNav.css') }}">
    <title>User Profile</title>
</head>

<body>
    <!-- Sidebar Structure -->
    <div class="sidebar" id="sidebar">
        <div id="ProfileHeader">
            <img src="{{ asset('icon/user.png') }}" alt="User Profile" class="user-icon" />
            <h2>Profile</h2>
        </div>
        <ul id="NavigationMenu">
            <li><a href="{{ route('userProfile') }}">Personal Information</a></li>
            <li><a href="{{ route('projectHistory') }}">Project History</a></li>
            @if($role !== 'Worker')
            <li>
                <a href="#" class="has-submenu">Warranty</a>
                <!-- Sub-Nav -->
                <ul id="sub-Nav">
                    <li><a href="{{ route('getWarrantyRecords') }}">Warranty Record</a></li>
                    <li><a href="{{ route('warrantyService') }}">Warranty Service Request</a></li>
                </ul>
            </li>
            @endif
        </ul>

        <div id="backOption">
            <a href="#" class="back-button" id="backBtn">Back</a>
        </div>


    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const menuLinks = document.querySelectorAll("#NavigationMenu > li > a");
            const subMenus = document.querySelectorAll("#sub-Nav");
            const currentUrl = window.location.href;
            const backBtn = document.getElementById("backBtn");

            // Define a default back URL (Home Page)
            let backUrl = "{{ route('home') }}";


            // Set the back button link
            backBtn.setAttribute("href", backUrl);

            // Handle submenu behavior
            menuLinks.forEach(link => {
                const parentLi = link.parentElement;
                const subNav = parentLi.querySelector("#sub-Nav");

                if (subNav) {
                    const subNavLinks = subNav.querySelectorAll("a");
                    let hasActiveSubNav = false;

                    subNavLinks.forEach(subLink => {
                        if (subLink.href === currentUrl) {
                            hasActiveSubNav = true;
                            link.classList.add("parent-active");
                            subNav.style.display = "block";
                            subLink.classList.add("active");
                        }
                    });

                    if (!hasActiveSubNav) {
                        link.addEventListener("click", function (e) {
                            e.preventDefault();
                            window.location.href = subNavLinks[0].href;
                        });
                    }
                } else {
                    if (link.href === currentUrl) {
                        link.classList.add("active");
                    }

                    link.addEventListener("click", function () {
                        subMenus.forEach(menu => (menu.style.display = "none"));
                        menuLinks.forEach(link => link.classList.remove("parent-active"));
                    });
                }
            });
        });


    </script>
</body>
