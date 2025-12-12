document.addEventListener('DOMContentLoaded', () => {
    const moduleRoot = document.querySelector('[data-module="livro-ocorrencias"]');
    if (!moduleRoot) {
        return;
    }

    const baseUrl = document.body.dataset.baseUrl || '/';
    const endpoint = new URL('admin/livro-ocorrencias/municipios', baseUrl).toString();
    const MIN_TERM = 2;
    const DEBOUNCE_DELAY = 250;
    const MAX_CACHE_SIZE = 20;

    const state = new WeakMap();

    const templateEmpty = `<div class="text-muted small px-3 py-2">Nenhum município encontrado.</div>`;
    const templateLoading = `<div class="text-muted small px-3 py-2">Buscando...</div>`;
    const templateHint = `<div class="text-muted small px-3 py-2">Digite pelo menos ${MIN_TERM} caracteres.</div>`;
    const templateError = `<div class="text-danger small px-3 py-2">Não foi possível carregar municípios agora.</div>`;

    const closeResults = (box) => {
        box?.classList.add('d-none');
        box?.removeAttribute('data-open');
    };

    const openResults = (box) => {
        if (!box) return;
        box.classList.remove('d-none');
        box.setAttribute('data-open', 'true');
    };

    const fetchMunicipios = async (term, limit = 10) => {
        const url = `${endpoint}?q=${encodeURIComponent(term)}&limit=${limit}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            throw new Error('Erro ao consultar municípios');
        }
        return response.json();
    };

    const rememberResult = (cache, key, value) => {
        cache.set(key, value);
        if (cache.size > MAX_CACHE_SIZE) {
            const oldest = cache.keys().next().value;
            cache.delete(oldest);
        }
    };

    const mountResults = (box, results, registerSelection) => {
        if (!box) return;
        if (!results.length) {
            box.innerHTML = templateEmpty;
            return;
        }

        const fragment = document.createDocumentFragment();
        results.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.innerHTML = `
                <strong>${item.nome}</strong>
                <div class="small text-muted">${item.uf_nome || item.uf}</div>`;
            button.addEventListener('click', () => registerSelection(item));
            fragment.appendChild(button);
        });

        box.innerHTML = '';
        box.appendChild(fragment);
    };

    const bindAutocomplete = (wrapper) => {
        const input = wrapper.querySelector('[data-autocomplete-input]');
        const resultsBox = wrapper.querySelector('[data-autocomplete-results]');
        const codeField = wrapper.querySelector('[data-autocomplete-code]');
        const labelField = wrapper.querySelector('[data-autocomplete-label]');

        if (!input || !resultsBox || !codeField) {
            return;
        }

        state.set(wrapper, { controller: null, results: [], debounce: null, cache: new Map() });

        const setSelection = (payload) => {
            codeField.value = payload?.codigo || '';
            if (labelField) {
                labelField.value = payload ? `${payload.nome} / ${payload.uf}` : '';
            }
            input.value = payload ? `${payload.nome} / ${payload.uf}` : '';
            closeResults(resultsBox);
        };

        input.addEventListener('focus', () => {
            if (resultsBox.hasAttribute('data-open') && resultsBox.children.length) {
                openResults(resultsBox);
            }
        });

        const lookup = (term) => {
            const current = state.get(wrapper);
            const cacheKey = term.toLowerCase();

            if (current.cache.has(cacheKey)) {
                const cached = current.cache.get(cacheKey);
                mountResults(resultsBox, cached, setSelection);
                openResults(resultsBox);
                return;
            }

            if (current.controller) {
                current.controller.abort();
            }
            const controller = new AbortController();
            current.controller = controller;
            resultsBox.innerHTML = templateLoading;
            openResults(resultsBox);

            fetchMunicipios(term)
                .then((items) => {
                    if (controller.signal.aborted) return;
                    current.results = items;
                    rememberResult(current.cache, cacheKey, items);
                    mountResults(resultsBox, items, setSelection);
                    openResults(resultsBox);
                })
                .catch(() => {
                    if (controller.signal.aborted) return;
                    resultsBox.innerHTML = templateError;
                    openResults(resultsBox);
                })
                .finally(() => {
                    if (!controller.signal.aborted) {
                        current.controller = null;
                    }
                });
        };

        input.addEventListener('input', (event) => {
            const term = event.target.value.trim();
            const current = state.get(wrapper);

            if (term.length < MIN_TERM) {
                codeField.value = '';
                if (labelField) labelField.value = '';
                current.debounce && clearTimeout(current.debounce);
                resultsBox.innerHTML = templateHint;
                openResults(resultsBox);
                return;
            }

            if (current.debounce) {
                clearTimeout(current.debounce);
            }
            current.debounce = setTimeout(() => lookup(term), DEBOUNCE_DELAY);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeResults(resultsBox);
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                closeResults(resultsBox);
            }
        });
    };

    moduleRoot.querySelectorAll('.livro-autocomplete').forEach(bindAutocomplete);
});
