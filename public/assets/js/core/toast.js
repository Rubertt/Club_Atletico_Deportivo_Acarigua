/**
 * Toast sencillo (auto-desaparece a los 4s).
 * Uso: Toast.show('Mensaje', 'success' | 'danger' | 'warning' | 'info');
 */
(function () {
    let container;

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.id = 'toast-container';
        Object.assign(container.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: 9999,
            display: 'flex',
            flexDirection: 'column',
            gap: '8px',
            maxWidth: '360px'
        });
        document.body.appendChild(container);
        return container;
    }

    function show(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.textContent = message;
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(30px)';
        toast.style.transition = 'all .25s ease';
        ensureContainer().appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(30px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    window.Toast = { show };
})();
