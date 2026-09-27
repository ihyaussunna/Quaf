import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Automated Malayalam Script Detection & Dynamic Anek Malayalam Font Switching
const MALAYALAM_REGEX = /[\u0D00-\u0D7F]/;

export function isMalayalamText(str) {
    return MALAYALAM_REGEX.test(str || '');
}

export function detectAndApplyMalayalamFont(el) {
    if (!el || el.nodeType !== Node.ELEMENT_NODE) return;

    // For inputs and textareas: inspect value or placeholder
    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        const text = el.value || el.placeholder || '';
        if (MALAYALAM_REGEX.test(text)) {
            el.classList.add('font-anek');
            el.setAttribute('data-script', 'malayalam');
        } else if (el.getAttribute('data-script') === 'malayalam') {
            el.classList.remove('font-anek');
            el.removeAttribute('data-script');
        }
        return;
    }

    // For explicitly designated elements only
    if (el.hasAttribute('data-detect-lang') || el.classList.contains('malayalam-detect')) {
        const text = el.innerText || el.textContent || '';
        if (MALAYALAM_REGEX.test(text)) {
            el.classList.add('font-anek');
            el.setAttribute('data-script', 'malayalam');
        } else {
            el.classList.remove('font-anek');
            el.removeAttribute('data-script');
        }
    }
}

// Live typing detection on text inputs and textareas only
document.addEventListener('input', (e) => {
    if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
        detectAndApplyMalayalamFont(e.target);
    }
}, true);

document.addEventListener('change', (e) => {
    if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
        detectAndApplyMalayalamFont(e.target);
    }
}, true);

// Initial check for inputs with pre-filled Malayalam content
export function initMalayalamInputs() {
    document.querySelectorAll('input, textarea, [data-detect-lang], .malayalam-detect').forEach(detectAndApplyMalayalamFont);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMalayalamInputs);
} else {
    initMalayalamInputs();
}

window.detectAndApplyMalayalamFont = detectAndApplyMalayalamFont;
window.isMalayalamText = isMalayalamText;

Alpine.start();
