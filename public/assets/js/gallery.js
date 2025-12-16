document.addEventListener('DOMContentLoaded', () => {
    const dataNode = document.getElementById('gallery-data');
    let dataset = [];

    if (dataNode) {
        try {
            dataset = JSON.parse(dataNode.textContent || '[]');
        } catch (error) {
            console.error('Falha ao interpretar dados da galeria:', error);
            dataset = [];
        }
    }

    const lightbox = document.getElementById('galleryLightbox');
    const cards = document.querySelectorAll('[data-gallery-index]');

    if (!lightbox || cards.length === 0 || dataset.length === 0) {
        return;
    }

    const mediaImage = lightbox.querySelector('[data-gallery-image]');
    const titleNode = lightbox.querySelector('[data-gallery-title]');
    const descriptionNode = lightbox.querySelector('[data-gallery-description]');
    const categoryNode = lightbox.querySelector('[data-gallery-category]');
    const dateNode = lightbox.querySelector('[data-gallery-date]');
    const closeButtons = lightbox.querySelectorAll('[data-gallery-close]');
    const originalLink = lightbox.querySelector('[data-gallery-view-original]');
    const navPrev = lightbox.querySelector('[data-gallery-nav="prev"]');
    const navNext = lightbox.querySelector('[data-gallery-nav="next"]');

    let activeIndex = 0;

    const renderItem = (index) => {
        const item = dataset[index];
        if (!item) {
            return;
        }

        mediaImage.src = item.url;
        mediaImage.alt = item.title || 'Imagem da galeria';
        titleNode.textContent = item.title || 'Imagem sem título';
        descriptionNode.textContent = item.description || 'Sem descrição registrada.';
        categoryNode.textContent = item.category ? `Categoria: ${item.category}` : '';
        if (item.category_color) {
            categoryNode.style.color = item.category_color;
        }
        dateNode.textContent = item.uploaded_label ? `Registrada em ${item.uploaded_label}` : '';
        if (originalLink) {
            originalLink.href = item.url;
        }
    };

    const openLightbox = (index) => {
        activeIndex = index;
        renderItem(activeIndex);
        lightbox.hidden = false;
        document.body.classList.add('no-scroll');
        lightbox.focus({ preventScroll: true });
    };

    const closeLightbox = () => {
        lightbox.hidden = true;
        document.body.classList.remove('no-scroll');
    };

    const showSibling = (direction) => {
        const total = dataset.length;
        if (!total) {
            return;
        }
        activeIndex = (activeIndex + direction + total) % total;
        renderItem(activeIndex);
    };

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            const index = Number(card.dataset.galleryIndex || 0);
            openLightbox(index);
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const index = Number(card.dataset.galleryIndex || 0);
                openLightbox(index);
            }
        });
    });

    closeButtons.forEach((btn) => btn.addEventListener('click', closeLightbox));
    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox || event.target.hasAttribute('data-gallery-close')) {
            closeLightbox();
        }
    });

    if (navPrev) {
        navPrev.addEventListener('click', () => showSibling(-1));
    }
    if (navNext) {
        navNext.addEventListener('click', () => showSibling(1));
    }

    document.addEventListener('keydown', (event) => {
        if (lightbox.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showSibling(-1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showSibling(1);
        }
    });
});
