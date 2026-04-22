/**
 * Modal System (CADA Custom Modal)
 * Ligero, sin dependencias, diseñado para mantener la UI consistente.
 */
const CadaModal = {
    init() {
        if (document.getElementById('cada-modal')) return;
        
        const html = `
            <div id="cada-modal" class="cada-modal-overlay">
                <div class="cada-modal-box">
                    <div class="cada-modal-icon" id="cada-modal-icon"></div>
                    <h3 id="cada-modal-title"></h3>
                    <p id="cada-modal-text"></p>
                    <div class="cada-modal-actions">
                        <button class="btn btn-ghost" id="cada-modal-btn-cancel">Cancelar</button>
                        <button class="btn btn-primary" id="cada-modal-btn-confirm">Confirmar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById('cada-modal');
        const cancelBtn = document.getElementById('cada-modal-btn-cancel');
        
        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('show')) {
                this.close();
            }
        });
        
        // Cancel actions
        cancelBtn.addEventListener('click', () => this.close());
    },

    /**
     * Muestra un diálogo de confirmación
     * @param {Object} options 
     * @returns Promise que se resuelve a true si confirma, false si cancela
     */
    confirm(options) {
        this.init();
        return new Promise((resolve) => {
            const overlay = document.getElementById('cada-modal');
            const box = overlay.querySelector('.cada-modal-box');
            const titleEl = document.getElementById('cada-modal-title');
            const textEl = document.getElementById('cada-modal-text');
            const iconEl = document.getElementById('cada-modal-icon');
            const btnConfirm = document.getElementById('cada-modal-btn-confirm');
            const btnCancel = document.getElementById('cada-modal-btn-cancel');

            // Set values
            titleEl.textContent = options.title || '¿Estás seguro?';
            textEl.textContent = options.text || 'Esta acción no se puede deshacer.';
            
            // Set style based on type
            const type = options.type || 'warning';
            iconEl.innerHTML = '';
            
            if (type === 'danger') {
                iconEl.innerHTML = '<i class="ph ph-warning-circle" style="color: var(--color-danger); font-size: 48px;"></i>';
                btnConfirm.className = 'btn btn-primary'; // Using primary for all actions right now but we can style danger differently
                btnConfirm.style.backgroundColor = 'var(--color-danger)';
            } else if (type === 'warning') {
                iconEl.innerHTML = '<i class="ph ph-warning" style="color: var(--color-warning); font-size: 48px;"></i>';
                btnConfirm.className = 'btn btn-primary';
                btnConfirm.style.backgroundColor = 'var(--color-warning)';
            } else {
                iconEl.innerHTML = '<i class="ph ph-info" style="color: var(--color-info); font-size: 48px;"></i>';
                btnConfirm.className = 'btn btn-primary';
                btnConfirm.style.backgroundColor = 'var(--color-primary)';
            }

            btnConfirm.textContent = options.confirmText || 'Confirmar';
            btnCancel.textContent = options.cancelText || 'Cancelar';

            // Handlers
            const handleConfirm = () => {
                this.close();
                resolve(true);
            };
            
            const handleCancel = () => {
                this.close();
                resolve(false);
            };

            // Remove old listeners (clone trick)
            const newBtnConfirm = btnConfirm.cloneNode(true);
            btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);
            newBtnConfirm.addEventListener('click', handleConfirm);
            
            const newBtnCancel = btnCancel.cloneNode(true);
            btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
            newBtnCancel.addEventListener('click', handleCancel);

            // Show
            overlay.classList.add('show');
            setTimeout(() => box.classList.add('show'), 10);
        });
    },
    
    close() {
        const overlay = document.getElementById('cada-modal');
        const box = overlay.querySelector('.cada-modal-box');
        if (box) box.classList.remove('show');
        setTimeout(() => overlay.classList.remove('show'), 200);
    }
};

// Auto intercept standard confirms
document.addEventListener('DOMContentLoaded', () => {
    CadaModal.init();
    
    document.body.addEventListener('submit', async (e) => {
        const confirmMsg = e.target.getAttribute('data-confirm');
        if (confirmMsg) {
            e.preventDefault();
            const confirmed = await CadaModal.confirm({
                title: 'Confirmar acción',
                text: confirmMsg,
                type: 'danger',
                confirmText: 'Sí, continuar'
            });
            if (confirmed) {
                e.target.removeAttribute('data-confirm');
                e.target.submit();
            }
        }
    });
});
