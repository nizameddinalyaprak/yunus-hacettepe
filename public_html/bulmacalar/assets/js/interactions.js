document.addEventListener('DOMContentLoaded', () => {
    // 1. Reveal Solution Logic (Updated with Blur)
    const revealBtn = document.getElementById('reveal-btn');
    const solutionSection = document.getElementById('solution-section');

    // Check if the solution is initially blurred
    if (solutionSection && !solutionSection.classList.contains('blurred')) {
        solutionSection.classList.add('blurred');
    }

    if (revealBtn && solutionSection) {
        revealBtn.addEventListener('click', () => {
            // Remove blur and show solution clearly
            solutionSection.classList.remove('blurred');
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

    // 1.5. Poll System Logic
    const pollContainer = document.getElementById('poll-container');
    if (pollContainer) {
        const problemId = pollContainer.getAttribute('data-problem-id');
        const btnSuccess = document.getElementById('btn-poll-success');
        const btnFail = document.getElementById('btn-poll-fail');
        const resultsDiv = document.getElementById('poll-results');
        const statText = document.getElementById('poll-stat');

        // Check if user already voted in this session
        const hasVoted = localStorage.getItem('voted_' + problemId);

        if (hasVoted) {
            showPollResults(problemId);
        } else {
            btnSuccess.addEventListener('click', () => submitVote(problemId, 'basarili', btnSuccess));
            btnFail.addEventListener('click', () => submitVote(problemId, 'basarisiz', btnFail));
        }

        function submitVote(id, voteType, clickedBtn) {
            clickedBtn.classList.add('selected');
            btnSuccess.style.pointerEvents = 'none';
            btnFail.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('action', 'vote');
            formData.append('problem_id', id);
            formData.append('vote', voteType);

            fetch('https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/public_html/api.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('voted_' + id, 'true');
                        displayStats(data);
                    }
                })
                .catch(error => console.error('Oylama hatası:', error));
        }

        function showPollResults(id) {
            btnSuccess.style.display = 'none';
            btnFail.style.display = 'none';
            document.querySelector('.poll-title').innerText = 'Sitenin Genel İstatistiği';

            fetch(`https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/public_html/api.php?action=get_stats&problem_id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.total_votes > 0) {
                        displayStats(data);
                    } else {
                        statText.innerHTML = "İlk çözen siz olun!";
                        resultsDiv.style.display = 'block';
                    }
                });
        }

        function displayStats(data) {
            btnSuccess.style.display = 'none';
            btnFail.style.display = 'none';
            document.querySelector('.poll-title').innerText = 'Sonuçlar';

            // "Sitenizi ziyaret edenlerin %X'i yanıldı" formatı
            statText.innerHTML = `Sitenizi ziyaret edenlerin <span class="stat-highlight">%${data.fail_rate}</span>'si bu soruda yanıldı!<br><span style="font-size:0.9rem; color:#64748b;">(Toplam ${data.total_votes} Oy)</span>`;
            resultsDiv.style.display = 'block';
        }
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
