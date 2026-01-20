/**
 * Get a translation string from the global translations object
 * @param {string} key - The translation key (e.g., 'ideas.placeholder')
 * @param {Object} replacements - Key-value pairs for placeholder replacement
 * @returns {string} The translated string
 */
export function __(key, replacements = {}) {
    let translation = key.split('.').reduce((obj, k) => obj?.[k], window.__translations) || key;

    Object.entries(replacements).forEach(([k, v]) => {
        translation = translation.replace(`:${k}`, v);
    });

    return translation;
}

/**
 * Get the current locale
 * @returns {string} The current locale code (e.g., 'nl', 'en')
 */
export function getLocale() {
    return window.__locale || 'nl';
}

/**
 * Get a date formatted according to the current locale
 * @param {Date|string|number} date - The date to format
 * @param {Object} options - Intl.DateTimeFormat options
 * @returns {string} The formatted date string
 */
export function formatDate(date, options = {}) {
    const locale = getLocale() === 'nl' ? 'nl-NL' : 'en-US';
    const defaultOptions = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(date).toLocaleDateString(locale, { ...defaultOptions, ...options });
}
