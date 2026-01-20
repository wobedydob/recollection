/**
 * Custom Tooltip Directive
 * Usage: v-tooltip="'Tooltip text'" or v-tooltip.bottom="'Text'" (positions: top, bottom, left, right)
 */

const createTooltip = (el, binding) => {
    // Remove existing tooltip if any
    removeTooltip(el)

    const text = binding.value
    if (!text) return

    // Determine position (default: top)
    const position = binding.arg || 'top'

    // Create tooltip element
    const tooltip = document.createElement('div')
    tooltip.className = `tooltip tooltip-${position}`
    tooltip.textContent = text
    tooltip.setAttribute('role', 'tooltip')

    // Store reference on element
    el._tooltip = tooltip
    el._tooltipPosition = position

    // Event handlers
    el._showTooltip = () => {
        document.body.appendChild(tooltip)
        positionTooltip(el, tooltip, position)
        requestAnimationFrame(() => {
            tooltip.classList.add('tooltip-visible')
        })
    }

    el._hideTooltip = () => {
        tooltip.classList.remove('tooltip-visible')
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip)
            }
        }, 150) // Match CSS transition duration
    }

    // Bind events
    el.addEventListener('mouseenter', el._showTooltip)
    el.addEventListener('mouseleave', el._hideTooltip)
    el.addEventListener('focus', el._showTooltip)
    el.addEventListener('blur', el._hideTooltip)
}

const positionTooltip = (el, tooltip, position) => {
    const rect = el.getBoundingClientRect()
    const tooltipRect = tooltip.getBoundingClientRect()
    const scrollX = window.scrollX
    const scrollY = window.scrollY
    const gap = 8 // Gap between element and tooltip

    let top, left

    switch (position) {
        case 'bottom':
            top = rect.bottom + scrollY + gap
            left = rect.left + scrollX + (rect.width - tooltipRect.width) / 2
            break
        case 'left':
            top = rect.top + scrollY + (rect.height - tooltipRect.height) / 2
            left = rect.left + scrollX - tooltipRect.width - gap
            break
        case 'right':
            top = rect.top + scrollY + (rect.height - tooltipRect.height) / 2
            left = rect.right + scrollX + gap
            break
        case 'top':
        default:
            top = rect.top + scrollY - tooltipRect.height - gap
            left = rect.left + scrollX + (rect.width - tooltipRect.width) / 2
            break
    }

    // Keep tooltip within viewport
    const padding = 8
    const viewportWidth = window.innerWidth
    const viewportHeight = window.innerHeight

    // Horizontal bounds
    if (left < padding) {
        left = padding
    } else if (left + tooltipRect.width > viewportWidth - padding) {
        left = viewportWidth - tooltipRect.width - padding
    }

    // Vertical bounds - flip if needed
    if (position === 'top' && top < scrollY + padding) {
        // Flip to bottom
        top = rect.bottom + scrollY + gap
        tooltip.classList.remove('tooltip-top')
        tooltip.classList.add('tooltip-bottom')
    } else if (position === 'bottom' && top + tooltipRect.height > scrollY + viewportHeight - padding) {
        // Flip to top
        top = rect.top + scrollY - tooltipRect.height - gap
        tooltip.classList.remove('tooltip-bottom')
        tooltip.classList.add('tooltip-top')
    }

    tooltip.style.top = `${top}px`
    tooltip.style.left = `${left}px`
}

const removeTooltip = (el) => {
    if (el._tooltip) {
        if (el._tooltip.parentNode) {
            el._tooltip.parentNode.removeChild(el._tooltip)
        }
        el._tooltip = null
    }
    if (el._showTooltip) {
        el.removeEventListener('mouseenter', el._showTooltip)
        el.removeEventListener('mouseleave', el._hideTooltip)
        el.removeEventListener('focus', el._showTooltip)
        el.removeEventListener('blur', el._hideTooltip)
    }
}

export const tooltip = {
    mounted(el, binding) {
        createTooltip(el, binding)
    },
    updated(el, binding) {
        if (binding.value !== binding.oldValue) {
            createTooltip(el, binding)
        }
    },
    unmounted(el) {
        removeTooltip(el)
    }
}

export default tooltip
