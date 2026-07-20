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

/* ══════════════════════════════════════════════════════════
   USER MANAGEMENT MODULE
   View switching, dropdowns, panels, modals
   ══════════════════════════════════════════════════════════ */
(function () {
    const container = document.getElementById('umContainer');
    if (!container) return; // Not on user management page

    // Move modals and overlays to body to bypass layout transform limits
    ['umPanelOverlay', 'umDetailPanel', 'umEditOverlay', 'umDeleteOverlay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) document.body.appendChild(el);
    });

    // ── View Switching ──────────────────────────────────────
    const viewList = document.getElementById('umViewList');
    const viewCreate = document.getElementById('umViewCreate');
    const btnCreate = document.getElementById('umBtnCreateUser');
    const btnBackCreate = document.getElementById('umBtnBackFromCreate');
    const btnCancelCreate = document.getElementById('umCancelCreate');

    function switchView(targetView) {
        [viewList, viewCreate].forEach(function (v) {
            if (v) {
                v.classList.remove('um-view-active');
                v.style.animation = 'none';
            }
        });
        if (targetView) {
            // Force reflow for animation restart
            targetView.offsetHeight;
            targetView.style.animation = '';
            targetView.classList.add('um-view-active');
            targetView.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    if (btnCreate) {
        btnCreate.addEventListener('click', function () {
            switchView(viewCreate);
        });
    }

    [btnBackCreate, btnCancelCreate].forEach(function (btn) {
        if (btn) {
            btn.addEventListener('click', function () {
                switchView(viewList);
            });
        }
    });

    // ── Action Dropdown Menus ───────────────────────────────
    let activeDropdown = null;

    function closeAllDropdowns() {
        document.querySelectorAll('.um-dropdown-menu.um-dropdown-visible').forEach(function (menu) {
            menu.classList.remove('um-dropdown-visible');
        });
        document.querySelectorAll('.um-action-trigger.um-dropdown-open').forEach(function (btn) {
            btn.classList.remove('um-dropdown-open');
        });
        activeDropdown = null;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-um-dropdown]');
        if (trigger) {
            e.stopPropagation();
            var menu = trigger.nextElementSibling;
            if (menu && menu.classList.contains('um-dropdown-menu')) {
                var isOpen = menu.classList.contains('um-dropdown-visible');
                closeAllDropdowns();
                if (!isOpen) {
                    menu.classList.add('um-dropdown-visible');
                    trigger.classList.add('um-dropdown-open');
                    activeDropdown = menu;
                }
            }
            return;
        }
        // Click outside → close
        if (activeDropdown && !e.target.closest('.um-dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    // ── View User Detail Panel ──────────────────────────────
    var panelOverlay = document.getElementById('umPanelOverlay');
    var detailPanel = document.getElementById('umDetailPanel');
    var panelClose = document.getElementById('umPanelClose');
    var panelLoading = document.getElementById('umPanelLoading');
    var panelContent = document.getElementById('umPanelContent');

    function openPanel() {
        if (panelOverlay) panelOverlay.classList.add('um-panel-overlay-active');
        if (detailPanel) detailPanel.classList.add('um-panel-open');
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        if (panelOverlay) panelOverlay.classList.remove('um-panel-overlay-active');
        if (detailPanel) detailPanel.classList.remove('um-panel-open');
        document.body.style.overflow = '';
    }

    if (panelClose) panelClose.addEventListener('click', closePanel);
    if (panelOverlay) panelOverlay.addEventListener('click', closePanel);

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '—') return '—';
        try {
            var d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) +
                   ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } catch (err) {
            return dateStr;
        }
    }

    function getRoleClass(slug) {
        var map = { admin: 'um-role-admin', instructor: 'um-role-instructor', trainee: 'um-role-trainee' };
        return map[slug] || 'um-role-trainee';
    }

    function getStatusClass(status) {
        var map = { active: 'um-status-active', pending: 'um-status-pending', inactive: 'um-status-inactive', suspended: 'um-status-suspended' };
        return map[status] || 'um-status-inactive';
    }

    function getInitials(name) {
        var parts = (name || '').trim().split(/\s+/);
        var first = (parts[0] || '').charAt(0);
        var last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
        var ini = (first + last).toUpperCase();
        return ini.length < 2 ? (name || '').substring(0, 2).toUpperCase() : ini;
    }

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('[data-um-view]');
        if (!viewBtn) return;
        closeAllDropdowns();

        var userId = viewBtn.getAttribute('data-um-view');
        if (!userId) return;

        // Show loading
        if (panelLoading) panelLoading.style.display = '';
        if (panelContent) panelContent.style.display = 'none';
        openPanel();

        fetch('index.php?page=admin-user-detail&id=' + encodeURIComponent(userId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status !== 'success' || !data.user) {
                if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">User not found.</p>';
                if (panelLoading) panelLoading.style.display = 'none';
                if (panelContent) panelContent.style.display = '';
                return;
            }

            var u = data.user;
            var roleClass = getRoleClass(u.role_slug);
            var statusClass = getStatusClass(u.status);
            var initials = getInitials(u.name);

            var avatarHtml = u.profile_picture
                ? '<img src="storage/uploads/' + u.profile_picture + '" alt="" class="um-avatar-img" style="width:100%;height:100%;object-fit:cover;border-radius:50%">'
                : initials;

            var html = '';
            html += '<div class="um-detail-avatar ' + roleClass + '">' + avatarHtml + '</div>';
            html += '<h4 class="um-detail-name">' + escHtml(u.name) + '</h4>';
            html += '<p class="um-detail-email">' + escHtml(u.email) + '</p>';
            html += '<div class="um-detail-badges">';
            html += '  <span class="um-badge ' + roleClass + '">' + escHtml(u.role_name) + '</span>';
            html += '  <span class="um-status-pill ' + statusClass + '"><span class="um-status-dot"></span>' + escHtml(capitalize(u.status)) + '</span>';
            html += '</div>';

            html += '<div class="um-detail-section">';
            html += '  <h5 class="um-detail-section-title">Contact Information</h5>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Email</span><span class="um-detail-value">' + escHtml(u.email) + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Phone</span><span class="um-detail-value">' + escHtml(u.phone || '—') + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Address</span><span class="um-detail-value">' + escHtml(u.address || '—') + '</span></div>';
            html += '</div>';

            html += '<div class="um-detail-section">';
            html += '  <h5 class="um-detail-section-title">Account Details</h5>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">User ID</span><span class="um-detail-value">#' + u.id + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Role</span><span class="um-detail-value">' + escHtml(u.role_name) + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Status</span><span class="um-detail-value">' + escHtml(capitalize(u.status)) + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Created</span><span class="um-detail-value">' + formatDate(u.created_at) + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Last Login</span><span class="um-detail-value">' + formatDate(u.last_login) + '</span></div>';
            html += '  <div class="um-detail-row"><span class="um-detail-key">Last Active</span><span class="um-detail-value">' + formatDate(u.last_active) + '</span></div>';
            html += '</div>';

            html += '<div class="um-panel-actions">';
            html += '  <button class="um-btn um-btn-secondary" type="button" data-um-panel-edit="' + u.id + '">Edit User</button>';
            html += '  <button class="um-btn um-btn-danger" type="button" data-um-panel-delete="' + u.id + '" data-user-name="' + escHtml(u.name) + '">Delete</button>';
            html += '</div>';

            if (panelContent) panelContent.innerHTML = html;
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        })
        .catch(function () {
            if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">Failed to load user details.</p>';
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        });
    });

    // Panel action buttons (edit/delete from panel)
    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-um-panel-edit]');
        if (editBtn) {
            var userId = editBtn.getAttribute('data-um-panel-edit');
            // Find the row's edit button and click it
            var rowEditBtn = document.querySelector('[data-um-edit="' + userId + '"]');
            if (rowEditBtn) {
                closePanel();
                setTimeout(function () { rowEditBtn.click(); }, 350);
            }
            return;
        }
        var delBtn = e.target.closest('[data-um-panel-delete]');
        if (delBtn) {
            var userId = delBtn.getAttribute('data-um-panel-delete');
            var userName = delBtn.getAttribute('data-user-name');
            closePanel();
            setTimeout(function () { openDeleteModal(userId, userName); }, 350);
        }
    });

    // ── Edit User Modal ─────────────────────────────────────
    var editOverlay = document.getElementById('umEditOverlay');
    var editClose = document.getElementById('umEditClose');
    var editCancel = document.getElementById('umEditCancel');

    function openEditModal(userData) {
        if (!editOverlay) return;
        document.getElementById('editUserId').value = userData.id || 0;
        document.getElementById('editName').value = userData.name || '';
        document.getElementById('editEmail').value = userData.email || '';
        document.getElementById('editPhone').value = userData.phone || '';
        document.getElementById('editAddress').value = userData.address || '';
        document.getElementById('editRole').value = userData.role_slug || 'trainee';
        document.getElementById('editStatus').value = userData.status || 'active';
        document.getElementById('editPassword').value = '';
        editOverlay.classList.add('um-modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        if (editOverlay) editOverlay.classList.remove('um-modal-active');
        document.body.style.overflow = '';
    }

    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    if (editOverlay) {
        editOverlay.addEventListener('click', function (e) {
            if (e.target === editOverlay) closeEditModal();
        });
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-um-edit]');
        if (!editBtn) return;
        closeAllDropdowns();
        try {
            var userData = JSON.parse(editBtn.getAttribute('data-user'));
            openEditModal(userData);
        } catch (err) {
            console.error('Failed to parse user data', err);
        }
    });

    // ── Delete Confirmation Modal ───────────────────────────
    var deleteOverlay = document.getElementById('umDeleteOverlay');
    var deleteCancel = document.getElementById('umDeleteCancel');
    var deleteNameEl = document.getElementById('umDeleteName');
    var deleteIdEl = document.getElementById('deleteUserId');

    function openDeleteModal(userId, userName) {
        if (!deleteOverlay) return;
        if (deleteNameEl) deleteNameEl.textContent = userName || 'this user';
        if (deleteIdEl) deleteIdEl.value = userId || 0;
        deleteOverlay.classList.add('um-modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        if (deleteOverlay) deleteOverlay.classList.remove('um-modal-active');
        document.body.style.overflow = '';
    }

    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
    if (deleteOverlay) {
        deleteOverlay.addEventListener('click', function (e) {
            if (e.target === deleteOverlay) closeDeleteModal();
        });
    }

    document.addEventListener('click', function (e) {
        var delBtn = e.target.closest('[data-um-delete]');
        if (!delBtn) return;
        closeAllDropdowns();
        var userId = delBtn.getAttribute('data-um-delete');
        var userName = delBtn.getAttribute('data-user-name');
        openDeleteModal(userId, userName);
    });

    // ── Escape key closes everything ────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePanel();
            closeEditModal();
            closeDeleteModal();
            closeAllDropdowns();
        }
    });

    // ── Helper functions ────────────────────────────────────
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }
})();

/* ══════════════════════════════════════════════════════════
   COURSE MANAGEMENT MODULE
   View switching, dropdowns, panels, modals
   ══════════════════════════════════════════════════════════ */
(function () {
    const container = document.getElementById('cmContainer');
    if (!container) return; // Not on course management page

    // Move modals and overlays to body to bypass layout transform limits
    ['cmPanelOverlay', 'cmDetailPanel', 'cmEditOverlay', 'cmDeleteOverlay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) document.body.appendChild(el);
    });

    // ── View Switching ──────────────────────────────────────
    const viewList = document.getElementById('cmViewList');
    const viewCreate = document.getElementById('cmViewCreate');
    const btnCreate = document.getElementById('cmBtnCreateCourse');
    const btnBackCreate = document.getElementById('cmBtnBackFromCreate');
    const btnCancelCreate = document.getElementById('cmCancelCreate');

    function switchView(targetView) {
        [viewList, viewCreate].forEach(function (v) {
            if (v) {
                v.classList.remove('cm-view-active');
                v.style.animation = 'none';
            }
        });
        if (targetView) {
            targetView.offsetHeight; // Force reflow
            targetView.style.animation = '';
            targetView.classList.add('cm-view-active');
            targetView.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    if (btnCreate) {
        btnCreate.addEventListener('click', function () {
            switchView(viewCreate);
        });
    }

    [btnBackCreate, btnCancelCreate].forEach(function (btn) {
        if (btn) {
            btn.addEventListener('click', function () {
                switchView(viewList);
            });
        }
    });

    // ── Action Dropdown Menus ───────────────────────────────
    let activeDropdown = null;

    function closeAllDropdowns() {
        document.querySelectorAll('.cm-dropdown-menu.cm-dropdown-visible').forEach(function (menu) {
            menu.classList.remove('cm-dropdown-visible');
        });
        document.querySelectorAll('.cm-card-trigger.cm-dropdown-open').forEach(function (btn) {
            btn.classList.remove('cm-dropdown-open');
        });
        activeDropdown = null;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-cm-dropdown]');
        if (trigger) {
            e.stopPropagation();
            var menu = trigger.nextElementSibling;
            if (menu && menu.classList.contains('cm-dropdown-menu')) {
                var isOpen = menu.classList.contains('cm-dropdown-visible');
                closeAllDropdowns();
                if (!isOpen) {
                    menu.classList.add('cm-dropdown-visible');
                    trigger.classList.add('cm-dropdown-open');
                    activeDropdown = menu;
                }
            }
            return;
        }
        if (activeDropdown && !e.target.closest('.cm-dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    // ── View Course Detail Panel ────────────────────────────
    var panelOverlay = document.getElementById('cmPanelOverlay');
    var detailPanel = document.getElementById('cmDetailPanel');
    var panelClose = document.getElementById('cmPanelClose');
    var panelLoading = document.getElementById('cmPanelLoading');
    var panelContent = document.getElementById('cmPanelContent');

    function openPanel() {
        if (panelOverlay) panelOverlay.classList.add('cm-panel-overlay-active');
        if (detailPanel) detailPanel.classList.add('cm-panel-open');
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        if (panelOverlay) panelOverlay.classList.remove('cm-panel-overlay-active');
        if (detailPanel) detailPanel.classList.remove('cm-panel-open');
        document.body.style.overflow = '';
    }

    if (panelClose) panelClose.addEventListener('click', closePanel);
    if (panelOverlay) panelOverlay.addEventListener('click', closePanel);

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '—') return '—';
        try {
            var d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (err) {
            return dateStr;
        }
    }

    function getStatusClass(status) {
        var map = { active: 'cm-status-active', published: 'cm-status-active', draft: 'cm-status-draft', completed: 'cm-status-completed', archived: 'cm-status-archived' };
        return map[status] || 'cm-status-draft';
    }

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('[data-cm-view]');
        if (!viewBtn) return;
        closeAllDropdowns();

        var courseId = viewBtn.getAttribute('data-cm-view');
        if (!courseId) return;

        if (panelLoading) panelLoading.style.display = '';
        if (panelContent) panelContent.style.display = 'none';
        openPanel();

        fetch('index.php?page=admin-course-detail&id=' + encodeURIComponent(courseId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status !== 'success' || !data.course) {
                if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">Course not found.</p>';
                if (panelLoading) panelLoading.style.display = 'none';
                if (panelContent) panelContent.style.display = '';
                return;
            }

            var c = data.course;
            var statusClass = getStatusClass(c.status);
            var image = c.thumbnail_image ? 'storage/uploads/' + c.thumbnail_image : 'public/assets/img/course-placeholder.svg';

            var html = '';
            html += '<img class="cm-detail-banner-img" src="' + escHtml(image) + '" alt="">';
            html += '<h4 class="cm-detail-title">' + escHtml(c.title) + '</h4>';
            html += '<div class="cm-detail-badges">';
            html += '  <span class="cm-card-badge cm-academy-general" style="position:static;">' + escHtml(c.category) + '</span>';
            if (c.academy_code) {
                var acadClass = c.academy_code === 'ADGEA' ? 'cm-academy-adgea' : (c.academy_code === 'IESGA' ? 'cm-academy-iesga' : 'cm-academy-general');
                html += '  <span class="cm-card-badge ' + acadClass + '" style="position:static;">' + escHtml(c.academy_code) + '</span>';
            }
            html += '  <span class="cm-status-pill ' + statusClass + '"><span class="cm-status-dot"></span>' + escHtml(capitalize(c.status)) + '</span>';
            html += '</div>';

            html += '<div class="cm-detail-section">';
            html += '  <h5 class="cm-detail-section-title">Course Description</h5>';
            html += '  <p class="cm-detail-desc-text">' + escHtml(c.description || 'No description provided.') + '</p>';
            html += '</div>';

            html += '<div class="cm-detail-section">';
            html += '  <h5 class="cm-detail-section-title">Class Information</h5>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Instructor</span><span class="cm-detail-value">' + escHtml(c.instructor_name || 'To be assigned') + '</span></div>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Duration</span><span class="cm-detail-value">' + formatDate(c.start_date) + ' to ' + formatDate(c.end_date) + '</span></div>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Enrolled Students</span><span class="cm-detail-value">' + (c.participant_count || 0) + ' / ' + (c.max_participants || c.capacity || 25) + '</span></div>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Capacity</span><span class="cm-detail-value">' + (c.capacity || 25) + '</span></div>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Course Fee</span><span class="cm-detail-value">RM ' + parseFloat(c.fee || 0).toFixed(2) + '</span></div>';
            html += '</div>';

            html += '<div class="cm-detail-section">';
            html += '  <h5 class="cm-detail-section-title">System Logs</h5>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Course ID</span><span class="cm-detail-value">#' + c.id + '</span></div>';
            html += '  <div class="cm-detail-row"><span class="cm-detail-key">Display Status</span><span class="cm-detail-value">' + escHtml(capitalize(c.course_status || c.status)) + '</span></div>';
            html += '</div>';

            html += '<div class="cm-panel-actions">';
            html += '  <button class="cm-btn cm-btn-secondary" type="button" data-cm-panel-edit="' + c.id + '">Edit Details</button>';
            html += '  <button class="cm-btn cm-btn-danger" type="button" data-cm-panel-delete="' + c.id + '" data-course-title="' + escHtml(c.title) + '">Delete</button>';
            html += '</div>';

            if (panelContent) panelContent.innerHTML = html;
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        })
        .catch(function () {
            if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">Failed to load course details.</p>';
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        });
    });

    // Panel action triggers
    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-cm-panel-edit]');
        if (editBtn) {
            var courseId = editBtn.getAttribute('data-cm-panel-edit');
            var cardEditBtn = document.querySelector('[data-course-id="' + courseId + '"] [data-cm-edit]');
            if (cardEditBtn) {
                closePanel();
                setTimeout(function () { cardEditBtn.click(); }, 350);
            }
            return;
        }
        var delBtn = e.target.closest('[data-cm-panel-delete]');
        if (delBtn) {
            var courseId = delBtn.getAttribute('data-cm-panel-delete');
            var courseTitle = delBtn.getAttribute('data-course-title');
            closePanel();
            setTimeout(function () { openDeleteModal(courseId, courseTitle); }, 350);
        }
    });

    // ── Edit Course Modal ───────────────────────────────────
    var editOverlay = document.getElementById('cmEditOverlay');
    var editClose = document.getElementById('cmEditClose');
    var editCancel = document.getElementById('cmEditCancel');

    function openEditModal(c) {
        if (!editOverlay) return;
        document.getElementById('editCourseId').value = c.id || 0;
        document.getElementById('editExistingThumbnail').value = c.thumbnail_image || '';
        document.getElementById('editTitle').value = c.title || '';
        document.getElementById('editCategory').value = c.category || '';
        document.getElementById('editAcademy').value = c.academy_id || '';
        document.getElementById('editDescription').value = c.description || '';
        document.getElementById('editStartDate').value = c.start_date || '';
        document.getElementById('editEndDate').value = c.end_date || '';
        document.getElementById('editCapacity').value = c.capacity || 25;
        document.getElementById('editMaxPart').value = c.max_participants || 25;
        document.getElementById('editFee').value = parseFloat(c.fee || 0).toFixed(2);
        document.getElementById('editInstructor').value = c.instructor_id || '';
        document.getElementById('editStatus').value = c.status || 'draft';
        document.getElementById('editCourseStatus').value = c.course_status || 'draft';
        document.getElementById('editThumbnail').value = '';

        editOverlay.classList.add('cm-modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        if (editOverlay) editOverlay.classList.remove('cm-modal-active');
        document.body.style.overflow = '';
    }

    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    if (editOverlay) {
        editOverlay.addEventListener('click', function (e) {
            if (e.target === editOverlay) closeEditModal();
        });
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-cm-edit]');
        if (!editBtn) return;
        closeAllDropdowns();
        try {
            var cData = JSON.parse(editBtn.getAttribute('data-course'));
            openEditModal(cData);
        } catch (err) {
            console.error('Failed to parse course data', err);
        }
    });

    // ── Delete Confirmation Modal ───────────────────────────
    var deleteOverlay = document.getElementById('cmDeleteOverlay');
    var deleteCancel = document.getElementById('cmDeleteCancel');
    var deleteTitleEl = document.getElementById('cmDeleteTitle');
    var deleteIdEl = document.getElementById('deleteCourseId');

    function openDeleteModal(courseId, courseTitle) {
        if (!deleteOverlay) return;
        if (deleteTitleEl) deleteTitleEl.textContent = courseTitle || 'this course';
        if (deleteIdEl) deleteIdEl.value = courseId || 0;
        deleteOverlay.classList.add('cm-modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        if (deleteOverlay) deleteOverlay.classList.remove('cm-modal-active');
        document.body.style.overflow = '';
    }

    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
    if (deleteOverlay) {
        deleteOverlay.addEventListener('click', function (e) {
            if (e.target === deleteOverlay) closeDeleteModal();
        });
    }

    document.addEventListener('click', function (e) {
        var delBtn = e.target.closest('[data-cm-delete]');
        if (!delBtn) return;
        closeAllDropdowns();
        var courseId = delBtn.getAttribute('data-cm-delete');
        var courseTitle = delBtn.getAttribute('data-course-title');
        openDeleteModal(courseId, courseTitle);
    });

    // ── Escape closes everything ────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePanel();
            closeEditModal();
            closeDeleteModal();
            closeAllDropdowns();
        }
    });

    // ── Helpers ─────────────────────────────────────────────
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }
})();

/* ══════════════════════════════════════════════════════════
   ENROLMENT MANAGEMENT MODULE
   Dropdowns, panels, and status transition modals
   ══════════════════════════════════════════════════════════ */
(function () {
    const container = document.getElementById('emContainer');
    if (!container) return; // Not on enrolment management page

    // Move modals and overlays to body to bypass layout transform limits
    ['emPanelOverlay', 'emDetailPanel', 'emStatusOverlay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) document.body.appendChild(el);
    });

    // ── Action Dropdown Menus ───────────────────────────────
    let activeDropdown = null;

    function closeAllDropdowns() {
        document.querySelectorAll('.em-dropdown-menu.em-dropdown-visible').forEach(function (menu) {
            menu.classList.remove('em-dropdown-visible');
        });
        document.querySelectorAll('.em-action-trigger.em-dropdown-open').forEach(function (btn) {
            btn.classList.remove('em-dropdown-open');
        });
        activeDropdown = null;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-em-dropdown]');
        if (trigger) {
            e.stopPropagation();
            var menu = trigger.nextElementSibling;
            if (menu && menu.classList.contains('em-dropdown-menu')) {
                var isOpen = menu.classList.contains('em-dropdown-visible');
                closeAllDropdowns();
                if (!isOpen) {
                    menu.classList.add('em-dropdown-visible');
                    trigger.classList.add('em-dropdown-open');
                    activeDropdown = menu;
                }
            }
            return;
        }
        if (activeDropdown && !e.target.closest('.em-dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    // ── View Enrolment Detail Panel ─────────────────────────
    var panelOverlay = document.getElementById('emPanelOverlay');
    var detailPanel = document.getElementById('emDetailPanel');
    var panelClose = document.getElementById('emPanelClose');
    var panelLoading = document.getElementById('emPanelLoading');
    var panelContent = document.getElementById('emPanelContent');

    function openPanel() {
        if (panelOverlay) panelOverlay.classList.add('em-panel-overlay-active');
        if (detailPanel) detailPanel.classList.add('cm-panel-open');
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        if (panelOverlay) panelOverlay.classList.remove('em-panel-overlay-active');
        if (detailPanel) detailPanel.classList.remove('cm-panel-open');
        document.body.style.overflow = '';
    }

    if (panelClose) panelClose.addEventListener('click', closePanel);
    if (panelOverlay) panelOverlay.addEventListener('click', closePanel);

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '—') return '—';
        try {
            var d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (err) {
            return dateStr;
        }
    }

    function getStatusClass(status) {
        var map = { active: 'em-status-active', completed: 'em-status-completed', pending: 'em-status-pending', rejected: 'em-status-rejected', withdrawn: 'em-status-withdrawn' };
        return map[status] || 'em-status-pending';
    }

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('[data-em-view]');
        if (!viewBtn) return;
        closeAllDropdowns();

        var enrolmentId = viewBtn.getAttribute('data-em-view');
        if (!enrolmentId) return;

        if (panelLoading) panelLoading.style.display = '';
        if (panelContent) panelContent.style.display = 'none';
        openPanel();

        fetch('index.php?page=admin-enrolment-detail&id=' + encodeURIComponent(enrolmentId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status !== 'success' || !data.enrolment) {
                if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">Registration details not found.</p>';
                if (panelLoading) panelLoading.style.display = 'none';
                if (panelContent) panelContent.style.display = '';
                return;
            }

            var r = data.enrolment;
            var statusClass = getStatusClass(r.status);
            var initials = '';
            var words = (r.trainee_name || '').split(' ');
            words.forEach(function (w) {
                if (w.length > 0) initials += w[0].toUpperCase();
            });
            initials = initials.substring(0, 2);

            var max = parseInt(r.course_max || r.course_capacity || 25);
            var occ = parseInt(r.course_occupancy || 0);
            var pct = max > 0 ? Math.min(100, Math.round((occ / max) * 100)) : 0;
            var barColor = 'cm-bg-success';
            if (pct >= 90) barColor = 'cm-bg-danger';
            else if (pct >= 70) barColor = 'cm-bg-warning';

            var html = '';
            html += '<div class="em-detail-header-card">';
            html += '  <div class="em-detail-avatar" style="background: linear-gradient(135deg, var(--ims-primary), var(--ims-accent));">' + escHtml(initials) + '</div>';
            html += '  <h4 class="em-detail-name">' + escHtml(r.trainee_name) + '</h4>';
            html += '  <span class="em-detail-email">' + escHtml(r.trainee_email) + '</span>';
            html += '  <span class="em-status-pill ' + statusClass + '"><span class="em-status-dot"></span>' + escHtml(capitalize(r.status)) + '</span>';
            html += '</div>';

            html += '<div class="em-detail-section">';
            html += '  <h5 class="em-detail-section-title">Trainee Contact Info</h5>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Phone Number</span><span class="em-detail-value">' + escHtml(r.trainee_phone || '—') + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Address</span><span class="em-detail-value">' + escHtml(r.trainee_address || '—') + '</span></div>';
            html += '</div>';

            html += '<div class="em-detail-section">';
            html += '  <h5 class="em-detail-section-title">Requested Course</h5>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Course Title</span><span class="em-detail-value">' + escHtml(r.course_title) + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Category</span><span class="em-detail-value">' + escHtml(r.course_category || '—') + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Instructor</span><span class="em-detail-value">' + escHtml(r.instructor_name || 'To be assigned') + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Duration</span><span class="em-detail-value">' + formatDate(r.course_start) + ' to ' + formatDate(r.course_end) + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Course Fee</span><span class="em-detail-value">RM ' + parseFloat(r.course_fee || 0).toFixed(2) + '</span></div>';
            html += '</div>';

            html += '<div class="em-detail-section">';
            html += '  <h5 class="em-detail-section-title">Class Capacity & Occupancy</h5>';
            html += '  <div class="em-detail-occupancy-section">';
            html += '    <div class="em-occupancy-meta-info">';
            html += '      <span><strong>Current Enrollment:</strong> ' + occ + ' / ' + max + '</span>';
            if (pct >= 90) {
                html += '      <span class="cm-progress-alert">Full</span>';
            }
            html += '    </div>';
            html += '    <div class="em-occupancy-track">';
            html += '      <div class="em-occupancy-bar ' + barColor + '" style="width:' + pct + '%;"></div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="em-detail-section">';
            html += '  <h5 class="em-detail-section-title">System Logs</h5>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Registration ID</span><span class="em-detail-value">#' + r.id + '</span></div>';
            html += '  <div class="em-detail-row"><span class="em-detail-key">Requested On</span><span class="em-detail-value">' + formatDate(r.created_at) + '</span></div>';
            html += '</div>';

            // Action Buttons
            html += '<div class="em-panel-actions">';
            if (r.status === 'pending') {
                html += '  <button class="em-btn em-btn-success" type="button" data-em-panel-action="' + r.id + '" data-new-status="active" data-trainee="' + escHtml(r.trainee_name) + '">Approve</button>';
                html += '  <button class="em-btn em-btn-danger" type="button" data-em-panel-action="' + r.id + '" data-new-status="rejected" data-trainee="' + escHtml(r.trainee_name) + '">Reject</button>';
            } else if (r.status === 'active') {
                html += '  <button class="em-btn em-btn-primary" type="button" data-em-panel-action="' + r.id + '" data-new-status="completed" data-trainee="' + escHtml(r.trainee_name) + '">Mark Completed</button>';
                html += '  <button class="em-btn em-btn-danger" type="button" data-em-panel-action="' + r.id + '" data-new-status="withdrawn" data-trainee="' + escHtml(r.trainee_name) + '">Withdraw</button>';
            } else if (r.status === 'rejected') {
                html += '  <button class="em-btn em-btn-success" type="button" data-em-panel-action="' + r.id + '" data-new-status="active" data-trainee="' + escHtml(r.trainee_name) + '">Re-approve</button>';
            } else {
                html += '  <span class="em-cell-muted" style="text-align:center;width:100%;">No pending actions available</span>';
            }
            html += '</div>';

            if (panelContent) panelContent.innerHTML = html;
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        })
        .catch(function () {
            if (panelContent) panelContent.innerHTML = '<p style="text-align:center;color:var(--ims-danger)">Failed to load details.</p>';
            if (panelLoading) panelLoading.style.display = 'none';
            if (panelContent) panelContent.style.display = '';
        });
    });

    // Drawer button actions mapping
    document.addEventListener('click', function (e) {
        var actionBtn = e.target.closest('[data-em-panel-action]');
        if (actionBtn) {
            var id = actionBtn.getAttribute('data-em-panel-action');
            var status = actionBtn.getAttribute('data-new-status');
            var trainee = actionBtn.getAttribute('data-trainee');
            closePanel();
            setTimeout(function () { openStatusModal(id, status, trainee); }, 350);
        }
    });

    // ── Status Transition Modal ─────────────────────────────
    var statusOverlay = document.getElementById('emStatusOverlay');
    var statusCancel = document.getElementById('emStatusCancel');
    var statusEnrolmentIdInput = document.getElementById('statusEnrolmentId');
    var statusNewValueInput = document.getElementById('statusNewValue');
    
    var modalTitle = document.getElementById('emModalTitle');
    var modalBodyText = document.getElementById('emModalBodyText');
    var modalIconWrapper = document.getElementById('emModalIconWrapper');
    var modalSubmitBtn = document.getElementById('emStatusSubmitBtn');

    function openStatusModal(id, newStatus, traineeName) {
        if (!statusOverlay) return;
        
        statusEnrolmentIdInput.value = id || 0;
        statusNewValueInput.value = newStatus || '';
        
        // Reset classes
        modalIconWrapper.className = 'em-status-icon-wrapper';
        modalSubmitBtn.className = 'em-btn';
        
        var title = 'Update Enrolment';
        var actionText = 'Are you sure you want to update status?';
        var buttonText = 'Confirm Update';
        
        if (newStatus === 'active') {
            title = 'Approve Registration';
            actionText = 'Are you sure you want to approve course registration for <strong>' + escHtml(traineeName) + '</strong>?';
            buttonText = 'Approve Enrolment';
            modalIconWrapper.classList.add('em-status-icon-success');
            modalIconWrapper.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
            modalSubmitBtn.classList.add('em-btn-success');
        } else if (newStatus === 'rejected') {
            title = 'Reject Registration';
            actionText = 'Are you sure you want to reject registration request from <strong>' + escHtml(traineeName) + '</strong>?';
            buttonText = 'Reject Enrolment';
            modalIconWrapper.classList.add('em-status-icon-danger');
            modalIconWrapper.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/></svg>';
            modalSubmitBtn.classList.add('em-btn-danger');
        } else if (newStatus === 'completed') {
            title = 'Mark Course Completed';
            actionText = 'Mark enrolment for <strong>' + escHtml(traineeName) + '</strong> as completed?';
            buttonText = 'Mark Completed';
            modalIconWrapper.classList.add('em-status-icon-primary');
            modalIconWrapper.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>';
            modalSubmitBtn.classList.add('em-btn-primary');
        } else if (newStatus === 'withdrawn') {
            title = 'Withdraw Student';
            actionText = 'Withdraw <strong>' + escHtml(traineeName) + '</strong> from the active course?';
            buttonText = 'Withdraw Trainee';
            modalIconWrapper.classList.add('em-status-icon-danger');
            modalIconWrapper.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
            modalSubmitBtn.classList.add('em-btn-danger');
        }

        modalTitle.textContent = title;
        modalBodyText.innerHTML = actionText;
        modalSubmitBtn.innerHTML = buttonText;
        
        statusOverlay.classList.add('cm-modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeStatusModal() {
        if (statusOverlay) statusOverlay.classList.remove('cm-modal-active');
        document.body.style.overflow = '';
    }

    if (statusCancel) statusCancel.addEventListener('click', closeStatusModal);
    if (statusOverlay) {
        statusOverlay.addEventListener('click', function (e) {
            if (e.target === statusOverlay) closeStatusModal();
        });
    }

    document.addEventListener('click', function (e) {
        var statusBtn = e.target.closest('[data-em-status-change]');
        if (!statusBtn) return;
        closeAllDropdowns();
        var id = statusBtn.getAttribute('data-em-status-change');
        var status = statusBtn.getAttribute('data-new-status');
        var trainee = statusBtn.getAttribute('data-trainee');
        openStatusModal(id, status, trainee);
    });

    // ── Escape closes everything ────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePanel();
            closeStatusModal();
            closeAllDropdowns();
        }
    });

    // ── Helpers ─────────────────────────────────────────────
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }
})();


