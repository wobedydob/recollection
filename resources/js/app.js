import './bootstrap';
import { createApp } from 'vue';
import PasswordInput from './components/PasswordInput.vue';
import PasswordStrength from './components/PasswordStrength.vue';
import IdeasApp from './components/IdeasApp.vue';
import TodoApp from './components/TodoApp.vue';
import TodoListDetail from './components/TodoListDetail.vue';

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
