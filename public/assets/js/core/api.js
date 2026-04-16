/**
 * Wrapper de fetch para llamar a la API. La cookie JWT httpOnly
 * viaja automáticamente; solo agregamos el CSRF token cuando hay.
 */
(function () {
    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function request(url, { method = 'GET', body = null, headers = {} } = {}) {
        const opts = {
            method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...headers
            }
        };
        if (body && method !== 'GET' && method !== 'HEAD') {
            opts.headers['Content-Type'] = 'application/json';
            opts.headers['X-CSRF-Token'] = getCsrf();
            opts.body = JSON.stringify(body);
        }

        const res = await fetch(url, opts);
        const contentType = res.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        const data = isJson ? await res.json() : await res.text();

        if (!res.ok) {
            const err = new Error(data?.error || `HTTP ${res.status}`);
            err.status = res.status;
            err.data = data;
            throw err;
        }
        return data;
    }

    window.API = {
        get:  (url)            => request(url),
        post: (url, body)      => request(url, { method: 'POST', body }),
        put:  (url, body)      => request(url, { method: 'PUT', body }),
        patch:(url, body)      => request(url, { method: 'PATCH', body }),
        del:  (url)            => request(url, { method: 'DELETE' })
    };
})();
