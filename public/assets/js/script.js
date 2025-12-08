// Animações de contagem da home
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter [data-count]');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const increment = target / 100;
        let current = 0;

        const updateCount = () => {
            if(current < target) {
                current += increment;
                counter.textContent = Math.ceil(current);
                requestAnimationFrame(updateCount);
            } else {
                counter.textContent = target;
            }
        }
        updateCount();
    });
});

// Controle de visibilidade
window.addEventListener('scroll', () => {
    const btn = document.getElementById('backToTop');
    btn.classList.toggle('show', window.scrollY > 300);
});

// Scroll suave
document.getElementById('backToTop').addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});


