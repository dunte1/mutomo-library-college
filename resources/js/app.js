import Cropper from 'cropperjs';

/* ============================================================
 * THEME TOGGLE (DARK / LIGHT MODE)
 * Listens for the 'toggle-dark-mode' event dispatched by the
 * header / mobile-drawer toggle buttons. Toggles the 'dark'
 * class on <html> and persists the preference in localStorage.
 * On load, the inline <head> script applies the saved theme
 * synchronously to prevent flash-of-wrong-theme.
 * ============================================================ */
document.addEventListener('toggle-dark-mode', function () {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    if (isDark) {
        html.classList.remove('dark');
        html.classList.add('light');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.remove('light');
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
});

/* ============================================================
 * MOBILE TABLE → CARD: auto data-label injection
 * Reads <th> text from each .table-mobile-cards table and
 * stamps data-label onto the corresponding <td> cells so the
 * CSS ::before pseudo-element can render the column name.
 * Runs on initial load and after every Livewire page update.
 * ============================================================ */
function injectTableLabels() {
    document.querySelectorAll('.table-mobile-cards table').forEach((table) => {
        const headers = Array.from(table.querySelectorAll('thead th')).map(
            (th) => th.textContent.trim()
        );
        if (!headers.length) return;
        table.querySelectorAll('tbody tr').forEach((row) => {
            row.querySelectorAll('td').forEach((td, i) => {
                if (!td.hasAttribute('colspan') && headers[i]) {
                    td.setAttribute('data-label', headers[i]);
                }
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', injectTableLabels);
document.addEventListener('livewire:navigated', injectTableLabels);
document.addEventListener('livewire:update', injectTableLabels);

/* ============================================================
 * SWIPE-TO-REVEAL: horizontal swipe gesture on .swipe-item-wrapper
 * Swipe left to reveal action buttons, swipe right to dismiss.
 * ============================================================ */
function initSwipeItems() {
    document.querySelectorAll('.swipe-item-wrapper:not([data-swipe-init])').forEach((wrapper) => {
        wrapper.setAttribute('data-swipe-init', '1');
        const content = wrapper.querySelector('.swipe-item-content');
        const actions  = wrapper.querySelector('.swipe-item-actions');
        if (!content || !actions) return;

        let startX = 0, startY = 0, currentX = 0, dragging = false, revealed = false;
        const THRESHOLD = 60;
        const ACTION_WIDTH = actions.offsetWidth || 100;

        content.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            dragging = true;
        }, { passive: true });

        content.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            const dx = e.touches[0].clientX - startX;
            const dy = e.touches[0].clientY - startY;
            if (Math.abs(dy) > Math.abs(dx)) { dragging = false; return; }
            currentX = revealed ? Math.min(0, dx) : Math.min(0, Math.max(-ACTION_WIDTH, dx));
            content.style.transform = `translateX(${currentX}px)`;
            content.style.transition = 'none';
        }, { passive: true });

        content.addEventListener('touchend', () => {
            if (!dragging) return;
            dragging = false;
            content.style.transition = '';
            if (!revealed && currentX < -THRESHOLD) {
                // reveal
                content.style.transform = `translateX(-${ACTION_WIDTH}px)`;
                actions.classList.add('revealed');
                revealed = true;
            } else if (revealed && currentX > -(ACTION_WIDTH - THRESHOLD)) {
                // hide
                content.style.transform = '';
                actions.classList.remove('revealed');
                revealed = false;
            } else {
                // snap back
                content.style.transform = revealed ? `translateX(-${ACTION_WIDTH}px)` : '';
            }
            currentX = 0;
        }, { passive: true });

        // Tap outside to close
        document.addEventListener('touchstart', (e) => {
            if (revealed && !wrapper.contains(e.target)) {
                content.style.transform = '';
                actions.classList.remove('revealed');
                revealed = false;
            }
        }, { passive: true });
    });
}

document.addEventListener('DOMContentLoaded', initSwipeItems);
document.addEventListener('livewire:navigated', initSwipeItems);
document.addEventListener('livewire:update', () => { setTimeout(initSwipeItems, 100); });

/* ============================================================
 * PULL TO REFRESH
 * Only active on mobile (touch devices). Pulling down from the
 * top of the page by >80px triggers a Livewire component refresh
 * or, as a fallback, a full page reload.
 * ============================================================ */
(function initPullToRefresh() {
    const THRESHOLD = 80;
    let startY = 0, pulling = false, indicator = null;

    function getIndicator() {
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'ptr-indicator';
            indicator.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg><span>Release to refresh</span>`;
            indicator.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:200;display:flex;align-items:center;justify-content:center;gap:6px;padding:8px 16px;font-size:13px;transform:translateY(-100%);transition:transform 0.2s ease;background:rgba(255,255,255,0.95);backdrop-filter:blur(4px);box-shadow:0 1px 3px rgba(0,0,0,0.1);';
            document.body.appendChild(indicator);
        }
        return indicator;
    }

    document.addEventListener('touchstart', (e) => {
        // Only trigger when scrolled to top
        if (window.scrollY === 0 && e.touches.length === 1) {
            startY = e.touches[0].clientY;
            pulling = true;
        }
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!pulling) return;
        const dy = e.touches[0].clientY - startY;
        if (dy > 0 && window.scrollY === 0) {
            const progress = Math.min(dy / THRESHOLD, 1);
            getIndicator().style.transform = `translateY(${progress * 60 - 60}px)`;
        }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        if (!pulling) return;
        pulling = false;
        const dy = e.changedTouches[0].clientY - startY;
        const ind = getIndicator();
        ind.style.transform = 'translateY(-100%)';

        if (dy > THRESHOLD && window.scrollY === 0) {
            // Attempt Livewire refresh on the topmost component, else reload
            if (window.Livewire) {
                try {
                    const firstComponent = document.querySelector('[wire\\:id]');
                    if (firstComponent) {
                        const id = firstComponent.getAttribute('wire:id');
                        window.Livewire.find(id)?.$refresh();
                        return;
                    }
                } catch (err) { /* fall through */ }
            }
            window.location.reload();
        }
    }, { passive: true });
})();


window.__initCropper = function (imageEl, aspectRatio) {
    if (window.__currentCropper) {
        window.__currentCropper.destroy();
    }
    window.__currentCropper = new Cropper(imageEl, {
        aspectRatio: aspectRatio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
    return window.__currentCropper;
};

window.__destroyCropper = function () {
    if (window.__currentCropper) {
        window.__currentCropper.destroy();
        window.__currentCropper = null;
    }
};

window.__getCroppedBlob = function (mimeType) {
    return new Promise((resolve) => {
        if (!window.__currentCropper) {
            resolve(null);
            return;
        }
        const canvas = window.__currentCropper.getCroppedCanvas({
            maxWidth: 1024,
            maxHeight: 1024,
        });
        canvas.toBlob((blob) => resolve(blob), mimeType, 0.9);
    });
};

/* ============================================================
 * PWA SERVICE WORKER REGISTRATION
 * Registers the Workbox-generated service worker for offline
 * support and PWA installability. On update detection, the new
 * worker is activated immediately and the page reloads so the
 * user always gets the latest assets.
 * ============================================================ */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });

            const activate = (worker) => {
                worker.postMessage({ type: 'SKIP_WAITING' });
                window.location.reload();
            };

            if (reg.waiting) {
                activate(reg.waiting);
            }

            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        activate(newWorker);
                    }
                });
            });
        } catch {
            // Service Worker registration failed — offline not available
        }
    });
}
