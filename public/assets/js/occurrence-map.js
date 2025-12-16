(function () {
    const canvas = document.getElementById('occurrence-map');
    if (!canvas) {
        return;
    }

    const endpoint = canvas.dataset.endpoint;
    if (!endpoint) {
        return;
    }

    const mapsEnabled = canvas.dataset.mapsEnabled === '1';
    const summaryEl = document.querySelector('[data-map-summary]');
    const listEl = document.querySelector('[data-map-top-list]');
    const statusEl = document.querySelector('[data-map-status]');
    const yearSelect = document.querySelector('[data-map-year-select]');
    const typeSelect = document.querySelector('[data-map-type-select]');
    const statusSelect = document.querySelector('[data-map-status-select]');
    const fallback = document.querySelector('[data-map-fallback]');
    const badgeSrc = canvas.dataset.badgeSrc || '';
    const badgeImageMarkup = badgeSrc
        ? `<img src="${badgeSrc}" alt="Brasão CBMRN" class="occurrence-map-infowindow-badge" loading="lazy">`
        : '';

    const state = {
        year: parseInt(canvas.dataset.initialYear || '', 10) || new Date().getFullYear(),
        tipo: '',
        status: '',
        items: [],
    };

    const syncFilterControls = () => {
        if (yearSelect) {
            yearSelect.value = String(state.year);
        }

        if (typeSelect) {
            typeSelect.value = state.tipo ? String(state.tipo) : '';
        }

        if (statusSelect) {
            statusSelect.value = state.status || '';
        }
    };

    let controller = null;
    let mapInstance = null;
    let infoWindow = null;
    let markers = [];
    let markerIndex = new Map();
    let mapsReady = false;

    const setStatus = (message, isError = false) => {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message;
        statusEl.classList.toggle('text-danger', isError);
        statusEl.classList.toggle('text-muted', !isError);
    };

    const formatTotal = (value) => `${value} ocorrência${value === 1 ? '' : 's'}`;

    const updateSummary = (payload) => {
        if (!summaryEl) {
            return;
        }

        const totalOcorrencias = (payload.items || []).reduce((acc, item) => acc + (item.total || 0), 0);
        const municipiosAtivos = payload.count || 0;

        summaryEl.innerHTML = `
            <p class="text-uppercase small text-muted mb-1">Ocorrências em ${payload.year}</p>
            <p class="display-6 fw-bold mb-1">${totalOcorrencias}</p>
            <p class="mb-0 text-muted small">${municipiosAtivos} município${municipiosAtivos === 1 ? '' : 's'} com registros.</p>
        `;
    };

    const registerListInteractions = () => {
        if (!listEl || !mapsEnabled) {
            return;
        }

        const candidates = listEl.querySelectorAll('[data-municipio-codigo]');
        candidates.forEach((element) => {
            const codigo = element.getAttribute('data-municipio-codigo');
            if (!codigo) {
                return;
            }

            const triggerHighlight = () => highlightMarker(codigo);
            element.addEventListener('mouseenter', triggerHighlight);
            element.addEventListener('focus', triggerHighlight);
            element.addEventListener('click', triggerHighlight);
        });
    };

    const updateTopList = (items) => {
        if (!listEl) {
            return;
        }

        if (!items.length) {
            listEl.innerHTML = '<li class="text-muted small">Nenhum município registrado para este ano.</li>';
            return;
        }

        const topItems = [...items]
            .sort((a, b) => (b.total || 0) - (a.total || 0))
            .slice(0, 3);

        listEl.innerHTML = topItems
            .map((item) => {
                const hasCodigo = Number.isInteger(item.codigo);
                const baseClass = 'occurrence-map-top-item';
                const itemClass = hasCodigo ? `${baseClass} ${baseClass}--interactive` : baseClass;
                const dataAttr = hasCodigo ? ` data-municipio-codigo="${item.codigo}"` : '';

                return `
                    <li class="${itemClass}"${dataAttr}>
                        <strong>${item.municipio}</strong>
                        <span class="text-muted small">${formatTotal(item.total)}</span>
                    </li>
                `;
            })
            .join('');

        registerListInteractions();
    };

    const clearMarkers = () => {
        markers.forEach((marker) => marker.setMap(null));
        markers = [];
        markerIndex = new Map();
    };

    const interpolateColor = (total) => {
        const value = Math.max(0, total || 0);

        if (value >= 10) {
            return { color: '#e81207', label: 'Alta', badgeClass: 'occurrence-map-badge-high' };
        }

        if (value >= 4) {
            return { color: '#fe7905ff', label: 'Moderada', badgeClass: 'occurrence-map-badge-medium' };
        }

        return { color: '#05b1fb', label: 'Baixa', badgeClass: 'occurrence-map-badge-low' };
    };

    const plotMarkers = (items) => {
        if (!mapsReady || !mapsEnabled || !mapInstance) {
            return;
        }

        clearMarkers();

        const withCoords = items.filter((item) =>
            typeof item.lat === 'number' && typeof item.lng === 'number'
        );

        if (!withCoords.length) {
            setStatus('Sem coordenadas para exibir no mapa neste ano.', false);
            return;
        }

        const bounds = new google.maps.LatLngBounds();
        const maxTotal = withCoords.reduce((acc, item) => Math.max(acc, item.total || 0), 0) || 1;

        withCoords.forEach((item) => {
            const weight = (item.total || 0) / maxTotal;
            const severity = interpolateColor(item.total);
            const marker = new google.maps.Marker({
                position: { lat: item.lat, lng: item.lng },
                map: mapInstance,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8 + weight * 10,
                    fillColor: severity.color,
                    fillOpacity: 0.9,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                },
            });

            marker.addListener('click', () => {
                if (!infoWindow) {
                    return;
                }
                infoWindow.setContent(`
                    <div class="occurrence-map-infowindow">
                       
                        <p class="occurrence-map-infowindow-header">CBMRN<br>2º Subgrupamento de Bombeiros Militar</p>
                        <strong>${item.municipio}</strong><br>
                        <span class="occurrence-map-badge ${severity.badgeClass}">${severity.label}</span>
                        <span class="text-muted small ms-2">${formatTotal(item.total)}</span>
                    </div>
                `);
                infoWindow.open({ anchor: marker, map: mapInstance, shouldFocus: false });
            });

            markers.push(marker);
            if (Number.isInteger(item.codigo)) {
                markerIndex.set(String(item.codigo), { marker, severity, data: item });
            }
            bounds.extend(marker.getPosition());
        });

        mapInstance.fitBounds(bounds, 48);
    };

    function highlightMarker(codigo) {
        if (!mapsEnabled || !mapsReady || !markerIndex || !google?.maps) {
            return;
        }

        const reference = markerIndex.get(String(codigo));
        if (!reference || !reference.marker) {
            return;
        }

        google.maps.event.trigger(reference.marker, 'click');

        if (google.maps.Animation) {
            reference.marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => {
                reference.marker?.setAnimation(null);
            }, 700);
        }
    }

    const buildRequest = ({ year, tipo, status }) => {
        if (controller) {
            controller.abort();
        }

        controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('year', year);

        if (tipo) {
            url.searchParams.set('tipo', tipo);
        }

        if (status) {
            url.searchParams.set('status', status);
        }

        const options = {
            headers: { Accept: 'application/json' },
        };

        if (controller) {
            options.signal = controller.signal;
        }

        return { url: url.toString(), options };
    };

    const refresh = async (overrides = {}) => {
        const nextState = {
            year: overrides.year ?? state.year,
            tipo: overrides.tipo ?? state.tipo,
            status: overrides.status ?? state.status,
        };

        setStatus('Carregando dados do mapa...');
        const request = buildRequest(nextState);

        try {
            const response = await fetch(request.url, request.options);
            if (!response.ok) {
                throw new Error('Falha ao carregar o mapa');
            }

            const payload = await response.json();
            state.year = payload.year;
            state.tipo = payload.filters?.tipo ?? nextState.tipo ?? '';
            state.status = payload.filters?.status ?? nextState.status ?? '';
            state.items = payload.items || [];

            syncFilterControls();

            updateSummary(payload);
            updateTopList(state.items);

            if (mapsEnabled && mapsReady) {
                plotMarkers(state.items);
            }

            const stamp = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            setStatus(`Atualizado às ${stamp}`);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            setStatus('Não foi possível carregar os dados agora.', true);
        }
    };

    window.initOccurrenceMap = () => {
        if (!mapsEnabled) {
            return;
        }

        mapInstance = new google.maps.Map(canvas, {
            center: { lat: -6.2, lng: -35.2 },
            zoom: 8,
            mapTypeControl: false,
            fullscreenControl: false,
            styles: [
                { elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
                { elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
                { elementType: 'labels.text.fill', stylers: [{ color: '#616161' }] },
                { elementType: 'labels.text.stroke', stylers: [{ color: '#f5f5f5' }] },
                {
                    featureType: 'administrative.land_parcel',
                    stylers: [{ visibility: 'off' }],
                },
                {
                    featureType: 'poi',
                    stylers: [{ visibility: 'off' }],
                },
                {
                    featureType: 'road',
                    stylers: [{ color: '#ffffff' }],
                },
                {
                    featureType: 'road.highway',
                    stylers: [{ color: '#f3f3f3' }],
                },
                {
                    featureType: 'water',
                    stylers: [{ color: '#c1d8ff' }],
                },
            ],
        });

        infoWindow = new google.maps.InfoWindow();
        mapsReady = true;

        if (state.items.length) {
            plotMarkers(state.items);
        }
    };

    if (!mapsEnabled) {
        fallback?.classList.remove('d-none');
    }

    if (mapsEnabled && window.google && window.google.maps && typeof window.initOccurrenceMap === 'function') {
        window.initOccurrenceMap();
    }

    yearSelect?.addEventListener('change', (event) => {
        const value = parseInt(event.target.value, 10);
        if (Number.isNaN(value)) {
            return;
        }
        refresh({ year: value });
    });

    typeSelect?.addEventListener('change', (event) => {
        const value = event.target.value || '';
        refresh({ tipo: value });
    });

    statusSelect?.addEventListener('change', (event) => {
        const value = event.target.value || '';
        refresh({ status: value });
    });

    refresh();
})();
