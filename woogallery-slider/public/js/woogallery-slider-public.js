/**
 * WooGallery Slider Frontend Engine - Premium JS v1.0.4
 * Sử dụng Vanilla JS để tối ưu hóa hiệu năng
 */

    /**
     * Khởi tạo 1 gallery slider
     */
    function initSingleGallery(container) {
        if (!container || !window.Splide) return;

        // Nếu slider đã tồn tại trên container (do UX Builder re-render), hủy instance cũ trước
        if (container.splideMain) {
            try { container.splideMain.destroy(true); } catch (e) {}
            container.splideMain = null;
        }
        if (container.splideThumb) {
            try { container.splideThumb.destroy(true); } catch (e) {}
            container.splideThumb = null;
        }

        const sliderId = container.id.replace('woogallery-container-', '');
        
        // Đọc cấu hình JSON
        let settings = {
            thumbPosition: 'bottom',
            thumbWidth: 80,
            thumbHeight: 80,
            thumbGap: 12,
            hoverZoom: true,
            lightbox: true,
            autoplay: false,
            autoplaySpeed: 3000
        };

        try {
            if (container.dataset.settings) {
                settings = Object.assign(settings, JSON.parse(container.dataset.settings));
            }
        } catch (e) {
            console.error('Không thể parse dữ liệu cấu hình HKSlider', e);
        }

        // Khởi tạo các phần tử DOM Selector
        const mainEl = document.getElementById(`woogallery-main-${sliderId}`);
        const thumbsEl = document.getElementById(`woogallery-thumbs-${sliderId}`);

        if (!mainEl || !thumbsEl) return;

        const thumbW = settings.thumbWidth ? parseInt(settings.thumbWidth, 10) : 80;
        const thumbH = settings.thumbHeight ? parseInt(settings.thumbHeight, 10) : 80;
        const thumbGap = settings.thumbGap !== undefined ? parseInt(settings.thumbGap, 10) : 12;

        // Định dạng cấu hình Splide của Thumbnail
        let thumbOptions = {
            fixedWidth: thumbW,
            fixedHeight: thumbH,
            gap: thumbGap,
            rewind: true,
            pagination: false,
            arrows: false, // Tắt hoàn toàn nút mũi tên ở dải Thumbnail
            isNavigation: true,
            updateOnMove: true,
            focus: 'center',
            breakpoints: {
                768: {
                    fixedWidth: Math.min(thumbW, 64),
                    fixedHeight: Math.min(thumbH, 64),
                    gap: Math.min(thumbGap, 8),
                    direction: 'ltr',
                    height: 'auto'
                }
            }
        };

        // Nếu vị trí thumbnail là dọc (Left/Right) và màn hình đủ lớn
        if ((settings.thumbPosition === 'left' || settings.thumbPosition === 'right') && window.innerWidth >= 768) {
            thumbOptions.direction = 'ttb';
            thumbOptions.height = '100%';
            thumbOptions.autoHeight = true;
        } else {
            thumbOptions.direction = 'ltr';
            thumbOptions.height = 'auto';
        }

        // Khởi tạo Splide chính
        const mainSplide = new Splide(mainEl, {
            type: 'fade',
            rewind: true,
            speed: 500,
            transition: 'fade',
            waitForTransition: true,
            pagination: false,
            arrows: true,
            autoplay: settings.autoplay,
            interval: settings.autoplaySpeed,
        });

        // Khởi tạo Splide Thumbnail
        const thumbSplide = new Splide(thumbsEl, thumbOptions);

        // Đồng bộ hóa 2 chiều hai slide ảnh
        mainSplide.sync(thumbSplide);

        // Khởi động
        mainSplide.mount();
        thumbSplide.mount();

        // Gán Icon SVG mũi tên chuẩn (Trái/Phải) để tránh lỗi SVG mặc định của Splide bị lộn ngược
        const prevBtn = mainEl.querySelector('.splide__arrow--prev');
        const nextBtn = mainEl.querySelector('.splide__arrow--next');
        if (prevBtn) {
            prevBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
        }
        if (nextBtn) {
            nextBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        }

        // Sự kiện click nút Expand phóng to ở góc dưới bên trái ảnh chính
        const expandBtn = container.querySelector('.woogallery-expand-btn');
        if (expandBtn) {
            expandBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                openLightbox(mainSplide, mainSplide.index);
            });
        }

        // Lưu instance vào container element
        container.splideMain = mainSplide;
        container.splideThumb = thumbSplide;

        // -------------------------------------------------------------
        // 1. Tính năng Hover Zoom (Chỉ chạy khi bật cài đặt hoverZoom)
        // -------------------------------------------------------------
        if (settings.hoverZoom) {
            const zoomableSlides = mainEl.querySelectorAll('.woogallery-zoomable');
            
            zoomableSlides.forEach(function(slide) {
                const img = slide.querySelector('img');
                if (!img) return;
                
                slide.addEventListener('mousemove', function(e) {
                    const rect = slide.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const xPercent = (x / rect.width) * 100;
                    const yPercent = (y / rect.height) * 100;
                    
                    img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
                    img.style.transform = 'scale(1.6)';
                });
                
                slide.addEventListener('mouseleave', function() {
                    img.style.transform = 'scale(1)';
                    img.style.transformOrigin = 'center center';
                });
            });
        }

        // -------------------------------------------------------------
        // 2. Tính năng Lightbox (Chỉ chạy khi bật cài đặt lightbox)
        // -------------------------------------------------------------
        if (settings.lightbox) {
            const mainSlides = mainEl.querySelectorAll('.splide__slide');
            
            mainSlides.forEach(function(slide, index) {
                const inner = slide.querySelector('.woogallery-slide-inner');
                if (!inner) return;
                
                inner.addEventListener('click', function() {
                    openLightbox(mainSplide, index);
                });
            });
        }
    }

    /**
     * Khởi tạo tất cả gallery trên trang hoặc trong scope
     */
    function initAllGalleries(context) {
        const scope = context || document;
        const containers = scope.querySelectorAll ? scope.querySelectorAll('.woogallery-container') : [];
        containers.forEach(function(container) {
            initSingleGallery(container);
        });
    }

    // Khởi chạy khi DOM sẵn sàng
    initAllGalleries();

    // Hỗ trợ sự kiện cập nhật của Flatsome UX Builder
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('ux_builder_rendered flatsome-elm-added ux_builder_element_updated', function(e) {
            initAllGalleries(e.target || document);
        });
    }

    // Lắng nghe sự kiện tùy chỉnh
    window.addEventListener('woogallery_init', function() {
        initAllGalleries();
    });

    // Observer quan sát DOM tự động re-init khi UX Builder chèn HTML mới vào iframe
    if (window.parent !== window || document.body.classList.contains('ux-builder-active') || window.UXBuilder) {
        const observer = new MutationObserver(function(mutations) {
            let shouldInit = false;
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (node.classList.contains('woogallery-container') || node.querySelector('.woogallery-container'))) {
                        shouldInit = true;
                    }
                });
            });
            if (shouldInit) {
                initAllGalleries();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // -------------------------------------------------------------
    // ENGINE LIGHTBOX GLOBAL CODE (Singleton-like structure)
    // -------------------------------------------------------------
    let lightboxOverlay = null;
    let activeSplideInstance = null;
    let currentImageIndex = 0;

    function openLightbox(splideInstance, startIndex) {
        activeSplideInstance = splideInstance;
        currentImageIndex = startIndex;

        // Tạo Lightbox overlays nếu chưa tồn tại
        if (!lightboxOverlay) {
            lightboxOverlay = document.createElement('div');
            lightboxOverlay.className = 'woogallery-lightbox-overlay';
            lightboxOverlay.innerHTML = `
                <button class="woogallery-lightbox-btn woogallery-lightbox-close" aria-label="Đóng">&times;</button>
                <button class="woogallery-lightbox-btn woogallery-lightbox-prev" aria-label="Ảnh trước">&#10094;</button>
                <div class="woogallery-lightbox-content">
                    <img class="woogallery-lightbox-img" src="" alt="" />
                </div>
                <button class="woogallery-lightbox-btn woogallery-lightbox-next" aria-label="Ảnh sau">&#10095;</button>
                <div class="woogallery-lightbox-caption"></div>
            `;
            document.body.appendChild(lightboxOverlay);

            // Gán sự kiện cho các nút điều khiển
            lightboxOverlay.querySelector('.woogallery-lightbox-close').addEventListener('click', closeLightbox);
            lightboxOverlay.querySelector('.woogallery-lightbox-prev').addEventListener('click', prevLightboxImage);
            lightboxOverlay.querySelector('.woogallery-lightbox-next').addEventListener('click', nextLightboxImage);
            
            // Click ra ngoài ảnh để tắt
            lightboxOverlay.addEventListener('click', function(e) {
                if (e.target === lightboxOverlay || e.target.classList.contains('woogallery-lightbox-content')) {
                    closeLightbox();
                }
            });

            // Lắng nghe bàn phím
            document.addEventListener('keydown', handleLightboxKeyboard);
        }

        // Hiển thị Overlay
        lightboxOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Ngăn cuộn trang chính

        // Update dữ liệu hình ảnh
        updateLightboxData();
    }

    function updateLightboxData() {
        if (!lightboxOverlay || !activeSplideInstance) return;

        const slides = activeSplideInstance.Components.Slides.get();
        const activeSlideData = slides[currentImageIndex];
        
        if (!activeSlideData) return;

        const containerInner = activeSlideData.slide.querySelector('.woogallery-slide-inner');
        const fullImageSrc = containerInner.dataset.src;
        const imgAlt = containerInner.querySelector('img').alt || '';

        const lightboxImg = lightboxOverlay.querySelector('.woogallery-lightbox-img');
        const caption = lightboxOverlay.querySelector('.woogallery-lightbox-caption');

        lightboxImg.src = fullImageSrc;
        lightboxImg.alt = imgAlt;
        
        // Hiển thị vị trí ảnh (Ví dụ: 2 / 5)
        caption.textContent = `${currentImageIndex + 1} / ${slides.length}`;
        
        // Đồng bộ slide gốc ở trang chính đi theo lightbox
        activeSplideInstance.go(currentImageIndex);
    }

    function closeLightbox() {
        if (!lightboxOverlay) return;
        
        lightboxOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Trả lại cuộn trang chính
        activeSplideInstance = null;
    }

    function nextLightboxImage() {
        if (!activeSplideInstance) return;
        const total = activeSplideInstance.Components.Slides.getLength();
        currentImageIndex = (currentImageIndex + 1) % total;
        updateLightboxData();
    }

    function prevLightboxImage() {
        if (!activeSplideInstance) return;
        const total = activeSplideInstance.Components.Slides.getLength();
        currentImageIndex = (currentImageIndex - 1 + total) % total;
        updateLightboxData();
    }

    function handleLightboxKeyboard(e) {
        if (!lightboxOverlay || !lightboxOverlay.classList.contains('active')) return;

        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowRight') {
            nextLightboxImage();
        } else if (e.key === 'ArrowLeft') {
            prevLightboxImage();
        }
    }
// End of HKSlider script
