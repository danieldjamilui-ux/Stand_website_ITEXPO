// Main JavaScript for Skincare Recommendation System

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Skin type selection in profile page
    const skinTypeCards = document.querySelectorAll('.skin-type-card');
    if (skinTypeCards.length > 0) {
        skinTypeCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                skinTypeCards.forEach(c => c.classList.remove('selected'));
                // Add selected class to clicked card
                this.classList.add('selected');
                // Set the hidden input value
                document.getElementById('skin_type_id').value = this.dataset.skinTypeId;
            });
        });
    }

    // Skin concern selection in profile page
    const skinConcernItems = document.querySelectorAll('.skin-concern-item');
    if (skinConcernItems.length > 0) {
        skinConcernItems.forEach(item => {
            item.addEventListener('click', function() {
                // Toggle selected class
                this.classList.toggle('selected');
                // Update the hidden input value
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            });
        });
    }

    // Product search functionality
    const searchInput = document.getElementById('product-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productBrand = card.querySelector('.product-brand').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productBrand.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }



    // Confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    if (confirmButtons.length > 0) {
        confirmButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    }
});