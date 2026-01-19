import './bootstrap';
import { createApp } from 'vue';
import PasswordInput from './components/PasswordInput.vue';
import PasswordStrength from './components/PasswordStrength.vue';
import IdeasApp from './components/IdeasApp.vue';
import TodoApp from './components/TodoApp.vue';
import TodoListDetail from './components/TodoListDetail.vue';
import SuggestionsApp from './components/SuggestionsApp.vue';

// Theme Toggle Functions
window.toggleTheme = async function() {
    const html = document.documentElement;
    const newTheme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    await setTheme(newTheme);
};

window.setTheme = async function(theme) {
    const html = document.documentElement;
    html.dataset.theme = theme;

    // Also store in localStorage for pages without auth (error pages, etc.)
    localStorage.setItem('theme', theme);

    // Update toggle button text in header
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('.theme-icon');
        if (icon) {
            icon.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
        themeToggle.childNodes[themeToggle.childNodes.length - 1].textContent =
            theme === 'dark' ? ' Lichte modus' : ' Donkere modus';
    }

    // Update profile page theme buttons
    document.querySelectorAll('.theme-option').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('onclick')?.includes(`'${theme}'`));
    });

    // Persist to database
    try {
        await fetch('/api/auth/theme', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({ theme })
        });
    } catch (e) {
        console.error('Failed to save theme preference:', e);
    }
};

window.setColorTheme = async function(colorTheme) {
    document.documentElement.dataset.color = colorTheme;
    localStorage.setItem('color_theme', colorTheme);

    // Update active state on buttons
    document.querySelectorAll('.color-theme-option').forEach(btn => {
        btn.classList.toggle('active', btn.classList.contains(colorTheme));
    });

    // Persist to database
    try {
        await fetch('/api/auth/color-theme', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({ color_theme: colorTheme })
        });
    } catch (e) {
        console.error('Failed to save color theme preference:', e);
    }
};

// Auto-initialize Vue components on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize IdeasApp if the element exists
    const ideasEl = document.getElementById('ideas-app');
    if (ideasEl) {
        createApp(IdeasApp).mount(ideasEl);
    }

    // Initialize TodoApp if the element exists
    const todoEl = document.getElementById('todo-app');
    if (todoEl) {
        createApp(TodoApp).mount(todoEl);
    }

    // Initialize TodoListDetail if the element exists
    const todoDetailEl = document.getElementById('todo-list-detail');
    if (todoDetailEl) {
        const listId = parseInt(todoDetailEl.dataset.listId, 10);
        createApp(TodoListDetail, { listId }).mount(todoDetailEl);
    }

    // Initialize SuggestionsApp if the element exists
    const suggestionsEl = document.getElementById('suggestions-app');
    if (suggestionsEl) {
        createApp(SuggestionsApp).mount(suggestionsEl);
    }

    // Initialize password components on auth pages
    const authForms = document.querySelectorAll('[data-vue-auth]');
    authForms.forEach(form => {
        const app = createApp({
            components: {
                PasswordInput,
                PasswordStrength
            },
            data() {
                return {
                    password: '',
                    passwordConfirmation: '',
                    currentPassword: '',
                    newPassword: '',
                    newPasswordConfirmation: '',
                    passwordValid: false
                }
            },
            computed: {
                passwordsMatch() {
                    if (!this.passwordConfirmation) return null;
                    return this.password === this.passwordConfirmation;
                },
                newPasswordsMatch() {
                    if (!this.newPasswordConfirmation) return null;
                    return this.newPassword === this.newPasswordConfirmation;
                }
            },
            methods: {
                onPasswordValidityChange(valid) {
                    this.passwordValid = valid;
                }
            }
        });
        app.mount(form);
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        // Close user menu dropdown
        const userDropdown = document.querySelector('.menu-dropdown.show');
        if (userDropdown && !e.target.closest('.user-menu')) {
            userDropdown.classList.remove('show');
        }

        // Close app switcher dropdown
        const appDropdown = document.querySelector('.app-dropdown.show');
        if (appDropdown && !e.target.closest('.app-switcher')) {
            appDropdown.classList.remove('show');
        }
    });
});
