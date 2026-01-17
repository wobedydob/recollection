<template>
    <div class="password-input-wrapper">
        <input
            :type="showPassword ? 'text' : 'password'"
            :name="name"
            :id="id"
            :placeholder="placeholder"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="input password-input"
            :class="inputClass"
            :required="required"
        />
        <span v-if="showMatch !== null" class="match-indicator" :class="{ match: showMatch, 'no-match': !showMatch }">
            {{ showMatch ? '✓' : '✗' }}
        </span>
        <button type="button" class="toggle-password" @click="showPassword = !showPassword">
            {{ showPassword ? '🙈' : '👁️' }}
        </button>
    </div>
</template>

<script>
export default {
    name: 'PasswordInput',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            required: true
        },
        id: {
            type: String,
            default: null
        },
        placeholder: {
            type: String,
            default: ''
        },
        required: {
            type: Boolean,
            default: false
        },
        showMatch: {
            type: Boolean,
            default: null
        },
        inputClass: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            showPassword: false
        }
    }
}
</script>
