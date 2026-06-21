/**
 * Global Tooltip Manager for CADA
 * ─────────────────────────────────────────────────────────
 * Solves the issue of tooltips getting clipped/cut off inside 
 * modal bodies or containers with overflow limits.
 * 
 * Works by dynamically positioning a single tooltip element 
 * appended directly to document.body.
 */
(function () {
    'use strict';

    let currentTooltip = null;
    let currentTarget = null;

    /**
     * Calculates layout coordinates and creates the tooltip element.
     */
    function createTooltip(target) {
        const text = target.getAttribute('data-tooltip');
        if (!text) return;

        // Temporarily remove title attribute to prevent double tooltips
        if (target.hasAttribute('title')) {
            target.setAttribute('data-original-title', target.getAttribute('title'));
            target.removeAttribute('title');
        }

        const pos = target.getAttribute('data-tooltip-pos') || 'top';

        // Create the global tooltip container
        const tooltip = document.createElement('div');
        tooltip.className = `global-tooltip global-tooltip--${pos}`;
        tooltip.textContent = text;

        // Render to DOM (outside any modal overflow boundary)
        document.body.appendChild(tooltip);
        currentTooltip = tooltip;
        currentTarget = target;

        // Measure boundaries
        const rect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();

        // Calculate horizontal centering and vertical offset
        let leftCoord = rect.left + (rect.width / 2) - (tooltipRect.width / 2) + window.scrollX;
        let topCoord = 0;

        if (pos === 'bottom') {
            topCoord = rect.bottom + window.scrollY + 8;
        } else {
            // Default position: top
            topCoord = rect.top + window.scrollY - tooltipRect.height - 8;
        }

        // Keep tooltip within screen boundaries horizontally
        const margin = 8;
        const maxLeft = window.innerWidth + window.scrollX - tooltipRect.width - margin;
        const minLeft = window.scrollX + margin;
        leftCoord = Math.max(minLeft, Math.min(maxLeft, leftCoord));

        // Position the element
        tooltip.style.left = `${leftCoord}px`;
        tooltip.style.top = `${topCoord}px`;

        // Trigger css fade-in transition
        requestAnimationFrame(() => {
            tooltip.classList.add('global-tooltip--visible');
        });
    }

    /**
     * Fades out and removes the active tooltip element.
     */
    function removeTooltip() {
        if (!currentTooltip) return;
        const tooltip = currentTooltip;
        currentTooltip = null;

        // Restore original title if it was temporarily removed
        if (currentTarget && currentTarget.hasAttribute('data-original-title')) {
            currentTarget.setAttribute('title', currentTarget.getAttribute('data-original-title'));
            currentTarget.removeAttribute('data-original-title');
        }
        currentTarget = null;

        tooltip.classList.remove('global-tooltip--visible');
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 150); // Matches CSS opacity transition
    }

    // ─── Event Listeners ───────────────────────────────────────────────────

    // Event delegation for mouse hover entering target
    document.addEventListener('mouseover', function (e) {
        const target = e.target.closest('[data-tooltip]');
        if (!target) return;

        // Avoid re-creating tooltip if already hovering it
        if (currentTarget === target) return;

        removeTooltip();
        createTooltip(target);
    });

    // Remove tooltip when mouse exits target
    document.addEventListener('mouseout', function (e) {
        if (!currentTarget) return;

        let related = e.relatedTarget;
        if (related) {
            // If moving inside the same element, keep it visible
            if (related === currentTarget || currentTarget.contains(related)) {
                return;
            }
        }

        removeTooltip();
    });

    // Scroll capture phase: immediately hide tooltip when any sub-element (like .modal-body) scrolls
    document.addEventListener('scroll', removeTooltip, true);

    // Hide tooltip on window resizing
    window.addEventListener('resize', removeTooltip);

})();
