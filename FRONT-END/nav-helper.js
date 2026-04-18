/**
 * Navigation Helper & UX Enhancements
 * DakarTech-Hack
 */

(function () {
    // Add Back Button
    const backBtn = document.createElement('a');
    backBtn.href = '#';
    backBtn.className = 'floating-back-btn';
    backBtn.title = 'Retour en arrière';
    backBtn.innerHTML = '<span>‹</span>'; // Simple arrow

    backBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.history.back();
    });

    // Only show back button if there is history
    if (window.history.length > 1) {
        document.body.appendChild(backBtn);
    }

    // Smooth page transitions for internal links
    document.querySelectorAll('a').forEach(link => {
        if (link.hostname === window.location.hostname && !link.hash && !link.getAttribute('target')) {
            link.addEventListener('click', e => {
                e.preventDefault();
                const href = link.href;
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    window.location.href = href;
                }, 500);
            });
        }
    });

    // Mobile Menu Toggle Central Logic
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close menu when clicking outside or on a link
        document.addEventListener('click', (e) => {
            if (navMenu.classList.contains('active') && !navMenu.contains(e.target) && e.target !== navToggle) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });

        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
    }

    // Add CSS for the back button span if needed
    const style = document.createElement('style');
    style.textContent = `
        .floating-back-btn span {
            font-size: 2.5rem;
            line-height: 0.8;
            margin-top: -5px;
            font-family: 'Orbitron', sans-serif;
        }
    `;
    document.head.appendChild(style);
})();
