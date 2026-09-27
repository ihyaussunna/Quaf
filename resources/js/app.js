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

    // For text content elements
    const text = el.innerText || el.textContent || '';
    if (MALAYALAM_REGEX.test(text)) {
        el.classList.add('font-anek');
        el.setAttribute('data-script', 'malayalam');
    }
}

export function scanDocumentForMalayalam() {
    const elements = document.querySelectorAll(
        'input[type="text"], input:not([type]), textarea, .rule-content, .rules-text, .criterion-text, .criterion-name, .news-title, .news-content, .prose, [data-detect-lang]'
    );
    elements.forEach(detectAndApplyMalayalamFont);
}

// Live typing detection on all inputs/textareas
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

// Initial scan on DOM load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scanDocumentForMalayalam);
} else {
    scanDocumentForMalayalam();
}

// Observe dynamic DOM changes (e.g. Alpine.js adding criteria rows, tab switching)
const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
            if (node.nodeType === Node.ELEMENT_NODE) {
                detectAndApplyMalayalamFont(node);
                if (node.querySelectorAll) {
                    node.querySelectorAll('input, textarea, p, h1, h2, h3, h4, td, span').forEach(detectAndApplyMalayalamFont);
                }
            }
        }
    }
});

if (document.body) {
    observer.observe(document.body, { childList: true, subtree: true });
} else {
    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { childList: true, subtree: true });
    });
}

window.detectAndApplyMalayalamFont = detectAndApplyMalayalamFont;
window.isMalayalamText = isMalayalamText;

Alpine.start();
