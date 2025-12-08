// admin.js

document.addEventListener("DOMContentLoaded", () => {
    const DESKTOP_BREAKPOINT = 992;
    const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT;
    
    // Quill Editor
    const editorField = document.querySelector('#conteudo');
    
    if (editorField && typeof Quill !== 'undefined') {
        console.log('[Quill] Inicializando editor...');
        
        // Cria um container para o Quill antes do textarea
        const container = document.createElement('div');
        container.id = 'conteudo-editor';
        container.style.minHeight = '400px';
        editorField.parentNode.insertBefore(container, editorField);
        const fieldWasRequired = editorField.hasAttribute('required');
        if (fieldWasRequired) {
            editorField.dataset.wasRequired = 'true';
            editorField.removeAttribute('required');
        }
        
        const quill = new Quill('#conteudo-editor', {
            theme: 'snow',
            placeholder: 'Escreva o conteúdo do post aqui...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });
        
        // Sincroniza Quill com textarea ao submeter formulário
        const form = editorField.closest('form');
        form?.classList.add('quill-enabled');
        if (form) {
            form.addEventListener('submit', (event) => {
                const plainText = quill.getText().trim();
                if (!plainText.length) {
                    event.preventDefault();
                    alert('Por favor, preencha o conteúdo do post antes de salvar.');
                    return;
                }
                editorField.value = quill.root.innerHTML;
            });
        }

        // Se houver conteúdo anterior, carrega no Quill
        if (editorField.value) {
            quill.root.innerHTML = editorField.value;
        }
        
        console.log('[Quill] Editor inicializado com sucesso');
    } else if (editorField) {
        console.warn('[Quill] Quill.js não carregado ou não disponível');
    }

    // AOS (scroll animation)
    AOS.init({ duration: 500, once: true });

    // Sidebar toggle (mobile & desktop)
    const sidebar = document.getElementById('adminSidebar') || document.querySelector('.admin-sidebar');
    const overlay = document.getElementById('sidebarOverlay') || document.getElementById('sidebarOverlay') || document.querySelector('.admin-sidebar-overlay');
    const toggle = document.getElementById('sidebarToggle');
    const contentArea = document.querySelector('.admin-content-area');

    const markRequiredLabels = () => {
        document.querySelectorAll('form [required]').forEach(field => {
            if (field.dataset.requiredIndicator === 'true') {
                return;
            }

            let label = null;
            if (field.id) {
                label = document.querySelector(`label[for="${field.id}"]`);
            }

            if (!label) {
                const wrapper = field.closest('.form-group, .mb-3, .form-floating, .col-12, .col-md-6, .col-lg-4, .row, .card-body');
                if (wrapper) {
                    label = wrapper.querySelector('label');
                }
            }

            if (label && !label.dataset.requiredIndicator) {
                const indicator = document.createElement('span');
                indicator.className = 'required-indicator';
                indicator.textContent = '*';
                label.appendChild(indicator);
                label.dataset.requiredIndicator = 'true';
            }

            field.dataset.requiredIndicator = 'true';
        });
    };

    markRequiredLabels();
    const requiredObserver = new MutationObserver(() => {
        markRequiredLabels();
    });
    requiredObserver.observe(document.body, { childList: true, subtree: true });

    function openSidebar() {
        console.log('Opening sidebar');
        sidebar?.classList.add('active');
        contentArea?.classList.add('sidebar-active');
        
        // Overlay e scroll lock apenas em mobile
        if (window.innerWidth < 992) {
            overlay?.classList.add('active');
            document.body.classList.add('sidebar-active');
        }
        
        localStorage.setItem('sidebarExpanded', 'true');
    }

    function closeSidebar() {
        console.log('Closing sidebar');
        sidebar?.classList.remove('active');
        contentArea?.classList.remove('sidebar-active');
        
        // Remove overlay e libera scroll apenas em mobile
        if (window.innerWidth < 992) {
            overlay?.classList.remove('active');
            document.body.classList.remove('sidebar-active');
        }
        
        localStorage.setItem('sidebarExpanded', 'false');
    }

    function toggleSidebar() {
        console.log('Toggling sidebar, current classes:', sidebar?.className);
        if (sidebar?.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    toggle?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Estado inicial: desktop aberto, mobile fechado
    if (isDesktop()) {
        openSidebar();
        overlay?.classList.remove('active');
        document.body.classList.remove('sidebar-active');
    } else {
        closeSidebar();
    }

    // Em resize, aplica regra
    window.addEventListener('resize', () => {
        if (isDesktop()) {
            openSidebar();
            overlay?.classList.remove('active');
            document.body.classList.remove('sidebar-active');
        } else {
            closeSidebar();
        }
    });

    // Close sidebar when navigation links are clicked on mobile (exclude dropdown toggles)
    const sidebarLinks = sidebar?.querySelectorAll('a:not([data-bs-toggle])');
    if (sidebarLinks?.length) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (!isDesktop()) {
                    setTimeout(() => closeSidebar(), 100);
                }
            });
        });
    }

    // Sidebar collapse toggle (desktop)
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminLayout = document.querySelector('.admin-layout');
    const updateCollapsedUI = (isCollapsed) => {
        if (!sidebar || !contentArea || !collapseBtn) return;

        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            contentArea.classList.add('collapsed');
            adminLayout?.classList.add('collapsed');
            collapseBtn.innerHTML = '<i class="bi bi-layout-sidebar-inset-reverse fs-5"></i>';
            collapseBtn.title = 'Expandir menu';
        } else {
            sidebar.classList.remove('collapsed');
            contentArea.classList.remove('collapsed');
            adminLayout?.classList.remove('collapsed');
            collapseBtn.innerHTML = '<i class="bi bi-layout-sidebar-inset fs-5"></i>';
            collapseBtn.title = 'Recolher menu';
        }
    };
    const applyStoredCollapse = () => {
        if (!collapseBtn) return;
        const shouldCollapse = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isDesktop() && shouldCollapse) {
            updateCollapsedUI(true);
        } else {
            updateCollapsedUI(false);
        }
    };
    
    if (collapseBtn) {
        collapseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!isDesktop()) return;

            const shouldCollapse = !sidebar?.classList.contains('collapsed');
            updateCollapsedUI(shouldCollapse);
            localStorage.setItem('sidebarCollapsed', shouldCollapse ? 'true' : 'false');
        });

        applyStoredCollapse();
        window.addEventListener('resize', applyStoredCollapse);
    }
    
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

    // CPF mask
    document.querySelectorAll('.cpf-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
            e.target.value = v;
        });
    });

    // Phone mask
    document.querySelectorAll('.phone-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            if (v.length >= 11) {
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length >= 6) {
                v = v.replace(/(\d{2})(\d{4})(\d)/, '($1) $2-$3');
            } else if (v.length >= 2) {
                v = v.replace(/(\d{2})(\d)/, '($1) $2');
            }
            e.target.value = v;
        });
    });

    // CNPJ mask
    document.querySelectorAll('.cnpj-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 14) v = v.slice(0, 14);
            v = v.replace(/(\d{2})(\d)/, '$1.$2');
            v = v.replace(/(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4');
            v = v.replace(/(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
            e.target.value = v;
        });
    });

    // Data mask DDMMYYYY
    document.querySelectorAll('.date-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 8) v = v.slice(0, 8);
            if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2);
            if (v.length >= 5) v = v.substring(0, 5) + '/' + v.substring(5);
            e.target.value = v;
        });
    });

    // Time mask HHMM
    document.querySelectorAll('.time-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 4) v = v.slice(0, 4);
            if (v.length >= 2) v = v.substring(0, 2) + ':' + v.substring(2);
            e.target.value = v;
        });
    });

    // Currency mask
    document.querySelectorAll('.currency-mask').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            v = (v / 100).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            e.target.value = v;
        });
    });
});
