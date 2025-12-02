// Main JavaScript Entry Point
console.log('OxygenFramework - Loaded');

// Import animations module
import './animations.js';

// Add smooth page transitions
document.addEventListener('DOMContentLoaded', () => {
    // Add fade-in animation to body
    document.body.classList.add('animate-fade-in');

    // Form validation enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                // Re-enable after 2 seconds (in case of client-side validation failure)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }, 2000);
            }
        });
    });

    // Add hover effects to cards
    const cards = document.querySelectorAll('.card, .card-dark');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('scale-105');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('scale-105');
        });
    });
});

// Flash message auto-hide
setTimeout(() => {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(msg => {
        msg.classList.add('animate-fade-out');
        setTimeout(() => msg.remove(), 500);
    });
}, 5000);
