<template>
    <div class="password-strength">
        <div class="strength-section">
            <div class="strength-bar">
                <div
                    class="strength-fill"
                    :style="{
                        width: strengthPercent + '%',
                        backgroundColor: strengthColor
                    }"
                ></div>
            </div>
            <span class="strength-label" :style="{ color: strengthColor }">
                {{ strengthLabel }}
            </span>
        </div>
        <div class="password-requirements">
            <div class="requirement" :class="{ met: hasLength }">
                <span class="requirement-icon">{{ hasLength ? '✓' : '○' }}</span>
                <span>Minimaal 6 tekens</span>
            </div>
            <div class="requirement" :class="{ met: hasNumber }">
                <span class="requirement-icon">{{ hasNumber ? '✓' : '○' }}</span>
                <span>Minimaal 1 cijfer</span>
            </div>
            <div class="requirement" :class="{ met: hasSpecial }">
                <span class="requirement-icon">{{ hasSpecial ? '✓' : '○' }}</span>
                <span>Minimaal 1 speciaal teken (!@#$%...)</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PasswordStrength',
    props: {
        password: {
            type: String,
            default: ''
        }
    },
    computed: {
        hasLength() {
            return this.password.length >= 6;
        },
        hasNumber() {
            return /\d/.test(this.password);
        },
        hasSpecial() {
            return /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/~`]/.test(this.password);
        },
        strength() {
            let score = 0;
            if (this.hasLength) score++;
            if (this.hasNumber) score++;
            if (this.hasSpecial) score++;
            if (this.password.length >= 10) score++;
            if (this.password.length >= 14) score++;
            return score;
        },
        strengthPercent() {
            return (this.strength / 5) * 100;
        },
        strengthColor() {
            if (this.strength <= 1) return '#e57373';
            if (this.strength <= 2) return '#ffb74d';
            if (this.strength <= 3) return '#fff176';
            if (this.strength <= 4) return '#aed581';
            return '#81c784';
        },
        strengthLabel() {
            if (this.password.length === 0) return '';
            if (this.strength <= 1) return 'Zwak';
            if (this.strength <= 2) return 'Matig';
            if (this.strength <= 3) return 'Redelijk';
            if (this.strength <= 4) return 'Sterk';
            return 'Zeer sterk';
        },
        isValid() {
            return this.hasLength && this.hasNumber && this.hasSpecial;
        }
    },
    watch: {
        isValid: {
            handler(newVal) {
                this.$emit('validity-change', newVal);
            },
            immediate: true
        }
    },
    emits: ['validity-change']
}
</script>
