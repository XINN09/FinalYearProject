document.querySelector('.new-task').addEventListener('click', () => {
    alert('New task button clicked!');
});
document.querySelector('.new-task').addEventListener('click', () => {
    // Show the new task row (just in case it's hidden)
    document.getElementById('new-task-row').style.display = 'table-row';
});
function toggleDropdown(event, type, taskId) {
    // Prevents closing when clicking inside the dropdown
    event.stopPropagation();
    // Check if it's the owner dropdown and handle it separately
    if (type === 'owner') {
        const ownerContainer = document.querySelector(`[data-task-id="${taskId}"]`);
        const dropdown = ownerContainer.querySelector('.owner-dropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        // Close other dropdowns
        const allOwnerDropdowns = document.querySelectorAll('.owner-dropdown');
        allOwnerDropdowns.forEach(d => {
            if (d !== dropdown) {
                d.style.display = 'none';
            }
        });
        // Close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            if (!ownerContainer.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        }, {once: true});
    } else {
        // Handle status and priority dropdowns (same as before)
        const dropdown = document.getElementById(`${type}-dropdown-${taskId}`);
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        // Close other dropdowns
        const allDropdowns = document.querySelectorAll(`.dropdown`);
        allDropdowns.forEach(d => {
            if (d !== dropdown) {
                d.style.display = 'none';
            }
        });
        // Close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target) && !event.target.closest('.status-container') && !event.target.closest('.priority-container')) {
                dropdown.style.display = 'none';
            }
        });
    }
}

function assignOwner(taskId, ownerInitials) {
    const ownerContainer = document.querySelector(`[data-task-id="${taskId}"]`);
    if (ownerContainer) {
        let ownerIcon = ownerContainer.querySelector('.owner-icon');
        if (!ownerIcon) {
            const placeholderIcon = ownerContainer.querySelector('.owner-placeholder-icon');
            if (placeholderIcon)
                placeholderIcon.remove();
            ownerIcon = document.createElement('div');
            ownerIcon.className = 'owner-icon';
            ownerIcon.onclick = () => toggleDropdown(ownerIcon);
            ownerContainer.prepend(ownerIcon);
        }
        ownerIcon.textContent = ownerInitials;
    }
    const dropdown = ownerContainer.querySelector('.owner-dropdown');
    if (dropdown)
        dropdown.style.display = 'none';
}



function updateStatus(taskId, status) {
    const statusSpan = document.getElementById(`status-${taskId}`);
    statusSpan.textContent = status; // Update the text content

    // Update the class for styling
    statusSpan.className = `status ${status.toLowerCase().replace(' ', '-')}`;
    // Special case for 'On Hold' status
    if (status === 'On Hold') {
        statusSpan.classList.add('on-hold');
    } else {
        statusSpan.classList.remove('on-hold');
    }

    document.getElementById(`status-dropdown-${taskId}`).style.display = 'none'; // Hide dropdown
}


function updatePriority(taskId, priority) {
    const prioritySpan = document.getElementById(`priority-${taskId}`);
    prioritySpan.textContent = priority; // Update the text content
    prioritySpan.className = `priority ${priority.toLowerCase()}`; // Update the class for styling
    document.getElementById(`priority-dropdown-${taskId}`).style.display = 'none'; // Hide dropdown
}

document.getElementById('task-name-input').addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && this.value.trim() !== '') {
        // Task Name entered, transform the row into a normal task row
        const taskRow = document.getElementById('new-task-row');
        const taskName = this.value.trim();
        // Hide the task name input
        this.classList.add('hidden');
        // Set the task name in the table and show other fields
        const taskCells = taskRow.getElementsByTagName('td');
        taskCells[1].innerHTML = taskName; // Task Name cell

        // Show the owner, status, and priority fields
        document.querySelectorAll('#new-task-row .hidden').forEach(element => {
            element.classList.remove('hidden');
        });
        // Set default values for Status, Priority, and Owner
        document.getElementById('status-new-task').textContent = 'Not Started'; // Default Status
        document.getElementById('priority-new-task').textContent = 'None'; // Default Priority

        // Optional: Set default start date to today's date
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start-date-input').value = today;
    }
});