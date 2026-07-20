/* ══════════════════════════════════════════════════════════
   CENTEXS ITOP IMS — App JavaScript v2.0
   Sidebar navigation + badge polling + scroll effects
   ══════════════════════════════════════════════════════════ */

/* ── Sidebar Toggle Logic ──────────────────────────────── */
(function () {
    const sidenav = document.getElementById('sidenav');
    const overlay = document.getElementById('sidenavOverlay');
    const toggleBtn = document.getElementById('sidenavToggle');
    const closeBtn = document.getElementById('sidenavCloseBtn');

    if (!sidenav) return; // Public page — no sidenav

    function openSidenav() {
        sidenav.classList.add('open');
        if (overlay) {
            overlay.classList.add('active');
            overlay.style.display = 'block';
            // Force reflow for animation
            overlay.offsetHeight;
            overlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeSidenav() {
        sidenav.classList.remove('open');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => {
                if (!overlay.classList.contains('active')) {
                    overlay.style.display = 'none';
                }
            }, 300);
        }
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (sidenav.classList.contains('open')) {
                closeSidenav();
            } else {
                openSidenav();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidenav);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidenav);
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidenav.classList.contains('open')) {
            closeSidenav();
        }
    });

    // Close sidenav on resize to desktop
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        const width = window.innerWidth;
        if (width >= 992 && lastWidth < 992) {
            closeSidenav();
        }
        lastWidth = width;
    });
})();

/* ── Legacy Chart Canvas (backward compat) ─────────────── */
(function () {
    const charts = document.querySelectorAll('#adminChart, .miniChart');
    if (!charts.length) return;

    charts.forEach((chart) => {
        const values = JSON.parse(chart.dataset.values || '[]').map(Number);
        const labels = JSON.parse(chart.dataset.labels || '[]');
        const ctx = chart.getContext('2d');
        const ratio = window.devicePixelRatio || 1;
        const width = chart.clientWidth || 700;
        const height = 300;
        chart.width = width * ratio;
        chart.height = height * ratio;
        ctx.scale(ratio, ratio);

        const max = Math.max(1, ...values);
        const gap = 16;
        const barWidth = Math.max(20, (width - gap * (values.length + 1)) / Math.max(1, values.length));

        ctx.clearRect(0, 0, width, height);
        ctx.font = '12px system-ui, sans-serif';
        ctx.fillStyle = '#182230';
        values.forEach((value, index) => {
            const x = gap + index * (barWidth + gap);
            const barHeight = (value / max) * 200;
            const y = 230 - barHeight;
            ctx.fillStyle = index % 2 ? '#18a999' : '#1256a3';
            ctx.fillRect(x, y, barWidth, barHeight);
            ctx.fillStyle = '#182230';
            ctx.fillText(String(value), x, y - 8);
            const words = String(labels[index] || '').split(' ');
            ctx.fillText(words.slice(0, 2).join(' '), x, 255);
            if (words.length > 2) ctx.fillText(words.slice(2, 4).join(' '), x, 272);
        });
    });
})();

/* ── Badge Polling ─────────────────────────────────────── */
(function () {
    const badgeUrl = 'index.php?page=badge-counts';
    const refreshBadges = () => {
        const badges = document.querySelectorAll('[data-badge]');
        if (!badges.length) return;
        fetch(badgeUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (!data) return;
                badges.forEach((badge) => {
                    const value = Number(data[badge.dataset.badge] || 0);
                    badge.textContent = value;
                    badge.classList.toggle('d-none', value === 0);
                    // Also update topbar badges
                    if (value === 0) {
                        badge.style.display = 'none';
                    } else {
                        badge.style.display = '';
                    }
                });
            })
            .catch(() => {});
    };

    document.querySelectorAll('[data-ajax-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"], button:not([type])');
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Saving...';
                button.classList.add('btn-loading');
            }
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
                .then((response) => response.json())
                .then(() => {
                    refreshBadges();
                    if (form.closest('.message-thread') || form.action.includes('send-message')) {
                        window.location.reload();
                    } else {
                        form.closest('.notification-card')?.remove();
                    }
                })
                .catch(() => window.location.reload())
                .finally(() => {
                    if (button) {
                        button.disabled = false;
                        button.textContent = button.dataset.originalText || 'Save';
                        button.classList.remove('btn-loading');
                    }
                });
        });
    });

    const thread = document.querySelector('.message-thread[data-conversation-id]');
    if (thread && Number(thread.dataset.conversationId)) {
        setInterval(() => {
            fetch('index.php?page=messages-feed&conversation_id=' + encodeURIComponent(thread.dataset.conversationId), {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then((response) => response.ok ? response.json() : []).then(refreshBadges).catch(() => {});
        }, 15000);
    }

    refreshBadges();
    setInterval(refreshBadges, 30000);
})();

/* ── Range Slider Interactivity ────────────────────────── */
(function () {
    document.querySelectorAll('.range-slider[data-range-display]').forEach(function (slider) {
        var valueDisplay = document.createElement('span');
        valueDisplay.style.cssText = 'display:inline-block;min-width:2rem;text-align:center;font-weight:700;font-size:.85rem;color:#054d9e;margin-top:.25rem';
        valueDisplay.textContent = parseFloat(slider.value).toFixed(1);
        slider.parentNode.appendChild(valueDisplay);

        function updateSlider() {
            var val = parseFloat(slider.value);
            valueDisplay.textContent = val.toFixed(1);
            var pct = ((val - parseFloat(slider.min)) / (parseFloat(slider.max) - parseFloat(slider.min))) * 100;
            slider.style.background = 'linear-gradient(90deg, #054d9e 0%, #054d9e ' + pct + '%, #e2e8f0 ' + pct + '%, #e2e8f0 100%)';

            if (slider.name === 'instructor_rating') {
                var hidden = slider.parentNode.querySelector('input[name="instructor_rating_value"]');
                if (hidden) hidden.value = Math.round(val);
                slider.value = val;
            }
        }

        slider.addEventListener('input', updateSlider);
        updateSlider();
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var instrSlider = form.querySelector('input[name="instructor_rating"]');
            if (instrSlider && instrSlider.type === 'range') {
                instrSlider.value = Math.round(parseFloat(instrSlider.value));
            }

            var extras = form.querySelectorAll('[data-extra]');
            if (extras.length) {
                var commentsField = form.querySelector('[name="comments"]');
                if (commentsField) {
                    var parts = [];
                    extras.forEach(function (el) {
                        parts.push(el.dataset.extra + ': ' + parseFloat(el.value).toFixed(1) + '/5');
                    });
                    var existing = commentsField.value.trim();
                    commentsField.value = (existing ? existing + '\n' : '') + '[Ratings] ' + parts.join(', ');
                }
            }
        });
    });
})();

/* ── Motion / Scroll Effects ───────────────────────────── */
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.classList.add('motion-ready');
        return;
    }

    const updateScrollEffects = () => {
        const scrollTop = window.scrollY || window.pageYOffset || 0;
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.toggle('is-scrolled', scrollTop > 14);
        }
    };

    const bindScrollEffects = () => {
        let ticking = false;
        const onScroll = () => {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(() => {
                updateScrollEffects();
                ticking = false;
            });
        };
        updateScrollEffects();
        window.addEventListener('scroll', onScroll, {passive: true});
        window.addEventListener('resize', onScroll, {passive: true});
    };

    const markVisible = () => {
        const rootTargets = document.querySelectorAll(
            'main > *, .main-content-inner > *, .hero-band > *, .hero-band .container > *, .site-footer > .container > *'
        );
        const componentTargets = document.querySelectorAll(
            '.card, .panel, .info-tile, .overview-panel, .overview-stat-card, .academy-card, .course-card, .announcement-card, .story-card, .intake-card, .lms-card, .table-responsive, .list-group, .alert, .conversation-item, .message-bubble, .notification-card, .auth-card, .chart-panel'
        );
        const targets = Array.from(new Set([...rootTargets, ...componentTargets])).filter((element) => {
            if (!(element instanceof HTMLElement)) return false;
            if (element.tagName === 'SCRIPT' || element.tagName === 'STYLE') return false;
            if (element.closest('[data-no-motion]')) return false;
            if (element.classList.contains('container') && element.querySelector('.page-layout')) return false;
            const rect = element.getBoundingClientRect();
            return rect.width > 0 && rect.height > 0;
        });

        if (!targets.length) {
            document.body.classList.add('motion-ready');
            return;
        }

        targets.forEach((element, index) => {
            if (element.classList.contains('reveal-on-scroll')) return;
            element.classList.add('reveal-on-scroll');
            element.style.setProperty('--reveal-delay', `${(index % 6) * 30}ms`);
        });

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.02,
                rootMargin: '0px 0px -8% 0px'
            }
        );

        targets.forEach((element) => observer.observe(element));
        bindScrollEffects();
        document.body.classList.add('motion-ready');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markVisible, {once: true});
    } else {
        markVisible();
    }
})();
