document.addEventListener('DOMContentLoaded', function() {
    // Toggle mobile menu
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    if (userMenuToggle) {
        userMenuToggle.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                e.preventDefault();
                const userDropdown = this.closest('.user-dropdown');
                userDropdown.classList.toggle('active');
            }
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
            const userDropdown = document.querySelector('.user-dropdown');
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            
            if (userDropdown && userDropdown.classList.contains('active') && 
                e.target !== userMenuToggle && !userMenuToggle.contains(e.target) &&
                e.target !== userDropdown && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        }
    });
    
    // Flash messages
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    }
    
    // Handle URL parameters for success messages
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    const errorParam = urlParams.get('error');
    
    if (successParam) {
        const messageContainer = document.createElement('div');
        messageContainer.className = 'alert alert-success';
        
        if (successParam === 'borrowed') {
            messageContainer.textContent = 'Položka byla úspěšně vypůjčena.';
        } else if (successParam === 'returned') {
            messageContainer.textContent = 'Položka byla úspěšně vrácena.';
        } else if (successParam === 'added') {
            messageContainer.textContent = 'Položka byla úspěšně přidána.';
        } else if (successParam === 'updated') {
            messageContainer.textContent = 'Položka byla úspěšně aktualizována.';
        } else if (successParam === 'deleted') {
            messageContainer.textContent = 'Položka byla úspěšně smazána.';
        }
        
        const content = document.querySelector('.content');
        if (content) {
            content.insertBefore(messageContainer, content.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.style.opacity = '0';
                setTimeout(() => {
                    messageContainer.style.display = 'none';
                }, 500);
            }, 5000);
            
            // Clean URL by removing the success parameter
            const url = new URL(window.location);
            url.searchParams.delete('success');
            window.history.replaceState({}, '', url);
        }
    }
    
    // Handle error messages from URL parameters
    if (errorParam) {
        const messageContainer = document.createElement('div');
        messageContainer.className = 'alert alert-error';
        
        if (errorParam === 'self_delete') {
            messageContainer.textContent = 'Nemůžete smazat svůj vlastní účet.';
        } else if (errorParam === 'has_borrowed_items') {
            messageContainer.textContent = 'Uživatel má vypůjčené položky a nemůže být smazán.';
        } else if (errorParam === 'delete_failed') {
            messageContainer.textContent = 'Chyba při mazání uživatele.';
        } else if (errorParam === 'unauthorized_return') {
            messageContainer.textContent = 'Nemůžete vrátit položku, kterou jste si nevypůjčili.';
        } else if (errorParam === 'unauthorized') {
            messageContainer.textContent = 'Nemáte oprávnění k této akci.';
        } else if (errorParam === 'borrowed_item') {
            messageContainer.textContent = 'Vypůjčenou položku nelze smazat.';
        } else if (errorParam === 'delete_failed') {
            messageContainer.textContent = 'Chyba při mazání položky.';
        }
        
        const content = document.querySelector('.content');
        if (content) {
            content.insertBefore(messageContainer, content.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.style.opacity = '0';
                setTimeout(() => {
                    messageContainer.style.display = 'none';
                }, 500);
            }, 5000);
            
            // Clean URL by removing the error parameter
            const url = new URL(window.location);
            url.searchParams.delete('error');
            window.history.replaceState({}, '', url);
        }
    }
});