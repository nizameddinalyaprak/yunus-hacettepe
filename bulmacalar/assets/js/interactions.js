document.addEventListener('DOMContentLoaded', () => {
    // 1. Reveal Solution Logic
    const revealBtn = document.getElementById('reveal-btn');
    const solutionSection = document.getElementById('solution-section');
    
    if (revealBtn && solutionSection) {
        revealBtn.addEventListener('click', () => {
            // Show the solution
            solutionSection.classList.add('visible');
            
            // Transform the button into a "Scroll down" indicator or hide it
            revealBtn.style.opacity = '0';
            setTimeout(() => {
                revealBtn.style.display = 'none';
            }, 300);

            // Smoothly scroll down so the solution is perfectly in view
            setTimeout(() => {
                solutionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        });
    }

    // 2. Hub Page Filtering Logic
    const filterBtns = document.querySelectorAll('.filter-btn');
    const problemCards = document.querySelectorAll('.problem-card');

    if (filterBtns.length > 0 && problemCards.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Manage active states on buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.getAttribute('data-filter');

                problemCards.forEach(card => {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'flex';
                        // Tiny delay to allow display flex to apply before transitioning opacity
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0) scale(1)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px) scale(0.95)';
                        // Wait for transition to finish before hiding from DOM flow
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }
});
