function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Toggle the collapsed class on the sidebar
    sidebar.classList.toggle('collapsed');
    
    // Adjust the main content margin based on sidebar state
    if (sidebar.classList.contains('collapsed')) {
        mainContent.style.width = "100%";
    } else {
        mainContent.style.width = "calc(100% - 250px)";
    }
}
