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

// Letter-by-Letter Kinetic Fade Up Animation for Headings and Subtitles
export function initLetterAnimations() {
    const targets = document.querySelectorAll('.animate-heading, .animate-subheading, [data-animate-letters]');
    if (!targets.length) return;

    function getGraphemes(str) {
        if (typeof Intl !== 'undefined' && Intl.Segmenter) {
            try {
                const segmenter = new Intl.Segmenter(undefined, { granularity: 'grapheme' });
                return Array.from(segmenter.segment(str), s => s.segment);
            } catch (e) {}
        }
        return Array.from(str);
    }

    function splitTextNode(textNode, startIndex) {
        const text = textNode.textContent;
        if (!text || !text.trim()) {
            return { fragment: document.createTextNode(text), nextIndex: startIndex };
        }

        const fragment = document.createDocumentFragment();
        const tokens = text.split(/(\s+)/);
        let charIndex = startIndex;

        tokens.forEach(token => {
            if (/^\s+$/.test(token)) {
                fragment.appendChild(document.createTextNode(' '));
            } else if (token.length > 0) {
                const wordSpan = document.createElement('span');
                wordSpan.className = 'inline-word inline-block whitespace-nowrap';

                const chars = getGraphemes(token);
                for (let i = 0; i < chars.length; i++) {
                    const charSpan = document.createElement('span');
                    charSpan.className = 'letter-char inline-block';
                    charSpan.textContent = chars[i];
                    charSpan.style.setProperty('--char-i', charIndex.toString());
                    wordSpan.appendChild(charSpan);
                    charIndex++;
                }
                fragment.appendChild(wordSpan);
            }
        });

        return { fragment, nextIndex: charIndex };
    }

    targets.forEach(el => {
        if (el.getAttribute('data-split-done')) return;
        el.setAttribute('data-split-done', 'true');

        let globalIndex = 0;
        function walkNodes(node) {
            const childNodes = Array.from(node.childNodes);
            childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    if (child.textContent.trim().length > 0) {
                        const { fragment, nextIndex } = splitTextNode(child, globalIndex);
                        globalIndex = nextIndex;
                        node.replaceChild(fragment, child);
                    }
                } else if (child.nodeType === Node.ELEMENT_NODE && !child.classList.contains('no-split')) {
                    walkNodes(child);
                }
            });
        }

        walkNodes(el);
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.05,
            rootMargin: '0px 0px -20px 0px'
        });

        targets.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                el.classList.add('in-view');
            } else {
                observer.observe(el);
            }
        });
    } else {
        targets.forEach(el => el.classList.add('in-view'));
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initMalayalamInputs();
        initLetterAnimations();
    });
} else {
    initMalayalamInputs();
    initLetterAnimations();
}

window.detectAndApplyMalayalamFont = detectAndApplyMalayalamFont;
window.isMalayalamText = isMalayalamText;
window.initLetterAnimations = initLetterAnimations;

Alpine.start();
