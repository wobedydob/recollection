import './bootstrap';
import { createApp } from 'vue';
import PasswordInput from './components/PasswordInput.vue';
import PasswordStrength from './components/PasswordStrength.vue';
import IdeasApp from './components/IdeasApp.vue';

// Auto-initialize Vue components on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize IdeasApp if the element exists
    const ideasEl = document.getElementById('ideas-app');
    if (ideasEl) {
        createApp(IdeasApp).mount(ideasEl);
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

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const dropdown = document.querySelector('.user-dropdown.show');
        if (dropdown && !e.target.closest('.nav-user')) {
            dropdown.classList.remove('show');
        }
    });
});
