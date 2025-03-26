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
        const isDisplayed = dropdown.style.display === 'block';
        dropdown.style.display = isDisplayed ? 'none' : 'block';
    });
});