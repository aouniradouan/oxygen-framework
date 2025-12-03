// Animation Utilities
// Inspired by Framer Motion for smooth, beautiful animations

/**
 * Fade In Animation
 */
export function fadeIn(element, duration = 500) {
    element.style.opacity = '0';
    element.style.transition = `opacity ${duration}ms ease-in-out`;

    requestAnimationFrame(() => {
        element.style.opacity = '1';
    });
}

/**
 * Slide Up Animation
 */
export function slideUp(element, duration = 500) {
    element.style.transform = 'translateY(20px)';
    element.style.opacity = '0';
    element.style.transition = `all ${duration}ms ease-out`;

    requestAnimationFrame(() => {
        element.style.transform = 'translateY(0)';
        element.style.opacity = '1';
    });
}

/**
 * Scale In Animation
 */
export function scaleIn(element, duration = 300) {
    element.style.transform = 'scale(0.9)';
    element.style.opacity = '0';
    element.style.transition = `all ${duration}ms ease-out`;

    requestAnimationFrame(() => {
        element.style.transform = 'scale(1)';
        element.style.opacity = '1';
    });
}

/**
 * Stagger Animation - Animate children with delay
 */
export function staggerChildren(parent, animationFn, staggerDelay = 100) {
    const children = Array.from(parent.children);

    children.forEach((child, index) => {
        setTimeout(() => {
            animationFn(child);
        }, index * staggerDelay);
    });
}

/**
 * Page Transition
 */
export function pageTransition() {
    const links = document.querySelectorAll('a:not([target="_blank"])');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');

            // Skip if it's a hash link or external
            if (!href || href.startsWith('#') || href.startsWith('http')) {
                return;
            }

            e.preventDefault();

            // Fade out
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 300ms ease-out';

            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });
    });
}

/**
 * Parallax Scroll Effect
 */
export function parallaxScroll(element, speed = 0.5) {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        element.style.transform = `translateY(${scrolled * speed}px)`;
    });
}

/**
 * Hover Scale Effect
 */
export function hoverScale(element, scale = 1.05) {
    element.addEventListener('mouseenter', () => {
        element.style.transform = `scale(${scale})`;
        element.style.transition = 'transform 200ms ease-out';
    });

    element.addEventListener('mouseleave', () => {
        element.style.transform = 'scale(1)';
    });
}

// Auto-initialize animations on DOM load
document.addEventListener('DOMContentLoaded', () => {
    // Animate elements with data-animate attribute
    const animatedElements = document.querySelectorAll('[data-animate]');

    animatedElements.forEach((element, index) => {
        const animationType = element.getAttribute('data-animate');
        const delay = parseInt(element.getAttribute('data-delay') || '0');

        setTimeout(() => {
            switch (animationType) {
                case 'fade-in':
                    fadeIn(element);
                    break;
                case 'slide-up':
                    slideUp(element);
                    break;
                case 'scale-in':
                    scaleIn(element);
                    break;
            }
        }, delay);
    });

    // Initialize page transitions
    pageTransition();
});

console.log('Animation utilities loaded');
