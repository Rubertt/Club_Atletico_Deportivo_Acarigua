/**
 * CadaPagination - Helper de paginación reutilizable client-side con diseño sliding window
 * @param {Object} options 
 * @param {string} options.rowSelector Selector CSS para obtener las filas a paginar (ej: '.asistencia-row')
 * @param {string} options.containerId ID del div donde se renderizará el pasador de páginas (ej: 'asistencias-pagination')
 * @param {number} [options.rowsPerPage] Cantidad de filas por página. Por defecto lee window.ROWS_PER_PAGE o usa 15.
 * @param {function} [options.onPageChange] Callback opcional que recibe el número de página activa al cambiar de página.
 */
window.CadaPagination = function(options = {}) {
    const rowSelector = options.rowSelector;
    const containerId = options.containerId;
    const onPageChange = options.onPageChange;
    
    // Leer el valor dinámico
    const rowsPerPage = parseInt(options.rowsPerPage || window.ROWS_PER_PAGE || 15, 10);
    
    // Obtener las filas reales
    const rows = Array.from(document.querySelectorAll(rowSelector));
    const totalCount = rows.length;
    const container = document.getElementById(containerId);
    
    if (container) {
        container.innerHTML = '';
    }
    
    // Si no hay filas o el total de filas cabe en una sola página
    if (totalCount <= rowsPerPage) {
        rows.forEach(r => r.style.display = '');
        return;
    }
    
    const totalPages = Math.ceil(totalCount / rowsPerPage);
    let currentPage = 1;
    
    function showPage(page) {
        currentPage = page;
        
        // Ocultar todas y mostrar solo el rango correspondiente
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((r, idx) => {
            if (idx >= start && idx < end) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        
        // Renderizar los controles con sliding window
        if (container) {
            renderControls();
        }
        
        if (typeof onPageChange === 'function') {
            onPageChange(page);
        }
    }
    
    function renderControls() {
        container.innerHTML = '';
        const ul = document.createElement('ul');
        ul.className = 'pagination';
        
        // Botón << (Ir al principio)
        const liFirst = document.createElement('li');
        if (currentPage === 1) {
            liFirst.className = 'disabled';
            const span = document.createElement('span');
            span.innerHTML = '<i class="ph ph-caret-double-left"></i>';
            liFirst.appendChild(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.innerHTML = '<i class="ph ph-caret-double-left"></i>';
            a.onclick = (e) => { e.preventDefault(); showPage(1); };
            liFirst.appendChild(a);
        }
        ul.appendChild(liFirst);
        
        // Botón < (Retroceder una página)
        const liPrev = document.createElement('li');
        if (currentPage === 1) {
            liPrev.className = 'disabled';
            const span = document.createElement('span');
            span.innerHTML = '<i class="ph ph-caret-left"></i>';
            liPrev.appendChild(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.innerHTML = '<i class="ph ph-caret-left"></i>';
            a.onclick = (e) => { e.preventDefault(); showPage(currentPage - 1); };
            liPrev.appendChild(a);
        }
        ul.appendChild(liPrev);
        
        // Lógica de ventana deslizante (sliding window)
        // Mostraremos: 1, ..., vecinas, ..., totalPages
        const range = 1; // cantidad de páginas vecinas a mostrar a cada lado de la activa
        const pages = [];
        
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || // siempre primera
                i === totalPages || // siempre última
                (i >= currentPage - range && i <= currentPage + range) // rango alrededor de la activa
            ) {
                pages.push(i);
            } else if (pages[pages.length - 1] !== '...') {
                pages.push('...');
            }
        }
        
        pages.forEach(p => {
            const li = document.createElement('li');
            if (p === '...') {
                li.className = 'disabled';
                const span = document.createElement('span');
                span.textContent = '...';
                li.appendChild(span);
            } else if (p === currentPage) {
                li.className = 'active';
                const span = document.createElement('span');
                span.textContent = p;
                li.appendChild(span);
            } else {
                const a = document.createElement('a');
                a.href = '#';
                a.textContent = p;
                a.onclick = (e) => { e.preventDefault(); showPage(p); };
                li.appendChild(a);
            }
            ul.appendChild(li);
        });
        
        // Botón > (Avanzar una página)
        const liNext = document.createElement('li');
        if (currentPage === totalPages) {
            liNext.className = 'disabled';
            const span = document.createElement('span');
            span.innerHTML = '<i class="ph ph-caret-right"></i>';
            liNext.appendChild(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.innerHTML = '<i class="ph ph-caret-right"></i>';
            a.onclick = (e) => { e.preventDefault(); showPage(currentPage + 1); };
            liNext.appendChild(a);
        }
        ul.appendChild(liNext);
        
        // Botón >> (Ir al final)
        const liLast = document.createElement('li');
        if (currentPage === totalPages) {
            liLast.className = 'disabled';
            const span = document.createElement('span');
            span.innerHTML = '<i class="ph ph-caret-double-right"></i>';
            liLast.appendChild(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.innerHTML = '<i class="ph ph-caret-double-right"></i>';
            a.onclick = (e) => { e.preventDefault(); showPage(totalPages); };
            liLast.appendChild(a);
        }
        ul.appendChild(liLast);
        
        container.appendChild(ul);
    }
    
    // Mostrar la primera página inicialmente
    showPage(1);
};
