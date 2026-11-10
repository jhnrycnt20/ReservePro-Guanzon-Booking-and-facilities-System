window.addEventListener('pageshow', (event) => {
    if (event.persisted && document.body.classList.contains('rp-page-payment-create')) {
        window.location.reload();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rp-booking-gallery]').forEach((gallery) => {
        const mainImage = gallery.querySelector('[data-rp-reservation-main]');
        const thumbnails = Array.from(gallery.querySelectorAll('[data-rp-reservation-photo]'));
        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                if (!mainImage || thumbnail.classList.contains('is-active')) return;
                const nextSource = thumbnail.dataset.rpReservationPhoto;
                if (!nextSource) return;

                mainImage.style.opacity = '0.7';
                const nextImage = new Image();
                nextImage.onload = () => {
                    mainImage.src = nextSource;
                    mainImage.style.opacity = '';
                };
                nextImage.onerror = () => { mainImage.style.opacity = ''; };
                nextImage.src = nextSource;

                thumbnails.forEach((item) => {
                    const isActive = item === thumbnail;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-pressed', String(isActive));
                });
            });
        });

        const openLightbox = gallery.querySelector('[data-rp-reservation-lightbox-open]');
        const lightbox = document.getElementById('rpReservationLightbox');
        const lightboxImage = lightbox?.querySelector('[data-rp-reservation-lightbox-image]');
        const lightboxStage = lightbox?.querySelector('[data-rp-reservation-lightbox-stage]');
        const lightboxCounter = lightbox?.querySelector('[data-rp-reservation-lightbox-counter]');
        const lightboxThumbs = Array.from(lightbox?.querySelectorAll('[data-rp-reservation-lightbox-thumb]') || []);
        const images = thumbnails.map((thumbnail) => thumbnail.dataset.rpReservationPhoto).filter(Boolean);
        if (images.length === 0 && mainImage?.src) images.push(mainImage.src);
        let lightboxIndex = 0;

        const showLightboxImage = (nextIndex) => {
            if (!images.length) return;
            lightboxIndex = (nextIndex + images.length) % images.length;
            const source = images[lightboxIndex];
            if (lightboxImage) lightboxImage.src = source;
            lightboxStage?.style.setProperty('--rp-lightbox-bg', `url("${source}")`);
            if (lightboxCounter) lightboxCounter.textContent = `${lightboxIndex + 1} / ${images.length}`;
            lightboxThumbs.forEach((thumbnail) => {
                const isActive = Number(thumbnail.dataset.rpReservationLightboxThumb) === lightboxIndex;
                thumbnail.classList.toggle('is-active', isActive);
                thumbnail.setAttribute('aria-pressed', String(isActive));
                if (isActive) {
                    thumbnail.scrollIntoView({
                        block: 'nearest',
                        inline: 'nearest',
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    });
                }
            });
        };

        openLightbox?.addEventListener('click', () => {
            const selectedIndex = thumbnails.findIndex((thumbnail) => thumbnail.classList.contains('is-active'));
            showLightboxImage(selectedIndex >= 0 ? selectedIndex : 0);
            if (lightbox && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(lightbox).show();
            }
        });
        lightbox?.querySelector('[data-rp-reservation-lightbox-prev]')?.addEventListener('click', () => showLightboxImage(lightboxIndex - 1));
        lightbox?.querySelector('[data-rp-reservation-lightbox-next]')?.addEventListener('click', () => showLightboxImage(lightboxIndex + 1));
        lightboxThumbs.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => showLightboxImage(Number(thumbnail.dataset.rpReservationLightboxThumb)));
        });
        lightbox?.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') showLightboxImage(lightboxIndex - 1);
            if (event.key === 'ArrowRight') showLightboxImage(lightboxIndex + 1);
        });
    });

    const themeToggle = document.querySelector('[data-rp-theme-toggle]');
    if (themeToggle) {
        const applyChartTheme = (dark) => {
            if (!window.Chart?.instances) return;
            const chartText = '#b8cbd0';
            const chartGrid = 'rgba(184, 203, 208, .16)';

            Object.values(window.Chart.instances).forEach((chart) => {
                if (!chart.canvas?.closest('.rp-staff-ui')) return;
                if (!chart.$rpLightPalette) {
                    chart.$rpLightPalette = chart.data.datasets.map((dataset) => ({
                        borderColor: dataset.borderColor,
                        backgroundColor: dataset.backgroundColor,
                    }));
                    chart.$rpLightScales = Object.fromEntries(
                        Object.entries(chart.options.scales || {}).map(([axis, scale]) => [axis, {
                            tickColor: scale.ticks?.color,
                            gridColor: scale.grid?.color,
                        }])
                    );
                    chart.$rpLightLegendColor = chart.options.plugins?.legend?.labels?.color;
                }

                chart.data.datasets.forEach((dataset, index) => {
                    if (!dark) {
                        Object.assign(dataset, chart.$rpLightPalette[index]);
                    } else if (chart.config.type === 'doughnut') {
                        dataset.backgroundColor = ['#87d5c8', '#d6bb82', '#71a8d6', '#c99acb'];
                    } else {
                        dataset.borderColor = index === 0 ? '#87d5c8' : '#d6bb82';
                        if (dataset.fill) {
                            const fill = chart.ctx.createLinearGradient(0, 0, 0, chart.height || 260);
                            fill.addColorStop(0, 'rgba(135, 213, 200, .24)');
                            fill.addColorStop(1, 'rgba(135, 213, 200, 0)');
                            dataset.backgroundColor = fill;
                        } else if (chart.config.type === 'bar') {
                            dataset.backgroundColor = '#87d5c8';
                        }
                    }
                });

                Object.entries(chart.options.scales || {}).forEach(([axis, scale]) => {
                    scale.ticks = { ...(scale.ticks || {}), color: dark ? chartText : chart.$rpLightScales[axis]?.tickColor };
                    scale.grid = { ...(scale.grid || {}), color: dark ? chartGrid : chart.$rpLightScales[axis]?.gridColor };
                });
                if (chart.options.plugins?.legend) {
                    chart.options.plugins.legend.labels = {
                        ...(chart.options.plugins.legend.labels || {}),
                        color: dark ? chartText : chart.$rpLightLegendColor,
                    };
                }
                chart.update('none');
            });
        };

        const syncThemeToggle = () => {
            const dark = document.documentElement.dataset.bsTheme === 'dark';
            const label = dark ? 'Switch to light mode' : 'Switch to dark mode';
            themeToggle.setAttribute('aria-checked', String(dark));
            themeToggle.setAttribute('aria-label', label);
            themeToggle.title = label;
            applyChartTheme(dark);
        };

        themeToggle.addEventListener('click', () => {
            const next = document.documentElement.dataset.bsTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.bsTheme = next;
            try { localStorage.setItem('rp-staff-theme', next); } catch (_) {}
            syncThemeToggle();
        });

        window.addEventListener('storage', (event) => {
            if (event.key !== 'rp-staff-theme') return;
            document.documentElement.dataset.bsTheme = event.newValue === 'dark' ? 'dark' : 'light';
            syncThemeToggle();
        });

        syncThemeToggle();
    }

    const publicNav = document.querySelector('.rp-public-nav');

    if (publicNav) {
        const hideAfter = 100;
        const scrollDelta = 6;
        const navOverlay = document.getElementById('rpNavOverlay');
        let navIsHidden = false;
        let lastScrollY = Math.max(0, window.scrollY);

        const setNavHidden = (hidden) => {
            if (hidden !== navIsHidden) {
                navIsHidden = hidden;
                publicNav.classList.toggle('rp-nav-hidden', hidden);
            }
        };

        const updateNavOnScroll = () => {
            const y = Math.max(0, window.scrollY);

            if (y <= hideAfter) {
                setNavHidden(false);
                lastScrollY = y;
                return;
            }

            if (Math.abs(y - lastScrollY) < scrollDelta) return;

            const menuOpen = navOverlay?.classList.contains('is-open');
            setNavHidden(y > lastScrollY && !menuOpen);
            lastScrollY = y;
        };

        updateNavOnScroll();
        window.addEventListener('scroll', updateNavOnScroll, { passive: true });
        publicNav.addEventListener('focusin', () => setNavHidden(false));
    }

    const galleryBannerBg = document.querySelector('.rp-gallery-banner-bg');

    if (galleryBannerBg) {
        const parallaxRatio = 0.12;
        const maxOffset = 24;
        let ticking = false;

        const updateParallax = () => {
            const rect = galleryBannerBg.parentElement.getBoundingClientRect();
            const offset = Math.max(-maxOffset, Math.min(maxOffset, -rect.top * parallaxRatio));
            galleryBannerBg.style.transform = `scale(1.15) translateY(${offset}px)`;
            ticking = false;
        };

        updateParallax();
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });
    }

    const galleryItems = document.querySelectorAll('#rpGalleryGrid .rp-gallery-item');
    const revealTargets = document.querySelectorAll('#rpGalleryGrid .rp-gallery-item, .rp-story-image');

    const galleryGrid = document.getElementById('rpGalleryGrid');
    if (galleryGrid) {
        const viewer = document.createElement('div');
        viewer.className = 'rp-gallery-viewer';
        viewer.hidden = true;
        viewer.innerHTML = `
            <div class="rp-gallery-viewer-panel" role="dialog" aria-modal="true" aria-label="Gallery image">
                <figure class="rp-gallery-viewer-figure">
                    <img class="rp-gallery-viewer-image" alt="">
                </figure>
            </div>`;
        document.body.appendChild(viewer);
        const viewerImage = viewer.querySelector('.rp-gallery-viewer-image');
        let lastTrigger = null;

        const closeViewer = () => {
            if (viewer.hidden) return;
            viewer.classList.remove('is-open');
            document.body.classList.remove('rp-gallery-viewer-open');
            window.setTimeout(() => {
                if (viewer.classList.contains('is-open')) return;
                viewer.hidden = true;
                viewerImage.removeAttribute('src');
                lastTrigger?.focus();
            }, 220);
        };

        const openViewer = (item) => {
            const image = item.querySelector('img');
            if (!image) return;
            lastTrigger = item;
            viewerImage.alt = image.alt || 'Gallery photo';
            viewerImage.src = image.currentSrc || image.src;
            viewer.hidden = false;
            document.body.classList.add('rp-gallery-viewer-open');
            requestAnimationFrame(() => {
                viewer.classList.add('is-open');
            });
        };

        galleryItems.forEach((item) => {
            item.tabIndex = 0;
            item.setAttribute('role', 'button');
            item.setAttribute('aria-label', `View ${item.querySelector('img')?.alt || 'gallery photo'} full size`);
            item.addEventListener('click', () => openViewer(item));
            item.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openViewer(item);
                }
            });
        });

        viewer.addEventListener('click', (event) => {
            if (event.target === viewer) closeViewer();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !viewer.hidden) closeViewer();
        });
    }

    if (revealTargets.length) {
        if ('IntersectionObserver' in window) {
            galleryItems.forEach((item, index) => {
                item.style.transitionDelay = `${(index % 4) * 0.08}s`;
            });

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

            revealTargets.forEach((item) => revealObserver.observe(item));
        } else {
            revealTargets.forEach((item) => item.classList.add('is-visible'));
        }
    }

    const offersContent = document.getElementById('rpOffersContent');
    const offersNextBtn = document.getElementById('rpOffersNextBtn');

    if (offersContent && offersNextBtn) {
        const offerRows = Array.from(offersContent.querySelectorAll('.rp-offer-row'));
        const pageSize = 3;
        const totalPages = Math.ceil(offerRows.length / pageSize);
        let currentPage = 0;

        const renderOffersPage = () => {
            offerRows.forEach((row, index) => {
                const page = Math.floor(index / pageSize);
                const isVisible = page === currentPage;
                row.classList.remove('is-visible', 'rp-offer-row--first', 'rp-offer-row--last');
                row.style.display = isVisible ? 'flex' : 'none';
            });

            const visibleRows = offerRows.filter((_, index) => Math.floor(index / pageSize) === currentPage);
            visibleRows[0]?.classList.add('rp-offer-row--first');
            visibleRows[visibleRows.length - 1]?.classList.add('rp-offer-row--last');

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    visibleRows.forEach((row) => row.classList.add('is-visible'));
                });
            });

        };

        offersNextBtn.addEventListener('click', () => {
            currentPage = (currentPage + 1) % totalPages;
            renderOffersPage();
        });

        if (totalPages <= 1) {
            offersNextBtn.style.display = 'none';
        }

        renderOffersPage();
    }

    const scrollTopBtn = document.getElementById('rpScrollTop');
    scrollTopBtn?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const planSlider = document.getElementById('rpPlanSlider');

    if (planSlider) {
        let isDown = false;
        let startX = 0;
        let startScroll = 0;
        let velocity = 0;
        let lastX = 0;
        let lastT = 0;
        let momentumId = null;

        const stopMomentum = () => {
            if (momentumId) cancelAnimationFrame(momentumId);
            momentumId = null;
        };

        const planTrack = planSlider.querySelector('.rp-plan-track');
        const originalCards = Array.from(planTrack?.children || []);
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let autoScrollId = null;
        let autoScrollLastTime = 0;
        let isSliderVisible = true;

        if (planTrack && originalCards.length) {
            originalCards.forEach((card) => {
                const clone = card.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.inert = true;
                planTrack.appendChild(clone);
            });

            const loopDistance = planTrack.children[originalCards.length].getBoundingClientRect().left
                - originalCards[0].getBoundingClientRect().left;
            const autoScroll = (time) => {
                if (!autoScrollLastTime) autoScrollLastTime = time;
                const elapsed = Math.min(time - autoScrollLastTime, 40);
                autoScrollLastTime = time;

                const paused = reducedMotion.matches || !isSliderVisible || isDown || momentumId
                    || planSlider.matches(':hover, :focus-within') || document.hidden;
                if (!paused) {
                    planSlider.scrollLeft += elapsed * 0.026;
                    if (planSlider.scrollLeft >= loopDistance) {
                        planSlider.scrollLeft -= loopDistance;
                    }
                }
                autoScrollId = requestAnimationFrame(autoScroll);
            };

            const startAutoScroll = () => {
                if (autoScrollId) return;
                autoScrollLastTime = 0;
                autoScrollId = requestAnimationFrame(autoScroll);
            };
            const stopAutoScroll = () => {
                if (autoScrollId) cancelAnimationFrame(autoScrollId);
                autoScrollId = null;
            };

            if ('IntersectionObserver' in window) {
                const sliderObserver = new IntersectionObserver(([entry]) => {
                    isSliderVisible = entry.isIntersecting;
                });
                sliderObserver.observe(planSlider);
            }
            reducedMotion.addEventListener?.('change', () => {
                if (reducedMotion.matches) stopAutoScroll();
                else startAutoScroll();
            });
            if (!reducedMotion.matches) startAutoScroll();
        }

        const runMomentum = () => {
            if (Math.abs(velocity) < 0.5) {
                stopMomentum();
                return;
            }
            planSlider.scrollLeft -= velocity;
            velocity *= 0.94;
            momentumId = requestAnimationFrame(runMomentum);
        };

        planSlider.addEventListener('pointerdown', (e) => {
            isDown = true;
            stopMomentum();
            planSlider.classList.add('is-dragging');
            startX = e.clientX;
            startScroll = planSlider.scrollLeft;
            lastX = e.clientX;
            lastT = performance.now();
            velocity = 0;
            planSlider.setPointerCapture(e.pointerId);
        });

        planSlider.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            planSlider.scrollLeft = startScroll - dx;

            const now = performance.now();
            const dt = now - lastT || 16;
            velocity = (e.clientX - lastX) / dt * 16;
            lastX = e.clientX;
            lastT = now;
        });

        const endDrag = (e) => {
            if (!isDown) return;
            isDown = false;
            planSlider.classList.remove('is-dragging');
            if (e?.pointerId !== undefined && planSlider.hasPointerCapture?.(e.pointerId)) {
                planSlider.releasePointerCapture(e.pointerId);
            }
            runMomentum();
        };

        planSlider.addEventListener('pointerup', endDrag);
        planSlider.addEventListener('pointercancel', endDrag);
        planSlider.addEventListener('pointerleave', endDrag);

        planSlider.querySelectorAll('a, img').forEach((el) => {
            el.addEventListener('dragstart', (e) => e.preventDefault());
        });
    }

    const sidebar = document.getElementById('rpSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const staffSidebar = document.body.classList.contains('rp-staff-ui');
    const desktopSidebar = window.matchMedia('(min-width: 992px)');
    const sidebarStorageKey = 'rp-staff-sidebar-collapsed';

    // Staff UI: neutralize Bootstrap scrollbar padding (document does not scroll).
    if (staffSidebar) {
        const clearModalShift = () => {
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
            document.querySelectorAll('.modal-open [style*="padding-right"]').forEach((el) => {
                if (el === document.body) return;
                if (el.classList.contains('modal')) return;
                el.style.removeProperty('padding-right');
            });
        };
        document.addEventListener('show.bs.modal', () => {
            requestAnimationFrame(clearModalShift);
        });
        document.addEventListener('shown.bs.modal', clearModalShift);
        document.addEventListener('hidden.bs.modal', clearModalShift);
    }

    if (staffSidebar && sidebar) {
        const sidebarNav = sidebar.querySelector('.rp-nav');
        sidebar.addEventListener('wheel', (event) => {
            if (!sidebarNav || sidebarNav.contains(event.target) || !event.deltaY) return;
            const distance = event.deltaY * (event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? sidebarNav.clientHeight : 1);
            sidebarNav.scrollTop += distance;
            event.preventDefault();
        }, { passive: false });
    }

    if (staffSidebar) {
        try {
            document.body.classList.toggle('rp-sidebar-collapsed', localStorage.getItem(sidebarStorageKey) === '1');
        } catch (_) {
            // The toggle still works when browser storage is unavailable.
        }
    }

    const syncSidebarToggle = () => {
        if (!toggle || !sidebar) return;
        const collapsed = staffSidebar && desktopSidebar.matches && document.body.classList.contains('rp-sidebar-collapsed');
        const expanded = desktopSidebar.matches ? !collapsed : sidebar.classList.contains('open');
        sidebar.inert = collapsed;
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', expanded ? 'Hide sidebar' : 'Show sidebar');
        toggle.title = expanded ? 'Hide sidebar' : 'Show sidebar';
    };

    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
        syncSidebarToggle();
    };

    toggle?.addEventListener('click', () => {
        if (staffSidebar && desktopSidebar.matches) {
            const collapsed = document.body.classList.toggle('rp-sidebar-collapsed');
            try {
                localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
            } catch (_) {
                // Keep the current page state without persistence.
            }
            syncSidebarToggle();
            return;
        }
        sidebar?.classList.toggle('open');
        backdrop?.classList.toggle('show');
        syncSidebarToggle();
    });

    backdrop?.addEventListener('click', closeSidebar);
    window.addEventListener('resize', () => {
        if (desktopSidebar.matches) closeSidebar();
        syncSidebarToggle();
    });
    syncSidebarToggle();

    document.querySelectorAll('[data-rp-history-back]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    });

    const loadHtml2Canvas = (() => {
        let loading = null;

        return () => {
            if (window.html2canvas) {
                return Promise.resolve(window.html2canvas);
            }

            if (loading) {
                return loading;
            }

            loading = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                script.async = true;
                script.onload = () => {
                    if (window.html2canvas) {
                        resolve(window.html2canvas);
                        return;
                    }
                    reject(new Error('html2canvas failed to load'));
                };
                script.onerror = () => reject(new Error('Could not load receipt saver'));
                document.head.appendChild(script);
            }).catch((error) => {
                loading = null;
                throw error;
            });

            return loading;
        };
    })();

    const receiptFileName = (target) => {
        const receiptText = target.querySelector('[data-receipt-field="receipt-number"]')?.textContent
            || Array.from(target.querySelectorAll('.rp-receipt-row strong'))
                .map((el) => el.textContent.trim())
                .find((text) => /^RCP-|^PAY-/i.test(text))
            || `receipt-${Date.now()}`;

        return `${String(receiptText).replace(/[^\w.-]+/g, '_')}.png`;
    };

    const showReceiptSavePreview = (dataUrl, filename) => {
        let overlay = document.getElementById('rpReceiptSaveOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'rpReceiptSaveOverlay';
            overlay.className = 'rp-receipt-save-overlay';
            overlay.innerHTML = `
                <div class="rp-receipt-save-sheet" role="dialog" aria-modal="true" aria-labelledby="rpReceiptSaveTitle">
                    <div class="rp-receipt-save-header">
                        <h2 id="rpReceiptSaveTitle" class="rp-receipt-save-title">Save receipt</h2>
                        <button type="button" class="btn-close" data-rp-receipt-save-close aria-label="Close"></button>
                    </div>
                    <p class="rp-receipt-save-hint">Long-press the image, then choose <strong>Save Image</strong> / <strong>Add to Photos</strong>.</p>
                    <img class="rp-receipt-save-image" alt="Payment receipt">
                    <div class="rp-receipt-save-actions">
                        <a class="btn btn-rp-primary" data-rp-receipt-save-link download>Download PNG</a>
                        <button type="button" class="btn btn-rp-soft" data-rp-receipt-save-close>Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            overlay.addEventListener('click', (event) => {
                if (event.target === overlay || event.target.closest('[data-rp-receipt-save-close]')) {
                    overlay.classList.remove('is-open');
                    document.body.classList.remove('rp-receipt-save-open');
                }
            });
        }

        const image = overlay.querySelector('.rp-receipt-save-image');
        const link = overlay.querySelector('[data-rp-receipt-save-link]');
        image.src = dataUrl;
        link.href = dataUrl;
        link.download = filename;
        overlay.classList.add('is-open');
        document.body.classList.add('rp-receipt-save-open');
    };

    const saveReceiptImage = async (blob, filename) => {
        const file = new File([blob], filename, { type: 'image/png' });
        const canShareFiles = typeof navigator.canShare === 'function'
            && navigator.canShare({ files: [file] });

        if (canShareFiles) {
            try {
                await navigator.share({
                    files: [file],
                    title: 'Payment receipt',
                    text: 'Guanzon Beach payment receipt',
                });
                return 'shared';
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return 'cancelled';
                }
            }
        }

        const dataUrl = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });

        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent)
            || (navigator.maxTouchPoints > 1 && /Macintosh/i.test(navigator.userAgent));

        if (isMobile) {
            showReceiptSavePreview(dataUrl, filename);
            return 'preview';
        }

        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        return 'downloaded';
    };

    document.querySelectorAll('[data-rp-print-receipt]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.dataset.rpPrintReceipt || '#rpPaymentReceiptPrint');
            if (!target) return;

            const originalLabel = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving…';

            try {
                const html2canvas = await loadHtml2Canvas();

                // Capture a detached clone so Bootstrap modal transforms do not blank the image.
                const host = document.createElement('div');
                host.setAttribute('aria-hidden', 'true');
                host.style.cssText = 'position:fixed;left:-10000px;top:0;width:360px;padding:0;margin:0;background:#fff;z-index:-1;pointer-events:none;';
            const clone = target.cloneNode(true);
                clone.id = '';
                clone.style.width = '360px';
                clone.style.maxWidth = '360px';
                clone.style.margin = '0';
                clone.style.boxShadow = 'none';
                host.appendChild(clone);
                document.body.appendChild(host);

                let canvas;
                try {
                    canvas = await html2canvas(clone, {
                        backgroundColor: '#ffffff',
                        scale: Math.min(3, window.devicePixelRatio || 2),
                        useCORS: true,
                        logging: false,
                        width: clone.offsetWidth,
                        windowWidth: clone.offsetWidth,
                    });
                } finally {
                    host.remove();
                }

                const blob = await new Promise((resolve, reject) => {
                    canvas.toBlob((result) => {
                        if (result) {
                            resolve(result);
                return;
            }
                        reject(new Error('Could not create receipt image'));
                    }, 'image/png');
                });

                await saveReceiptImage(blob, receiptFileName(target));
            } catch (error) {
                console.error(error);
                window.alert('Could not save the receipt. Please try again.');
            } finally {
                button.disabled = false;
                button.innerHTML = originalLabel;
            }
        });
    });

    document.querySelectorAll('[data-rp-payment-form]').forEach((form) => {
        const amountInput = form.querySelector('#paymentAmount');
        const sampleForm = form.closest('.rp-payment-create-card')?.querySelector('.rp-payment-sample-form');
        const sampleAmount = sampleForm?.querySelector('[data-rp-sample-amount]');
        const minAmount = Number(form.dataset.rpMinAmount || 0);
        const maxAmount = Number(form.dataset.rpMaxAmount || 0);
        const methodSelect = form.querySelector('[name="payment_method"]');
        const refWrap = form.querySelector('[data-rp-pay-ref-wrap]');
        const refInput = refWrap?.querySelector('[name="reference_number"]');

        const syncPaymentRefVisibility = () => {
            if (!methodSelect || !refWrap) return;
            const needsRef = methodSelect.value === 'gcash' || methodSelect.value === 'bank_transfer';
            refWrap.hidden = !needsRef;
            if (refInput) {
                refInput.required = needsRef;
                if (!needsRef) {
                    refInput.value = '';
                    refInput.setCustomValidity('');
                }
            }
        };

        methodSelect?.addEventListener('change', syncPaymentRefVisibility);
        syncPaymentRefVisibility();

        sampleForm?.addEventListener('submit', () => {
            if (amountInput && sampleAmount) sampleAmount.value = amountInput.value;
        });

        form.querySelectorAll('[data-rp-pay-amount]').forEach((button) => {
            button.addEventListener('click', () => {
                if (amountInput) {
                    const selectedAmount = Number(button.dataset.rpPayAmount);
                    amountInput.value = Number.isFinite(selectedAmount)
                        ? selectedAmount.toFixed(2)
                        : (button.dataset.rpPayAmount || '');
                    amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                    amountInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                form.querySelectorAll('[data-rp-pay-amount]').forEach((choice) => {
                    choice.classList.toggle('is-selected', choice === button);
                });
            });
        });

        form.addEventListener('submit', (event) => {
            if (!amountInput) {
                return;
            }

            const amount = Number(String(amountInput.value || '').replace(/,/g, ''));
            if (!Number.isFinite(amount)) {
                return;
            }

            if (minAmount > 0 && amount + 0.009 < minAmount) {
                event.preventDefault();
                amountInput.setCustomValidity(`Minimum payment is ₱${minAmount.toFixed(2)} (50% deposit).`);
                amountInput.reportValidity();
                return;
            }

            if (maxAmount > 0 && amount - 0.009 > maxAmount) {
                event.preventDefault();
                amountInput.setCustomValidity(`Amount cannot exceed ₱${maxAmount.toFixed(2)}.`);
                amountInput.reportValidity();
                return;
            }

            amountInput.setCustomValidity('');
        });

        amountInput?.addEventListener('input', () => {
            amountInput.setCustomValidity('');
        });
    });

    const moneyLabel = (value) => `₱${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;

    document.querySelectorAll('[data-rp-walkin-form]').forEach((form) => {
        const accommodation = form.querySelector('[data-rp-capacity-select]');
        const checkIn = form.querySelector('[data-stay-check-in]');
        const checkOut = form.querySelector('[data-stay-check-out]');
        const totalEl = form.querySelector('[data-rp-walkin-total]');
        const payRoot = form.querySelector('[data-rp-walkin-payments]');
        const rowsWrap = form.querySelector('[data-rp-walkin-pay-rows]');
        const template = form.querySelector('[data-rp-walkin-pay-template]');
        const addBtn = form.querySelector('[data-rp-walkin-add-pay]');
        const summaryEl = form.querySelector('[data-rp-walkin-pay-summary]');
        const summaryTextEl = form.querySelector('[data-rp-walkin-pay-summary-text]');
        const summaryIconEl = summaryEl?.querySelector('i');
        const maxRows = 3;
        let stayTotal = 0;

        const rows = () => [...(rowsWrap?.querySelectorAll('[data-rp-walkin-pay-row]') || [])];

        const reindexRows = () => {
            rows().forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name.replace(/payments\[(?:\d+|__INDEX__)\]/, `payments[${index}]`);
                });
            });
            if (addBtn) {
                addBtn.disabled = rows().length >= maxRows;
            }
            rows().forEach((row) => {
                const removeBtn = row.querySelector('[data-rp-walkin-remove-pay]');
                if (removeBtn) removeBtn.disabled = rows().length <= 1;
            });
        };

        const clearFieldError = (input) => {
            if (!input) return;
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            const field = input.closest('.rp-walkin-pay-field') || input.parentElement;
            field?.querySelectorAll('.invalid-feedback').forEach((el) => {
                el.remove();
            });
        };

        const syncRowRef = (row) => {
            const method = row.querySelector('[data-rp-walkin-pay-method]');
            const refWrap = row.querySelector('[data-rp-walkin-pay-ref-wrap]');
            const refInput = row.querySelector('[data-rp-walkin-pay-ref]');
            if (!method || !refWrap || !refInput) return;
            const needsRef = method.value === 'gcash';
            refWrap.hidden = !needsRef;
            row.classList.toggle('has-ref', needsRef);
            refInput.required = needsRef;
            if (!needsRef) {
                refInput.value = '';
                clearFieldError(refInput);
            } else if ((refInput.value || '').trim() !== '') {
                clearFieldError(refInput);
            }
        };

        const enteredTotal = () => rows().reduce((sum, row) => {
            const amount = Number(row.querySelector('[data-rp-walkin-pay-amount]')?.value || 0);
            return sum + (Number.isFinite(amount) ? amount : 0);
        }, 0);

        const setSummary = (text, state) => {
            if (!summaryEl) return;
            if (summaryTextEl) summaryTextEl.textContent = text;
            else summaryEl.textContent = text;
            summaryEl.classList.remove('is-ok', 'is-bad', 'is-pending');
            summaryEl.classList.add(state);
            if (summaryIconEl) {
                summaryIconEl.className = state === 'is-ok'
                    ? 'bi bi-check-circle-fill'
                    : (state === 'is-bad' ? 'bi bi-exclamation-circle-fill' : 'bi bi-info-circle');
            }
        };

        const updateSummary = () => {
            const paid = enteredTotal();
            const ok = stayTotal > 0 && Math.abs(paid - stayTotal) <= 0.009;
            const bad = stayTotal > 0 && !ok;
            setSummary(
                `Entered: ${moneyLabel(paid)} / ${moneyLabel(stayTotal)}${ok ? ' ✓' : ''}`,
                ok ? 'is-ok' : (bad ? 'is-bad' : 'is-pending')
            );
        };

        const fillSingleRowToTotal = () => {
            const list = rows();
            if (list.length === 1 && stayTotal > 0) {
                const amountInput = list[0].querySelector('[data-rp-walkin-pay-amount]');
                if (amountInput) {
                    amountInput.value = stayTotal.toFixed(2);
                    delete amountInput.dataset.rpUserEdited;
                }
            }
            updateSummary();
        };

        const updateStayTotal = () => {
            if (!accommodation || !checkIn || !checkOut || !totalEl) return;
            const start = new Date(`${checkIn.value}T00:00:00`);
            const end = new Date(`${checkOut.value}T00:00:00`);
            const nights = Math.round((end - start) / 86400000);
            const rate = Number(accommodation.selectedOptions[0]?.dataset.rate || 0);
            stayTotal = nights > 0 && rate > 0 ? nights * rate : 0;
            totalEl.textContent = stayTotal > 0 ? moneyLabel(stayTotal) : '—';
            fillSingleRowToTotal();
        };

        const bindRow = (row) => {
            row.querySelector('[data-rp-walkin-pay-method]')?.addEventListener('change', () => {
                syncRowRef(row);
            });
            const amountInput = row.querySelector('[data-rp-walkin-pay-amount]');
            amountInput?.addEventListener('input', () => {
                amountInput.dataset.rpUserEdited = '1';
                clearFieldError(amountInput);
                updateSummary();
            });
            const refInput = row.querySelector('[data-rp-walkin-pay-ref]');
            refInput?.addEventListener('input', () => {
                clearFieldError(refInput);
            });
            row.querySelector('[data-rp-walkin-remove-pay]')?.addEventListener('click', () => {
                if (rows().length <= 1) return;
                row.remove();
                reindexRows();
                updateSummary();
            });
            syncRowRef(row);
        };

        addBtn?.addEventListener('click', () => {
            if (!rowsWrap || !template || rows().length >= maxRows) return;
            const html = template.innerHTML.replaceAll('__INDEX__', String(rows().length));
            const holder = document.createElement('div');
            holder.innerHTML = html.trim();
            const row = holder.firstElementChild;
            if (!row) return;
            rowsWrap.appendChild(row);
            bindRow(row);
            reindexRows();
            updateSummary();
        });

        rows().forEach(bindRow);
        reindexRows();

        accommodation?.addEventListener('change', updateStayTotal);
        checkIn?.addEventListener('change', updateStayTotal);
        checkOut?.addEventListener('change', updateStayTotal);
        updateStayTotal();

        form.addEventListener('submit', (event) => {
            reindexRows();

            for (const row of rows()) {
                const method = row.querySelector('[data-rp-walkin-pay-method]')?.value;
                const refInput = row.querySelector('[data-rp-walkin-pay-ref]');
                if (method !== 'gcash' || !refInput) continue;

                const ref = String(refInput.value || '').trim();
                refInput.value = ref;
                if (ref === '') {
                    event.preventDefault();
                    clearFieldError(refInput);
                    refInput.classList.add('is-invalid');
                    refInput.setCustomValidity('Reference number is required for GCash.');
                    refInput.reportValidity();
                    refInput.focus();
                    return;
                }
                clearFieldError(refInput);
            }

            const paid = enteredTotal();
            if (stayTotal <= 0) {
                event.preventDefault();
                setSummary('Select room and dates to calculate the full stay total.', 'is-bad');
                return;
            }
            if (Math.abs(paid - stayTotal) > 0.009) {
                event.preventDefault();
                setSummary(`Entered: ${moneyLabel(paid)} / ${moneyLabel(stayTotal)} — must match full total.`, 'is-bad');
                const firstAmount = form.querySelector('[data-rp-walkin-pay-amount]');
                firstAmount?.setCustomValidity(`Payments must total ${moneyLabel(stayTotal)}.`);
                firstAmount?.reportValidity();
                firstAmount?.setCustomValidity('');
            }
        });
    });

    document.querySelectorAll('[data-rp-filter-compact]').forEach((wrap) => {
        const toggle = wrap.querySelector('[data-rp-filter-toggle]');
        const panel = wrap.querySelector('[data-rp-filter-panel]');
        if (!toggle || !panel) return;

        const setOpen = (open) => {
            wrap.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = !open;
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(panel.hidden);
        });

        panel.querySelector('[data-rp-filter-close]')?.addEventListener('click', () => {
            setOpen(false);
            toggle.focus();
        });

        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) {
                setOpen(false);
                toggle.focus();
            }
        });
    });

    document.querySelectorAll('[data-rp-live-filter]').forEach((form) => {
        let timer = null;
        const applyFilter = () => {
            const rawAction = form.getAttribute('action') || window.location.pathname;
            const hashFromAction = rawAction.includes('#') ? rawAction.slice(rawAction.indexOf('#')) : '';
            const action = rawAction.replace(/#.*$/, '') || window.location.pathname;
            const hash = form.dataset.rpLiveFilterHash
                ? `#${form.dataset.rpLiveFilterHash.replace(/^#/, '')}`
                : hashFromAction;
            const params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach((key) => {
                if (!(params.get(key) || '').trim()) {
                    params.delete(key);
                }
            });
            const query = params.toString();
            window.location.assign(`${action}${query ? `?${query}` : ''}${hash}`);
        };

        form.querySelectorAll('[data-rp-live-filter-q]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(timer);
                const delay = input.value.trim() === '' ? 0 : 300;
                timer = setTimeout(applyFilter, delay);
            });
        });

        form.querySelectorAll('[data-rp-live-filter-change]').forEach((select) => {
            select.addEventListener('change', () => {
                clearTimeout(timer);
                applyFilter();
            });
        });
    });

    const navMenuBtn = document.getElementById('rpNavMenuBtn');
    const navOverlay = document.getElementById('rpNavOverlay');
    let navScrollY = 0;

    const closeNavOverlay = () => {
        navMenuBtn?.classList.remove('is-open');
        navOverlay?.classList.remove('is-open');
        navMenuBtn?.setAttribute('aria-expanded', 'false');
        window.scrollTo(0, navScrollY);
    };

    navMenuBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        navScrollY = window.scrollY;
        const isOpen = navMenuBtn.classList.toggle('is-open');
        navOverlay?.classList.toggle('is-open', isOpen);
        navMenuBtn.setAttribute('aria-expanded', String(isOpen));
        window.scrollTo(0, navScrollY);
    });

    navOverlay?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeNavOverlay);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeNavOverlay();
    });

    document.addEventListener('click', (e) => {
        if (!navOverlay?.classList.contains('is-open')) return;
        if (navOverlay.contains(e.target) || navMenuBtn?.contains(e.target)) return;
        closeNavOverlay();
    });

    const termsModalEl = document.getElementById('rpTermsModal');
    const termsModalBody = document.querySelector('[data-rp-terms-modal-body]');
    let termsScrollTarget = null;

    document.querySelectorAll('[data-rp-terms-anchor]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            termsScrollTarget = trigger.getAttribute('data-rp-terms-anchor');
        });
    });

    termsModalEl?.addEventListener('shown.bs.modal', () => {
        if (!termsScrollTarget || !termsModalBody) return;
        const target = document.getElementById(termsScrollTarget);
        if (target) {
            termsModalBody.scrollTop = target.offsetTop - termsModalBody.offsetTop;
        }
        termsScrollTarget = null;
    });

    termsModalEl?.addEventListener('hidden.bs.modal', () => {
        if (termsModalBody) termsModalBody.scrollTop = 0;
    });

    const loginModalEl = document.getElementById('rpLoginModal');
    const registerModalEl = document.getElementById('rpRegisterModal');
    const authModals = [loginModalEl, registerModalEl].filter(Boolean);
    let openAuthModalCount = 0;

    authModals.forEach((modalEl) => {
        modalEl.addEventListener('show.bs.modal', () => {
            openAuthModalCount += 1;
            document.body.classList.add('rp-login-modal-open');
            authModals.forEach((other) => {
                if (other !== modalEl && other.classList.contains('show') && window.bootstrap) {
                    bootstrap.Modal.getInstance(other)?.hide();
                }
            });
        });
        modalEl.addEventListener('hidden.bs.modal', () => {
            openAuthModalCount = Math.max(0, openAuthModalCount - 1);
            if (openAuthModalCount === 0) {
                document.body.classList.remove('rp-login-modal-open');
            }
        });
    });

    const authQuery = new URLSearchParams(window.location.search);
    const wantsLoginModal = loginModalEl?.getAttribute('data-rp-autoshow') === '1' || authQuery.get('login') === '1';
    const wantsRegisterModal = registerModalEl?.getAttribute('data-rp-autoshow') === '1' || authQuery.get('signup') === '1';

    if (authQuery.has('login') || authQuery.has('signup')) {
        authQuery.delete('login');
        authQuery.delete('signup');
        const cleanQuery = authQuery.toString();
        const cleanUrl = window.location.pathname + (cleanQuery ? `?${cleanQuery}` : '') + window.location.hash;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    if (wantsLoginModal && window.bootstrap) {
        new bootstrap.Modal(loginModalEl).show();
    } else if (wantsRegisterModal && window.bootstrap) {
        new bootstrap.Modal(registerModalEl).show();
    }

    const toastEl = document.getElementById('rpToast');
    if (toastEl && window.bootstrap) {
        new bootstrap.Toast(toastEl).show();
    }

    document.querySelectorAll('[data-rp-auto-dismiss]').forEach((alertEl) => {
        let dismissed = false;
        const dismiss = () => {
            if (dismissed || !alertEl.isConnected) return;
            dismissed = true;
            if (!window.bootstrap?.Alert) {
                alertEl.remove();
                return;
            }
            bootstrap.Alert.getOrCreateInstance(alertEl).close();
        };
        alertEl.querySelector('[data-bs-dismiss="alert"]')?.addEventListener('click', dismiss);
        const dismissDelay = Number(alertEl.dataset.rpAutoDismiss);
        setTimeout(dismiss, Number.isFinite(dismissDelay) && dismissDelay > 0 ? dismissDelay : 4000);
    });

    document.querySelectorAll('[data-rp-notif]').forEach((root) => {
        const toggle = root.querySelector('[data-rp-notif-toggle]');
        const panel = root.querySelector('[data-rp-notif-panel]');
        if (!toggle || !panel) return;

        let open = false;
        let lastToggleAt = 0;

        const setOpen = (next) => {
            open = Boolean(next);
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            root.classList.toggle('is-open', open);
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const now = Date.now();
            // Ignore accidental double-clicks that fight open/close.
            if (now - lastToggleAt < 280) return;
            lastToggleAt = now;
            setOpen(!open);
        });

        panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            if (open) setOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) setOpen(false);
        });
    });

    const confirmModalEl = document.getElementById('rpConfirmModal');
    let pendingConfirmAction = null;

    const askConfirm = (message, onConfirm) => {
        if (!confirmModalEl || !window.bootstrap) {
            if (window.confirm(message)) onConfirm();
            return;
        }
        const messageEl = confirmModalEl.querySelector('[data-rp-confirm-message]');
        if (messageEl) messageEl.textContent = message;
        pendingConfirmAction = onConfirm;
        bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
    };

    confirmModalEl?.querySelector('[data-rp-confirm-accept]')?.addEventListener('click', () => {
        const action = pendingConfirmAction;
        pendingConfirmAction = null;
        bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
        if (typeof action === 'function') action();
    });

    const noticeModalEl = document.getElementById('rpNoticeModal');
    const askNotice = (message, title = 'Unavailable') => {
        if (!noticeModalEl || !window.bootstrap) {
            window.alert(message);
            return;
        }
        const titleEl = noticeModalEl.querySelector('#rpNoticeModalLabel');
        const messageEl = noticeModalEl.querySelector('[data-rp-notice-message]');
        const iconEl = noticeModalEl.querySelector('[data-rp-notice-icon]');
        const isSuccess = String(title).toLowerCase() === 'success';
        noticeModalEl.classList.toggle('is-success', isSuccess);
        if (iconEl) iconEl.innerHTML = isSuccess ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-info-lg"></i>';
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;
        bootstrap.Modal.getOrCreateInstance(noticeModalEl).show();
    };

    document.querySelectorAll('[data-rp-blocked-click]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            askNotice(
                el.dataset.rpBlockedClick || 'This option is currently unavailable.',
                el.dataset.rpBlockedTitle || 'Unavailable'
            );
        });
    });

    document.querySelectorAll('form[data-rp-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.rpConfirmed === '1') {
                delete form.dataset.rpConfirmed;
                return;
            }
            event.preventDefault();
            askConfirm(form.dataset.rpConfirm || 'Are you sure?', () => {
                form.dataset.rpConfirmed = '1';
                form.requestSubmit();
            });
        });
    });

    document.querySelectorAll('[data-rp-confirm-click]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const form = button.closest('form');
            if (!form) return;
            if (form.dataset.rpConfirmed === '1') {
                delete form.dataset.rpConfirmed;
                return;
            }
            event.preventDefault();
            askConfirm(button.dataset.rpConfirmClick || 'Are you sure?', () => {
                form.dataset.rpConfirmed = '1';
                form.requestSubmit();
            });
        });
    });

    const checkIn = document.querySelector('[data-calc-check-in]');
    const checkOut = document.querySelector('[data-calc-check-out]');
    const rate = document.querySelector('[data-calc-rate]');
    const total = document.querySelector('[data-calc-total]');

    const calcTotal = () => {
        if (!checkIn || !checkOut || !rate || !total) return;
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end <= start) {
            total.textContent = '—';
            return;
        }
        const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        const amount = nights * parseFloat(rate.dataset.calcRate || rate.value || '0');
        total.textContent = `₱${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (estimate)`;
    };

    checkIn?.addEventListener('change', calcTotal);
    checkOut?.addEventListener('change', calcTotal);

    const resolveCapacity = (form) => {
        const totalEl = form.querySelector('[data-rp-guest-total]');
        if (totalEl?.dataset.rpCapacity) {
            return parseInt(totalEl.dataset.rpCapacity, 10) || 0;
        }
        const select = form.querySelector('[data-rp-capacity-select]');
        const option = select?.selectedOptions?.[0];
        return parseInt(option?.dataset.capacity || '0', 10) || 0;
    };

    const syncGuestCapacity = (form) => {
        const adultsEl = form.querySelector('[data-rp-guest-adults]');
        const childrenEl = form.querySelector('[data-rp-guest-children]');
        const totalEl = form.querySelector('[data-rp-guest-total]');
        const errorEl = form.querySelector('[data-rp-capacity-error]');
        if (!adultsEl || !childrenEl || !totalEl) return true;

        const capacity = resolveCapacity(form);
        let adults = Math.max(1, parseInt(adultsEl.value || '1', 10) || 1);
        if (capacity > 0 && adults > capacity) {
            adults = capacity;
            adultsEl.value = String(adults);
        }

        // Children is optional — blank means 0 (e.g. adults already fill capacity).
        childrenEl.required = false;
        const remaining = capacity > 0 ? Math.max(0, capacity - adults) : null;
        let children = Math.max(0, parseInt(childrenEl.value || '0', 10) || 0);
        if (remaining !== null && children > remaining) {
            children = remaining;
            childrenEl.value = String(children);
        }

        const total = adults + children;
        totalEl.value = String(total);

        if (capacity > 0) {
            adultsEl.max = String(capacity);
            childrenEl.max = String(remaining);
            totalEl.max = String(capacity);
        }

        const over = capacity > 0 && total > capacity;
        if (errorEl) {
            errorEl.classList.toggle('d-none', !over);
            errorEl.textContent = over
                ? `Total guests (${total}) exceeds capacity of ${capacity}.`
                : 'Total guests cannot exceed capacity.';
        }
        adultsEl.classList.toggle('is-invalid', over);
        childrenEl.classList.toggle('is-invalid', over);
        totalEl.classList.toggle('is-invalid', over);

        const submitBtn = form.querySelector('[type="submit"], button:not([type]), .rp-avail-btn-secondary');
        if (submitBtn) {
            submitBtn.disabled = over;
        }

        return !over;
    };

    document.querySelectorAll('form').forEach((form) => {
        if (!form.querySelector('[data-rp-guest-adults]')) return;

        const sync = () => syncGuestCapacity(form);
        form.querySelectorAll('[data-rp-guest-adults], [data-rp-guest-children], [data-rp-capacity-select]').forEach((el) => {
            el.addEventListener('input', sync);
            el.addEventListener('change', sync);
        });
        form.addEventListener('submit', (event) => {
            if (!syncGuestCapacity(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        sync();
    });

    const pad = (n) => String(n).padStart(2, '0');
    const toYmd = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const addDays = (ymd, days) => {
        const date = new Date(`${ymd}T00:00:00`);
        date.setDate(date.getDate() + days);
        return toYmd(date);
    };
    const todayYmd = toYmd(new Date());

    const bindStayDates = (checkInEl, checkOutEl) => {
        checkInEl.min = todayYmd;
        if (checkInEl.value && checkInEl.value < todayYmd) {
            checkInEl.value = '';
        }

        const syncCheckOutMin = () => {
            const minOut = checkInEl.value ? addDays(checkInEl.value, 1) : addDays(todayYmd, 1);
            checkOutEl.min = minOut;
            if (checkOutEl.value && checkOutEl.value < minOut) {
                checkOutEl.value = minOut;
            }
            calcTotal();
        };

        syncCheckOutMin();
        checkInEl.addEventListener('change', syncCheckOutMin);
    };

    document.querySelectorAll('[data-stay-check-in]').forEach((checkInEl) => {
        const form = checkInEl.closest('form');
        const checkOutEl = form?.querySelector('[data-stay-check-out]');
        if (checkOutEl) {
            bindStayDates(checkInEl, checkOutEl);
        }
    });

    const enhancePasswordFields = () => {
        document.querySelectorAll('input[type="password"]').forEach((input) => {
            if (input.dataset.rpPasswordToggle === '1') return;
            input.dataset.rpPasswordToggle = '1';

            let wrap = input.closest('.input-group');
            let button = wrap?.querySelector('[data-rp-toggle-password], #togglePassword');

            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'input-group';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);
            }

            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-secondary';
                button.setAttribute('data-rp-toggle-password', '');
                button.setAttribute('aria-label', 'Show password');
                button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
                wrap.appendChild(button);
            } else {
                button.setAttribute('data-rp-toggle-password', '');
            }

            const icon = button.querySelector('i') || button;

            button.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                if (icon.classList) {
                    icon.classList.toggle('bi-eye', !show);
                    icon.classList.toggle('bi-eye-slash', show);
                }
            });
        });
    };

    enhancePasswordFields();

    document.querySelectorAll('[data-rp-user-create-form]').forEach((form) => {
        const role = form.querySelector('[data-rp-user-role]');
        const phone = form.querySelector('[data-rp-user-phone]');
        const requiredMarker = form.querySelector('[data-rp-user-phone-required]');
        const optionalMarker = form.querySelector('[data-rp-user-phone-optional]');
        if (!role || !phone) return;

        const syncPhoneRequirement = () => {
            const required = role.selectedOptions[0]?.dataset.rpRequiresPhone === '1';
            phone.required = required;
            phone.setAttribute('aria-required', String(required));
            if (requiredMarker) requiredMarker.hidden = !required;
            if (optionalMarker) optionalMarker.hidden = required;
        };

        role.addEventListener('change', syncPhoneRequirement);
        syncPhoneRequirement();
    });

    document.querySelectorAll('[data-rp-promo-input]').forEach((input) => {
        const wrap = input.closest('form') || document;
        const button = wrap.querySelector('[data-rp-promo-check]');
        const feedback = wrap.querySelector('[data-rp-promo-feedback]');
        const estimate = wrap.querySelector('[data-rp-promo-estimate]');
        const totalEl = wrap.querySelector('[data-calc-total]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const checkInInput = wrap.querySelector('[name="check_in_date"]');
        const checkOutInput = wrap.querySelector('[name="check_out_date"]');
        let requestNumber = 0;

        const resetPreview = () => {
            requestNumber++;
            input.classList.remove('is-valid', 'is-invalid');
            if (estimate) estimate.textContent = '';
            if (feedback) feedback.textContent = 'Check a code to see your discounted estimate.';
            calcTotal();
        };

        const applyCheck = async () => {
            const code = (input.value || '').trim();
            if (!code) {
                resetPreview();
                return;
            }
            if (!checkInInput?.value || !checkOutInput?.value) {
                resetPreview();
                if (feedback) feedback.textContent = 'Select your check-in and check-out dates first.';
                return;
            }

            const currentRequest = ++requestNumber;
            try {
                const response = await fetch(input.dataset.rpPromoValidateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: JSON.stringify({
                        promo_code: code,
                        accommodation_id: Number(input.dataset.rpPromoAccommodation || 0),
                        check_in_date: checkInInput.value,
                        check_out_date: checkOutInput.value,
                    }),
                });
                const data = await response.json();
                if (currentRequest !== requestNumber) return;
                if (!response.ok) {
                    const message = data?.errors?.promo_code?.[0] || data?.message || 'Invalid promo code.';
                    if (feedback) feedback.textContent = message;
                    if (estimate) estimate.textContent = '';
                    calcTotal();
                    input.classList.add('is-invalid');
                    return;
                }

                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                input.value = data.code;
                if (feedback) feedback.textContent = data.message;
                if (estimate) {
                    estimate.textContent = `Promo total: ₱${Number(data.promo_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (save ₱${Number(data.discount_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
                }
                if (totalEl && data.promo_total != null) {
                    totalEl.textContent = `₱${Number(data.promo_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            } catch (e) {
                if (currentRequest !== requestNumber) return;
                if (feedback) feedback.textContent = 'Could not validate promo code right now.';
                if (estimate) estimate.textContent = '';
                calcTotal();
            }
        };

        input.addEventListener('input', resetPreview);
        [checkInInput, checkOutInput].forEach((dateInput) => {
            dateInput?.addEventListener('change', () => {
                resetPreview();
                if (input.value.trim()) applyCheck();
            });
        });

        button?.addEventListener('click', (event) => {
            event.preventDefault();
            applyCheck();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyCheck();
            }
        });
    });

    const refreshCsrfToken = async (form) => {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            throw new Error('csrf-refresh-failed');
        }
        const data = await response.json();
        const token = data?.token;
        if (!token) {
            throw new Error('csrf-token-missing');
        }
        form.querySelectorAll('input[name="_token"]').forEach((input) => {
            input.value = token;
        });
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', token);
        return token;
    };

    document.querySelectorAll('[data-rp-login-form]').forEach((form) => {
        const names = ['email', 'password'];
        const field = (name) => form.querySelector(`[data-rp-login-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-login-feedback="${name}"]`);
        const touched = new Set();
        const serverInvalid = new Set(names.filter((name) => field(name)?.classList.contains('is-invalid')));
        let submitting = false;

        const validateField = (name, force = false) => {
            const input = field(name);
            const messageEl = feedback(name);
            if (!input || !messageEl) return true;

            let message = '';
            if (name === 'email') {
                const email = input.value.trim();
                if (!email) message = 'Please enter your email address.';
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) message = 'Enter a valid email address.';
            } else if (!input.value) {
                message = 'Please enter your password.';
            }

            const showError = Boolean(message) && (force || touched.has(name));
            input.classList.toggle('is-invalid', showError);
            if (showError) input.setAttribute('aria-invalid', 'true');
            else input.removeAttribute('aria-invalid');
            messageEl.textContent = showError ? message : '';

            return !message;
        };

        names.forEach((name) => {
            const input = field(name);
            if (!input) return;

            input.addEventListener('input', () => {
                serverInvalid.delete(name);
                touched.add(name);
                validateField(name);
            });
            input.addEventListener('blur', () => {
                if (serverInvalid.has(name)) return;
                touched.add(name);
                validateField(name);
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll(
                '#rpLoginModal .rp-login-error:not(:empty), #rpRegisterModal .rp-login-error:not(:empty)'
            ).forEach((messageEl) => {
                messageEl.textContent = '';
                const input = messageEl.closest('.rp-login-field')?.querySelector('input, textarea');
                input?.classList.remove('is-invalid');
                input?.removeAttribute('aria-invalid');
            });
        });

        form.addEventListener('submit', async (event) => {
            if (submitting) return;

            names.forEach((name) => touched.add(name));
            const valid = names.map((name) => validateField(name, true)).every(Boolean);
            if (!valid) {
                event.preventDefault();
                form.querySelector('[data-rp-login-field].is-invalid')?.focus();
                return;
            }

            // Refresh CSRF right before POST — fixes mobile "session expired" (419) after sleep/resume.
            event.preventDefault();
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            try {
                await refreshCsrfToken(form);
            } catch (error) {
                // Fall through and let the server respond; user can retry.
            }
            submitting = true;
            HTMLFormElement.prototype.submit.call(form);
        });

        // Keep browser validation as a fallback when JavaScript is unavailable.
        form.noValidate = true;
    });

    document.querySelectorAll('[data-rp-register-form]').forEach((form) => {
        const fieldNames = ['name', 'email', 'phone', 'password', 'password_confirmation'];
        const field = (name) => form.querySelector(`[data-rp-register-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-register-feedback="${name}"]`);
        const hint = (name) => form.querySelector(`[data-rp-register-hint="${name}"]`);
        const submitBtn = form.querySelector('[type="submit"]');
        const touched = new Set();

        form.noValidate = true;

        const normalizePhone = (value) => String(value || '').replace(/[\s\-()]/g, '');

        const validators = {
            name: (value) => {
                const trimmed = value.trim();
                if (!trimmed) return 'Please enter your full name.';
                if (trimmed.length < 2) return 'Full name must be at least 2 characters.';
                if (!/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:[ '\-.][A-Za-zÀ-ÖØ-öø-ÿ]+)*$/.test(trimmed)) {
                    return 'Full name may only include letters, spaces, hyphens, and apostrophes.';
                }
                return '';
            },
            email: (value) => {
                const trimmed = value.trim().toLowerCase();
                if (!trimmed) return 'Please enter your email address.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                    return 'Enter a valid Gmail address (e.g. you@gmail.com).';
                }
                const parts = trimmed.split('@');
                if (parts.length !== 2 || !['gmail.com', 'googlemail.com'].includes(parts[1])) {
                    return 'Only a Gmail address (@gmail.com) is accepted.';
                }
                const base = parts[0].split('+')[0];
                if (!base || base.length > 30) {
                    return 'Enter a valid Gmail address (example@gmail.com).';
                }
                if (!/^[a-z0-9.]+$/.test(base)) {
                    return 'Gmail usernames may only use letters, numbers, and periods.';
                }
                if (base.startsWith('.') || base.endsWith('.') || base.includes('..')) {
                    return 'Enter a valid Gmail username (no leading, trailing, or double periods).';
                }
                return '';
            },
            phone: (value) => {
                const normalized = normalizePhone(value);
                if (!normalized) return 'Please enter your contact number.';
                if (!/^(?:\+?63|0)9\d{9}$/.test(normalized)) {
                    return 'Enter a valid PH mobile number, e.g. 09171234567.';
                }
                return '';
            },
            password: (value) => {
                if (!value) return 'Please create a password.';
                if (value.length < 8) return 'Password must be at least 8 characters.';
                if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/\d/.test(value)) {
                    return 'Use uppercase, lowercase, and a number.';
                }
                return '';
            },
            password_confirmation: (value) => {
                const password = field('password')?.value || '';
                if (!value) return 'Please confirm your password.';
                if (value !== password) return 'Passwords do not match.';
                return '';
            },
        };

        const updatePasswordChecks = () => {
            const value = field('password')?.value || '';
            const rules = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /\d/.test(value),
            };
            Object.entries(rules).forEach(([rule, met]) => {
                const item = form.querySelector(`[data-rp-pw-rule="${rule}"]`);
                if (!item) return;
                item.classList.toggle('is-met', met);
                const icon = item.querySelector('i');
                if (icon) {
                    icon.className = met ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                }
            });
        };

        const setFieldState = (name, message, { force = false } = {}) => {
            const input = field(name);
            const messageEl = feedback(name);
            const hintEl = hint(name);
            if (!input || !messageEl) return !message;

            const showError = Boolean(message) && (force || touched.has(name) || input.classList.contains('is-invalid'));
            const showValid = !message && (force || touched.has(name));

            input.classList.toggle('is-invalid', showError);
            input.classList.toggle('is-valid', showValid);

            if (showError) {
                messageEl.textContent = message;
                hintEl?.classList.add('d-none');
            } else {
                messageEl.textContent = '';
                hintEl?.classList.remove('d-none');
            }

            return !message;
        };

        const validateField = (name, options = {}) => {
            const input = field(name);
            if (!input || !validators[name]) return true;
            return setFieldState(name, validators[name](input.value), options);
        };

        const validateAll = (options = {}) => {
            updatePasswordChecks();
            return fieldNames.map((name) => validateField(name, options)).every(Boolean);
        };

        fieldNames.forEach((name) => {
            const input = field(name);
            if (!input) return;

            input.addEventListener('input', () => {
                if (name === 'password') {
                    updatePasswordChecks();
                    if (touched.has('password_confirmation') || field('password_confirmation')?.value) {
                        validateField('password_confirmation', { force: true });
                    }
                }
                if (touched.has(name) || input.classList.contains('is-invalid')) {
                    validateField(name, { force: true });
                }
            });

            input.addEventListener('blur', () => {
                touched.add(name);
                validateField(name, { force: true });
            });
        });

        let registering = false;
        form.addEventListener('submit', async (event) => {
            if (registering) return;

            fieldNames.forEach((name) => touched.add(name));
            const valid = validateAll({ force: true });
            if (!valid) {
                event.preventDefault();
                event.stopPropagation();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Register';
                }
                const firstInvalid = form.querySelector('.is-invalid');
                firstInvalid?.focus();
                firstInvalid?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                return;
            }

            event.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating account...';
            }
            try {
                await refreshCsrfToken(form);
            } catch (error) {
                // Continue; server will show a clear error if the token is still stale.
            }
            registering = true;
            HTMLFormElement.prototype.submit.call(form);
        });

                    updatePasswordChecks();
        if (form.querySelector('.is-invalid')) {
            fieldNames.forEach((name) => {
                const input = field(name);
                if (input?.classList.contains('is-invalid')) {
                    touched.add(name);
                    validateField(name, { force: true });
                }
            });
        }
    });

    document.querySelectorAll('[data-rp-profile-form]').forEach((form) => {
        const names = ['name', 'email', 'phone', 'avatar'];
        const field = (name) => form.querySelector(`[data-rp-profile-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-profile-feedback="${name}"]`);
        const touched = new Set();
        const serverErrors = new Map();
        const originalEmail = (form.dataset.rpOriginalEmail || '').trim().toLowerCase();

        names.forEach((name) => {
            const input = field(name);
            if (input?.classList.contains('is-invalid')) {
                touched.add(name);
                if (name !== 'avatar') {
                    serverErrors.set(name, { value: input.value, message: feedback(name)?.textContent?.trim() || '' });
                }
                input.setAttribute('aria-invalid', 'true');
            }
        });

        const errorFor = (name) => {
            const input = field(name);
            if (!input) return '';
            const value = input.value.trim();

            if (name === 'name') {
                if (!value) return 'Please enter your full name.';
                if ([...value].length < 2) return 'Full name must be at least 2 characters.';
                if ([...value].length > 255) return 'Full name must be 255 characters or fewer.';
                if (!/^\p{L}+(?:[ '\-.]\p{L}+)*$/u.test(value)) {
                    return 'Full name may only include letters, spaces, hyphens, and apostrophes.';
                }
            }

            if (name === 'email') {
                if (!value) return 'Please enter your email address.';
                if (value.length > 255 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Enter a valid email address.';
                if (value.toLowerCase() !== originalEmail) {
                    const [local, domain] = value.toLowerCase().split('@');
                    const base = local.split('+')[0];
                    if (!['gmail.com', 'googlemail.com'].includes(domain)) return 'Only a Gmail address (@gmail.com) is accepted.';
                    if (!base || base.length > 30 || !/^[a-z0-9.]+$/.test(base) || base.startsWith('.') || base.endsWith('.') || base.includes('..')) {
                        return 'Enter a valid Gmail address (example@gmail.com).';
                    }
                }
            }

            if (name === 'phone') {
                if (!value) return 'Please enter your contact number.';
                if (value.length > 20 || !/^(?:\+?63|0)9\d{9}$/.test(value.replace(/[\s\-()]/g, ''))) {
                    return 'Enter a valid PH mobile number, e.g. 09171234567.';
                }
            }

            if (name === 'avatar') {
                const file = input.files?.[0];
                if (!file) return '';
                const extension = file.name.split('.').pop()?.toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'webp'].includes(extension) || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                    return 'Choose a JPG, PNG, or WebP image.';
                }
                if (file.size > 2048 * 1024) return 'Profile photo must be 2MB or smaller.';
            }

            return '';
        };

        const validate = (name, { force = false } = {}) => {
            const input = field(name);
            if (!input) return true;
            const serverError = serverErrors.get(name);
            const message = serverError && input.value === serverError.value
                ? serverError.message
                : errorFor(name);
            const show = Boolean(message) && (force || touched.has(name));
            input.classList.toggle('is-invalid', show);
            if (show) input.setAttribute('aria-invalid', 'true');
            else input.removeAttribute('aria-invalid');
            const messageEl = feedback(name);
            if (messageEl) {
                messageEl.textContent = show ? message : '';
                messageEl.classList.toggle('d-block', show);
            }
            if (name === 'avatar') {
                const trigger = form.querySelector('[data-rp-avatar-trigger]');
                if (show) trigger?.setAttribute('aria-invalid', 'true');
                else trigger?.removeAttribute('aria-invalid');
            }
            return !message;
        };

        names.forEach((name) => {
            const input = field(name);
            if (!input) return;
            input.addEventListener(name === 'avatar' ? 'change' : 'input', () => {
                if (name === 'avatar') touched.add(name);
                if (touched.has(name)) validate(name);
            });
            if (name !== 'avatar') {
            input.addEventListener('blur', () => {
                    touched.add(name);
                    validate(name, { force: true });
                });
            }
        });

        form.addEventListener('submit', (event) => {
            names.forEach((name) => touched.add(name));
            const valid = names.map((name) => validate(name, { force: true })).every(Boolean);
            if (!valid) {
                event.preventDefault();
                const firstInvalid = form.querySelector('[data-rp-profile-field].is-invalid');
                const focusTarget = firstInvalid?.name === 'avatar' ? form.querySelector('[data-rp-avatar-trigger]') : firstInvalid;
                if (focusTarget?.closest('[data-rp-profile-step]')) {
                    setProfilePasswordStep(form, focusTarget.closest('[data-rp-profile-step]').dataset.rpProfileStep);
                    window.setTimeout(() => focusTarget.focus(), 230);
                } else {
                    focusTarget?.focus();
                }
            }
        });
    });

    const setProfilePasswordStep = (form, step) => {
        const steps = form.querySelector('[data-rp-profile-steps]');
        if (!steps) return;
        steps.dataset.activeStep = step;
        steps.querySelectorAll('[data-rp-profile-step]').forEach((panel) => {
            const isActive = panel.dataset.rpProfileStep === step;
            panel.inert = !isActive;
            panel.setAttribute('aria-hidden', String(!isActive));
        });
        form.closest('[data-rp-profile-manage]')?.querySelectorAll('[data-rp-profile-step-title]').forEach((title) => {
            title.hidden = title.dataset.rpProfileStepTitle !== step;
        });
        const manageShell = form.closest('[data-rp-profile-manage]');
        const backButton = manageShell?.querySelector('[data-rp-profile-cancel]');
        const hasSummary = Boolean(manageShell?.querySelector('[data-rp-profile-view]'));
        if (backButton) {
            backButton.setAttribute('aria-label', step === 'password' ? 'Back to edit details' : hasSummary ? 'Back' : 'Close profile editor');
        }
    };

    document.querySelectorAll('[data-rp-password-form]').forEach((form) => {
        const steps = form.querySelector('[data-rp-profile-steps]');
        if (steps) setProfilePasswordStep(form, steps.dataset.activeStep || 'details');
        const field = (name) => form.querySelector(`[data-rp-password-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-password-feedback="${name}"]`);
        const touched = new Set();
        const serverErrors = new Map();
        const emailField = form.querySelector('[data-rp-profile-field="email"]');
        const originalEmail = (form.dataset.rpOriginalEmail || '').trim().toLowerCase();
        const emailChanged = () => Boolean(emailField) && emailField.value.trim().toLowerCase() !== originalEmail;
        const isChangingPassword = () => ['password', 'password_confirmation']
            .some((name) => Boolean(field(name)?.value?.trim()));
        const needsCurrentPassword = () => emailChanged() || isChangingPassword();

        const validators = {
            current_password: (value) => {
                if (!value && needsCurrentPassword()) return 'Please enter your current password.';
                return '';
            },
            password: (value) => {
                if (!value) return 'Please create a new password.';
                if (value.length < 8) return 'Password must be at least 8 characters.';
                if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/\d/.test(value)) {
                    return 'Use uppercase, lowercase, and a number.';
                }
                return '';
            },
            password_confirmation: (value) => {
                const password = field('password')?.value || '';
                if (!value) return 'Please confirm your new password.';
                if (value !== password) return 'Passwords do not match.';
                return '';
            },
        };

        const updatePasswordChecks = () => {
            const value = field('password')?.value || '';
            const rules = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /\d/.test(value),
            };
            Object.entries(rules).forEach(([rule, met]) => {
                const item = form.querySelector(`[data-rp-pw-rule="${rule}"]`);
                if (!item) return;
                item.classList.toggle('is-met', met);
                const icon = item.querySelector('i');
                if (icon) icon.className = met ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            });
        };

        const setFieldState = (name, message, { force = false } = {}) => {
            const input = field(name);
            const messageEl = feedback(name);
            if (!input) return !message;

            const showError = Boolean(message) && (force || touched.has(name) || input.classList.contains('is-invalid'));

            input.classList.toggle('is-invalid', showError);
            if (showError) input.setAttribute('aria-invalid', 'true');
            else input.removeAttribute('aria-invalid');
            if (messageEl) {
                messageEl.textContent = showError ? message : '';
                messageEl.classList.toggle('d-block', showError);
            }

            return !message;
        };

        const validateField = (name, options = {}) => {
            const input = field(name);
            if (!input || !validators[name]) return true;
            const serverError = serverErrors.get(name);
            const message = serverError && input.value === serverError.value
                ? serverError.message
                : validators[name](input.value);
            return setFieldState(name, message, options);
        };

        const clearPasswordErrors = () => {
            ['current_password', 'password', 'password_confirmation'].forEach((name) => {
                setFieldState(name, '', { force: true });
            });
            serverErrors.clear();
        };

        const validateAll = (options = {}) => {
            updatePasswordChecks();
            const changingPassword = isChangingPassword();
            const currentRequired = needsCurrentPassword();
            if (field('current_password')) field('current_password').required = currentRequired;
            if (!currentRequired) {
                clearPasswordErrors();
                return true;
            }
            if (!changingPassword) {
                ['password', 'password_confirmation'].forEach((name) => setFieldState(name, '', { force: true }));
            }
            return (changingPassword ? ['current_password', 'password', 'password_confirmation'] : ['current_password'])
                .map((name) => validateField(name, options))
                .every(Boolean);
        };

        emailField?.addEventListener('input', () => {
            if (field('current_password')) field('current_password').required = needsCurrentPassword();
            if (!needsCurrentPassword()) clearPasswordErrors();
            else if (touched.has('current_password')) validateField('current_password');
        });

        ['current_password', 'password', 'password_confirmation'].forEach((name) => {
            const input = field(name);
            if (!input) return;

            input.addEventListener('input', () => {
                if (name === 'password') updatePasswordChecks();
                if (field('current_password')) field('current_password').required = needsCurrentPassword();
                if (!needsCurrentPassword()) {
                    clearPasswordErrors();
                    return;
                }
                if ((name === 'current_password' || isChangingPassword()) && (touched.has(name) || input.classList.contains('is-invalid'))) {
                    validateField(name);
                }
                if (name === 'password' && touched.has('password_confirmation')) {
                    validateField('password_confirmation');
                }
            });

            input.addEventListener('blur', () => {
                if (!needsCurrentPassword()) {
                    clearPasswordErrors();
                    return;
                }
                if (name !== 'current_password' && !isChangingPassword()) return;
                touched.add(name);
                validateField(name, { force: true });
            });
        });

        form.addEventListener('submit', (event) => {
            if (form.querySelector('[data-rp-profile-field].is-invalid')) {
                setProfilePasswordStep(form, 'details');
                return;
            }
            if (!needsCurrentPassword()) {
                clearPasswordErrors();
                return;
            }
            (isChangingPassword() ? ['current_password', 'password', 'password_confirmation'] : ['current_password'])
                .forEach((name) => touched.add(name));
            if (!validateAll({ force: true })) {
                event.preventDefault();
                const firstInvalid = form.querySelector('[data-rp-password-field].is-invalid');
                const credentialFields = ['current_password', 'password', 'password_confirmation'];
                if (firstInvalid && credentialFields.includes(firstInvalid.name)) {
                    setProfilePasswordStep(form, 'password');
                    window.setTimeout(() => firstInvalid.focus(), 230);
                } else {
                    firstInvalid?.focus();
                }
            }
        });

        updatePasswordChecks();
        if (field('current_password')) field('current_password').required = needsCurrentPassword();
        if (form.querySelector('.is-invalid')) {
            ['current_password', 'password', 'password_confirmation'].forEach((name) => {
                const input = field(name);
                if (input?.classList.contains('is-invalid')) {
                    serverErrors.set(name, { value: input.value, message: feedback(name)?.textContent?.trim() || '' });
                    touched.add(name);
                    validateField(name, { force: true });
                }
            });
        }
    });

    const ensureProfileLightbox = () => {
        let lightbox = document.getElementById('rpProfileLightbox');
        if (lightbox) return lightbox;

        lightbox = document.createElement('div');
        lightbox.id = 'rpProfileLightbox';
        lightbox.className = 'rp-profile-lightbox';
        lightbox.hidden = true;
        lightbox.innerHTML = '<button type="button" class="rp-profile-lightbox-close" aria-label="Close">&times;</button><img alt="Profile photo">';
        document.body.appendChild(lightbox);

        const closeLightbox = () => {
            lightbox.hidden = true;
            lightbox.querySelector('img').removeAttribute('src');
        };

        lightbox.querySelector('.rp-profile-lightbox-close')?.addEventListener('click', (event) => {
            event.stopPropagation();
            closeLightbox();
        });
        lightbox.addEventListener('click', closeLightbox);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !lightbox.hidden) closeLightbox();
        });

        return lightbox;
    };

    const openProfileLightbox = (src, alt = 'Profile photo') => {
        if (!src) return;
        const lightbox = ensureProfileLightbox();
        const image = lightbox.querySelector('img');
        image.src = src;
        image.alt = alt;
        lightbox.hidden = false;
    };

    document.querySelectorAll('[data-rp-avatar-view]').forEach((viewBtn) => {
        viewBtn.addEventListener('click', () => {
            if (viewBtn.disabled) return;
            const preview = viewBtn.querySelector('[data-rp-avatar-preview], img');
            const src = viewBtn.dataset.rpAvatarSrc || preview?.getAttribute('src') || '';
            if (!src || preview?.classList.contains('d-none')) return;
            openProfileLightbox(src, preview?.getAttribute('alt') || 'Profile photo');
        });
    });

    document.querySelectorAll('[data-rp-profile-manage]').forEach((shell) => {
        const viewPanel = shell.querySelector('[data-rp-profile-view]');
        const editPanel = shell.querySelector('[data-rp-profile-edit-panel]');
        const showEdit = () => {
            if (viewPanel) viewPanel.hidden = true;
            if (editPanel) editPanel.hidden = false;
            shell.dataset.rpProfileEditing = '1';
        };
        const showView = () => {
            if (viewPanel) viewPanel.hidden = false;
            if (editPanel) editPanel.hidden = true;
            delete shell.dataset.rpProfileEditing;
        };
        shell.querySelectorAll('[data-rp-profile-edit]').forEach((btn) => {
            btn.addEventListener('click', showEdit);
        });
        const profileForm = shell.querySelector('[data-rp-password-form]');
        shell.querySelectorAll('[data-rp-profile-step-trigger="password"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!profileForm) return;
                setProfilePasswordStep(profileForm, 'password');
                window.setTimeout(() => profileForm.querySelector('[data-rp-password-field="current_password"]')?.focus(), 230);
            });
        });
        shell.querySelectorAll('[data-rp-profile-cancel]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (profileForm?.querySelector('[data-rp-profile-steps]')?.dataset.activeStep === 'password') {
                    setProfilePasswordStep(profileForm, 'details');
                    window.setTimeout(() => shell.querySelector('[data-rp-profile-step-trigger="password"]')?.focus(), 230);
                    return;
                }
                if (viewPanel) {
                    showView();
                    return;
                }
                const modal = shell.closest('.modal');
                if (modal && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(modal).hide();
            });
        });

        shell.querySelectorAll('[data-rp-password-dropdown]').forEach((dropdown) => {
            const toggle = dropdown.querySelector('[data-rp-password-toggle]');
            const panel = dropdown.querySelector('[data-rp-password-panel]');
            if (!toggle || !panel) return;
            toggle.addEventListener('click', () => {
                const open = dropdown.classList.toggle('is-open');
                panel.hidden = !open;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    });

    document.querySelectorAll('[data-rp-profile-photo]').forEach((wrap) => {
        const input = wrap.querySelector('[data-rp-avatar-input]');
        const preview = wrap.querySelector('[data-rp-avatar-preview]');
        const fallback = wrap.querySelector('[data-rp-avatar-fallback]');
        const remove = wrap.querySelector('[data-rp-avatar-remove]');
        const viewBtn = wrap.querySelector('[data-rp-avatar-view]');
        const savePhotoBtn = wrap.querySelector('[data-rp-avatar-save]');
        if (!input || !preview) return;

        wrap.querySelector('[data-rp-avatar-trigger]')?.addEventListener('click', () => input.click());

        const setViewable = (src) => {
            if (!viewBtn) return;
            const hasImage = Boolean(src);
            viewBtn.disabled = !hasImage;
            viewBtn.classList.toggle('is-empty', !hasImage);
            viewBtn.setAttribute('aria-label', hasImage ? 'View profile photo' : 'No profile photo yet');
            if (hasImage) viewBtn.dataset.rpAvatarSrc = src;
            else delete viewBtn.dataset.rpAvatarSrc;
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                if (savePhotoBtn) savePhotoBtn.hidden = !remove?.checked;
                return;
            }
            if (savePhotoBtn) savePhotoBtn.hidden = false;
            const extension = file.name.split('.').pop()?.toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'webp'].includes(extension)
                || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))
                || file.size > 2048 * 1024) return;
            if (remove) remove.checked = false;
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            fallback?.classList.add('d-none');
            setViewable(url);
        });

        remove?.addEventListener('change', () => {
            if (savePhotoBtn) savePhotoBtn.hidden = !remove.checked && !input.files?.length;
            if (!remove.checked) return;
            input.value = '';
            preview.src = '';
            preview.classList.add('d-none');
            fallback?.classList.remove('d-none');
            setViewable('');
        });
    });

    document.querySelectorAll('[data-rp-nav-group]').forEach((group) => {
        const toggle = group.querySelector('[data-rp-nav-group-toggle]');
        const panel = group.querySelector('[data-rp-nav-group-panel]');
        if (!toggle || !panel) return;

        // Keep the section open when it contains the active page link.
        if (panel.querySelector('a.active')) {
            group.classList.add('is-open');
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', () => {
            const open = group.classList.toggle('is-open');
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    const starLabels = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very good',
        5: 'Excellent',
    };

    document.querySelectorAll('[data-rp-star-rating]').forEach((wrap) => {
        const input = wrap.querySelector('[data-rp-star-value]');
        const label = wrap.querySelector('[data-rp-star-label]');
        const buttons = [...wrap.querySelectorAll('[data-rp-star]')];
        if (!input || !buttons.length) return;

        const paint = (value) => {
            const rating = Math.max(1, Math.min(5, Number(value) || 0));
            input.value = String(rating);
            if (label) label.textContent = starLabels[rating] || '';
            buttons.forEach((btn) => {
                const star = Number(btn.dataset.rpStar || 0);
                const active = star <= rating;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-pressed', star === rating ? 'true' : 'false');
                const icon = btn.querySelector('i');
                if (icon) icon.className = active ? 'bi bi-star-fill' : 'bi bi-star';
            });
        };

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => paint(btn.dataset.rpStar));
            btn.addEventListener('mouseenter', () => {
                const hoverValue = Number(btn.dataset.rpStar || 0);
                buttons.forEach((item) => {
                    const star = Number(item.dataset.rpStar || 0);
                    const active = star <= hoverValue;
                    item.classList.toggle('is-active', active);
                    const icon = item.querySelector('i');
                    if (icon) icon.className = active ? 'bi bi-star-fill' : 'bi bi-star';
                });
            });
        });

        wrap.querySelector('.rp-star-rating-stars')?.addEventListener('mouseleave', () => {
            paint(input.value);
        });

        paint(input.value);
    });

    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const scope = button.closest('.rp-login-modal-body, .rp-auth-card') || document;
            const form = scope.querySelector('[data-rp-login-form]') || button.closest('form');
            const emailInput = scope.querySelector('[data-rp-login-field="email"]')
                || scope.querySelector('input[name="email"]')
                || document.getElementById('email');
            const demoPasswordInput = scope.querySelector('[data-rp-login-field="password"]')
                || scope.querySelector('input[name="password"]')
                || document.getElementById('password');
            if (!emailInput || !demoPasswordInput) return;

            emailInput.value = button.dataset.demoEmail || '';
            demoPasswordInput.value = button.dataset.demoPassword || '';
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            demoPasswordInput.dispatchEvent(new Event('input', { bubbles: true }));

            // On mobile, auto-submit so users do not lose the Login button behind the keyboard.
            if (form && typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else if (form) {
                form.submit();
            }
        });
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');
    const installSteps = document.getElementById('rpInstallSteps');
    const installedNote = document.getElementById('rpInstalledNote');
    const userAgent = navigator.userAgent;
    const installPlatform = /iphone|ipad|ipod/i.test(userAgent) ? 'ios' : 'android';
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    const showInstallTab = (platform) => {
        const tabButton = document.querySelector(`[data-rp-install-tab="${platform}"]`);
        if (tabButton && window.bootstrap?.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(tabButton).show();
        }
    };

    const markInstalled = () => {
        installBtn?.classList.add('d-none');
        installSteps?.classList.add('d-none');
        installedNote?.classList.remove('d-none');
    };

    if (isStandalone) {
        markInstalled();
    } else {
        showInstallTab(installPlatform);
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
    });

    installBtn?.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            return;
        }

        showInstallTab(installPlatform);
        installSteps?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        markInstalled();
    });

    initAvailabilityCalendar();
    initPromoDatetimePicker();
});

function initPromoDatetimePicker() {
    const modalEl = document.getElementById('rpPromoDatetimeModal');
    const fields = Array.from(document.querySelectorAll('[data-rp-promo-dt-field]'));
    if (!modalEl || !fields.length || !window.bootstrap) return;

    const titleEl = modalEl.querySelector('#rpPromoDatetimeModalLabel');
    const monthTitleEl = modalEl.querySelector('[data-rp-promo-dt-title]');
    const daysEl = modalEl.querySelector('[data-rp-promo-dt-days]');
    const hourEl = modalEl.querySelector('[data-rp-promo-dt-hour]');
    const minuteEl = modalEl.querySelector('[data-rp-promo-dt-minute]');
    const ampmEl = modalEl.querySelector('[data-rp-promo-dt-ampm]');
    const previewEl = modalEl.querySelector('[data-rp-promo-dt-preview]');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    let activeField = null;
    let viewYear = new Date().getFullYear();
    let viewMonth = new Date().getMonth() + 1;
    let selectedDate = null;
    let selectedHour12 = 12;
    let selectedMinute = 0;
    let selectedAmPm = 'AM';

    const pad = (n) => String(n).padStart(2, '0');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const toLocalValue = (date) => (
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
    );

    const formatDisplay = (value) => {
        if (!value) return 'Select date & time';
        const date = new Date(`${value}:00`);
        if (Number.isNaN(date.getTime())) return 'Select date & time';
        const h24 = date.getHours();
        const h12 = h24 % 12 || 12;
        const ampm = h24 >= 12 ? 'PM' : 'AM';
        return `${pad(date.getMonth() + 1)}/${pad(date.getDate())}/${date.getFullYear()} ${h12}:${pad(date.getMinutes())} ${ampm}`;
    };

    const parseValue = (value) => {
        if (!value) return null;
        const date = new Date(`${value}:00`);
        return Number.isNaN(date.getTime()) ? null : date;
    };

    const getMinDate = () => {
        const now = new Date();
        now.setSeconds(0, 0);
        if (activeField?.dataset.rpPromoDtRole === 'end') {
            const startValue = document.querySelector('[data-rp-promo-dt-role="start"] [data-rp-promo-dt-value]')?.value;
            const startDate = parseValue(startValue);
            if (startDate && startDate > now) return startDate;
        }
        if (activeField?.dataset.rpPromoDtRole === 'start' && activeField.dataset.rpAllowPastStart === '1') {
            const existing = parseValue(activeField.querySelector('[data-rp-promo-dt-value]')?.value);
            if (existing && existing < now) return existing;
        }
        return now;
    };

    const buildSelectedDate = () => {
        if (!selectedDate) return null;
        let hour24 = selectedHour12 % 12;
        if (selectedAmPm === 'PM') hour24 += 12;
        const date = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), hour24, selectedMinute, 0, 0);
        return date;
    };

    const isDisabledDate = (date) => {
        const min = getMinDate();
        const day = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const minDay = new Date(min.getFullYear(), min.getMonth(), min.getDate());
        return day < minDay;
    };

    const isDisabledTime = (hour12, minute, ampm) => {
        if (!selectedDate) return false;
        let hour24 = hour12 % 12;
        if (ampm === 'PM') hour24 += 12;
        const candidate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), hour24, minute, 0, 0);
        return candidate < getMinDate();
    };

    const syncPreview = () => {
        const date = buildSelectedDate();
        if (!previewEl) return;
        previewEl.textContent = date ? formatDisplay(toLocalValue(date)) : '—';
    };

    const renderDays = () => {
        if (!daysEl || !monthTitleEl) return;
        monthTitleEl.textContent = `${monthNames[viewMonth - 1]} ${viewYear}`;
        daysEl.innerHTML = '';

        const first = new Date(viewYear, viewMonth - 1, 1);
        const startPad = first.getDay();
        const daysInMonth = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = 0; i < startPad; i += 1) {
            const empty = document.createElement('span');
            empty.className = 'rp-promo-dt-day is-empty';
            daysEl.appendChild(empty);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(viewYear, viewMonth - 1, day);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-promo-dt-day';
            btn.textContent = String(day);
            const disabled = isDisabledDate(date);
            if (disabled) {
                btn.classList.add('is-disabled');
                btn.disabled = true;
            }
            if (
                selectedDate
                && selectedDate.getFullYear() === date.getFullYear()
                && selectedDate.getMonth() === date.getMonth()
                && selectedDate.getDate() === date.getDate()
            ) {
                btn.classList.add('is-selected');
            }
            btn.addEventListener('click', () => {
                selectedDate = date;
                // If current time is now in the past for this date, bump to min.
                const built = buildSelectedDate();
                const min = getMinDate();
                if (!built || built < min) {
                    selectedHour12 = min.getHours() % 12 || 12;
                    selectedMinute = min.getMinutes();
                    selectedAmPm = min.getHours() >= 12 ? 'PM' : 'AM';
                }
                renderDays();
                renderTime();
                syncPreview();
            });
            daysEl.appendChild(btn);
        }
    };

    const renderTimeList = (container, items, selected, onPick, disabledCheck) => {
        if (!container) return;
        container.innerHTML = '';
        items.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-promo-dt-time-option';
            btn.textContent = item.label;
            if (item.value === selected) btn.classList.add('is-selected');
            if (disabledCheck?.(item.value)) {
                btn.classList.add('is-disabled');
                btn.disabled = true;
            }
            btn.addEventListener('click', () => {
                onPick(item.value);
                renderTime();
                syncPreview();
            });
            container.appendChild(btn);
        });
        const selectedBtn = container.querySelector('.is-selected');
        selectedBtn?.scrollIntoView({ block: 'nearest' });
    };

    const renderTime = () => {
        const hours = Array.from({ length: 12 }, (_, i) => {
            const value = i + 1;
            return { value, label: pad(value) };
        });
        const minutes = Array.from({ length: 60 }, (_, i) => ({ value: i, label: pad(i) }));
        renderTimeList(hourEl, hours, selectedHour12, (v) => { selectedHour12 = v; }, (v) => isDisabledTime(v, selectedMinute, selectedAmPm));
        renderTimeList(minuteEl, minutes, selectedMinute, (v) => { selectedMinute = v; }, (v) => isDisabledTime(selectedHour12, v, selectedAmPm));
        renderTimeList(ampmEl, [{ value: 'AM', label: 'AM' }, { value: 'PM', label: 'PM' }], selectedAmPm, (v) => { selectedAmPm = v; }, (v) => isDisabledTime(selectedHour12, selectedMinute, v));
    };

    const openForField = (field) => {
        activeField = field;
        const valueInput = field.querySelector('[data-rp-promo-dt-value]');
        const current = parseValue(valueInput?.value) || new Date();
        const min = getMinDate();
        const seed = current < min && field.dataset.rpAllowPastStart !== '1' ? min : current;

        selectedDate = new Date(seed.getFullYear(), seed.getMonth(), seed.getDate());
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth() + 1;
        selectedHour12 = seed.getHours() % 12 || 12;
        selectedMinute = seed.getMinutes();
        selectedAmPm = seed.getHours() >= 12 ? 'PM' : 'AM';

        if (titleEl) {
            titleEl.textContent = field.dataset.rpPromoDtRole === 'end'
                ? 'Select end date & time'
                : 'Select start date & time';
        }

        renderDays();
        renderTime();
        syncPreview();
        modal.show();
    };

    fields.forEach((field) => {
        field.querySelector('[data-rp-promo-dt-open]')?.addEventListener('click', () => openForField(field));
    });

    modalEl.querySelector('[data-rp-promo-dt-prev]')?.addEventListener('click', () => {
        viewMonth -= 1;
        if (viewMonth < 1) {
            viewMonth = 12;
            viewYear -= 1;
        }
        renderDays();
    });

    modalEl.querySelector('[data-rp-promo-dt-next]')?.addEventListener('click', () => {
        viewMonth += 1;
        if (viewMonth > 12) {
            viewMonth = 1;
            viewYear += 1;
        }
        renderDays();
    });

    modalEl.querySelector('[data-rp-promo-dt-clear]')?.addEventListener('click', () => {
        if (!activeField) return;
        const valueInput = activeField.querySelector('[data-rp-promo-dt-value]');
        const label = activeField.querySelector('[data-rp-promo-dt-label]');
        if (valueInput) valueInput.value = '';
        if (label) {
            label.textContent = activeField.dataset.rpPromoDtRole === 'end'
                ? 'Select end date & time'
                : 'Select start date & time';
        }
        modal.hide();
    });

    modalEl.querySelector('[data-rp-promo-dt-today]')?.addEventListener('click', () => {
        const min = getMinDate();
        selectedDate = new Date(min.getFullYear(), min.getMonth(), min.getDate());
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth() + 1;
        selectedHour12 = min.getHours() % 12 || 12;
        selectedMinute = min.getMinutes();
        selectedAmPm = min.getHours() >= 12 ? 'PM' : 'AM';
        renderDays();
        renderTime();
        syncPreview();
    });

    modalEl.querySelector('[data-rp-promo-dt-ok]')?.addEventListener('click', () => {
        if (!activeField || !selectedDate) return;
        let date = buildSelectedDate();
        const min = getMinDate();
        if (!date || date < min) {
            date = min;
        }

        // End cannot be before start.
        if (activeField.dataset.rpPromoDtRole === 'end') {
            const startValue = document.querySelector('[data-rp-promo-dt-role="start"] [data-rp-promo-dt-value]')?.value;
            const startDate = parseValue(startValue);
            if (startDate && date < startDate) {
                if (previewEl) previewEl.textContent = 'End must be on or after start';
                return;
            }
        }

        const value = toLocalValue(date);
        const valueInput = activeField.querySelector('[data-rp-promo-dt-value]');
        const label = activeField.querySelector('[data-rp-promo-dt-label]');
        if (valueInput) valueInput.value = value;
        if (label) label.textContent = formatDisplay(value);

        // If start moved past an existing end, clear the end so the range stays valid.
        if (activeField.dataset.rpPromoDtRole === 'start') {
            const endField = document.querySelector('[data-rp-promo-dt-role="end"]');
            const endValueInput = endField?.querySelector('[data-rp-promo-dt-value]');
            const endLabel = endField?.querySelector('[data-rp-promo-dt-label]');
            const endDate = parseValue(endValueInput?.value);
            if (endDate && date && endDate < date) {
                if (endValueInput) endValueInput.value = '';
                if (endLabel) endLabel.textContent = 'Select end date & time';
            }
        }

        modal.hide();
    });
}

function initAvailabilityCalendar() {
    const modalEl = document.getElementById('rpAvailabilityModal');
    const form = document.querySelector('[data-rp-availability-form]');
    const calendarRoot = document.querySelector('[data-rp-availability-calendar]');

    if (!modalEl || !form || !calendarRoot || !window.bootstrap) {
        return;
    }

    let occupiedUrl = form.dataset.occupiedUrl || '';
    const accommodationSelect = form.querySelector('[data-rp-capacity-select]');
    const unitWrap = modalEl.querySelector('[data-rp-cal-unit-wrap]');
    const unitSelect = modalEl.querySelector('[data-rp-cal-unit]');
    const checkInInput = form.querySelector('[data-stay-check-in]');
    const checkOutInput = form.querySelector('[data-stay-check-out]');
    const checkInDisplay = form.querySelector('[data-rp-date-display="check_in"]');
    const checkOutDisplay = form.querySelector('[data-rp-date-display="check_out"]');
    const titleEl = calendarRoot.querySelector('[data-rp-cal-title]');
    const daysEl = calendarRoot.querySelector('[data-rp-cal-days]');
    const selectionEl = calendarRoot.querySelector('[data-rp-cal-selection]');
    const applyBtn = modalEl.querySelector('[data-rp-cal-apply]');
    const prevBtn = calendarRoot.querySelector('[data-rp-cal-prev]');
    const nextBtn = calendarRoot.querySelector('[data-rp-cal-next]');
    const proceedLink = document.querySelector('[data-rp-proceed-booking]');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((dismissButton) => {
        dismissButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            modal.hide();
        });
    });
    const mapRoots = [
        ...modalEl.querySelectorAll('[data-rp-resort-map]'),
        ...document.querySelectorAll('.rp-resort-map-page [data-rp-resort-map]'),
    ];

    const familyToZone = (family) => {
        const key = String(family || '');
        if (key === 'CAB') return 'cabana';
        if (key === 'BH') return 'beach';
        if (key === 'VILLA') return 'villa';
        if (key === 'COT' || key === 'COT-NVK' || key === 'COT-VK') return 'cottage';
        if (key === 'SUITE') return 'suite';
        if (key === 'PAY') return 'payag';
        if (key === 'RM-AC' || key === 'RM-AC-VK') return 'ac';
        return '';
    };

    let unitOptions = [];
    try {
        unitOptions = JSON.parse(form.dataset.rpUnits || '[]');
        if (!Array.isArray(unitOptions)) unitOptions = [];
    } catch (error) {
        unitOptions = [];
    }

    const findUnit = (id) => unitOptions.find((unit) => String(unit.id) === String(id));
    const findUnitByLabel = (label) => unitOptions.find((unit) => String(unit.label) === String(label));

    const syncResortMaps = (selectedUnitId) => {
        if (!mapRoots.length) return;

        const selected = findUnit(selectedUnitId) || unitOptions[0] || null;
        const family = selected?.family || form.dataset.rpFamily || '';
        const activeZone = familyToZone(family);
        const selectableLabels = new Set(unitOptions.map((unit) => String(unit.label)));
        const selectedLabel = selected ? String(selected.label) : '';

        mapRoots.forEach((root) => {
            const hint = root.querySelector('[data-rp-map-hint]');
            if (hint) {
                hint.textContent = selectedLabel
                    ? `Selected room ${selectedLabel}. Tap another numbered room to switch.`
                    : 'Select a numbered room for this accommodation type.';
            }

            root.querySelectorAll('[data-map-zone]').forEach((zoneEl) => {
                if (!zoneEl.classList.contains('rp-map-zone')) return;
                zoneEl.classList.toggle('is-active', activeZone !== '' && zoneEl.dataset.mapZone === activeZone);
            });

            root.querySelectorAll('.rp-map-room').forEach((roomBtn) => {
                const label = String(roomBtn.dataset.mapLabel || '');
                const zone = String(roomBtn.dataset.mapZone || '');
                const selectable = selectableLabels.has(label) && (!activeZone || zone === activeZone);
                const isSelected = selectable && label === selectedLabel;

                roomBtn.classList.toggle('is-selectable', selectable);
                roomBtn.classList.toggle('is-selected', isSelected);
                roomBtn.classList.toggle('is-locked', activeZone !== '' && zone === activeZone && !selectable);
                roomBtn.disabled = !selectable;
                roomBtn.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            });
        });
    };

    const applySelectedUnit = (unitId, { reload = false } = {}) => {
        const unit = findUnit(unitId) || unitOptions[0];
        if (!unit) return;

        if (unitSelect && String(unitSelect.value) !== String(unit.id)) {
            unitSelect.value = String(unit.id);
        }

        if (unit.occupied_url) {
            occupiedUrl = unit.occupied_url;
            form.dataset.occupiedUrl = unit.occupied_url;
        }
        if (unit.availability_url && !form.hasAttribute('data-rp-proceed-only')) {
            form.setAttribute('action', unit.availability_url);
        }
        const selectedAccommodationInput = form.querySelector('[data-rp-selected-accommodation]');
        if (selectedAccommodationInput) {
            selectedAccommodationInput.value = String(unit.id);
        }
        form.dataset.rpSelectedUnit = String(unit.id);
        if (unit.family) {
            form.dataset.rpFamily = unit.family;
        }

        if (proceedLink && unit.booking_url) {
            const url = new URL(unit.booking_url, window.location.origin);
            if (checkInInput?.value) url.searchParams.set('check_in', checkInInput.value);
            if (checkOutInput?.value) url.searchParams.set('check_out', checkOutInput.value);
            proceedLink.setAttribute('href', url.pathname + url.search);
        }

        syncResortMaps(unit.id);

        if (reload) {
            occupiedSet = new Set();
            loadMonth();
        }
    };

    const populateUnitSelect = () => {
        if (!unitWrap || !unitSelect) return;
        if (unitOptions.length < 2) {
            unitWrap.classList.add('d-none');
            applySelectedUnit(form.dataset.rpSelectedUnit || unitOptions[0]?.id);
            return;
        }

        unitSelect.innerHTML = '';
        unitOptions.forEach((unit) => {
            const option = document.createElement('option');
            option.value = String(unit.id);
            option.textContent = unit.label ? `Room ${unit.label}` : (unit.name || unit.number);
            unitSelect.appendChild(option);
        });

        const selected = form.dataset.rpSelectedUnit || unitOptions[0].id;
        unitSelect.value = String(selected);
        unitWrap.classList.remove('d-none');
        applySelectedUnit(unitSelect.value);
    };

    const onMapRoomClick = (event) => {
        const roomBtn = event.target.closest('.rp-map-room');
        if (!roomBtn || roomBtn.disabled) return;
        const unit = findUnitByLabel(roomBtn.dataset.mapLabel);
        if (!unit) return;
        applySelectedUnit(unit.id, { reload: true });
    };

    mapRoots.forEach((root) => {
        root.addEventListener('click', onMapRoomClick);
    });

    const initMapViewport = (root) => {
        const canvas = root.querySelector('[data-rp-map-canvas]') || root.querySelector('.rp-resort-map-canvas');
        if (!canvas || canvas.dataset.rpMapPanReady === '1') return;
        canvas.dataset.rpMapPanReady = '1';

        const board = canvas.querySelector('[data-rp-map-board]') || canvas.querySelector('.rp-map-board');
        const space = canvas.querySelector('[data-rp-map-zoom-space]');
        const zoomInBtn = root.querySelector('[data-rp-map-zoom-in]');
        const zoomOutBtn = root.querySelector('[data-rp-map-zoom-out]');
        const zoomResetBtn = root.querySelector('[data-rp-map-zoom-reset]');
        const zoomLabel = root.querySelector('[data-rp-map-zoom-label]');
        if (!board || !space) return;

        const MIN_ZOOM = 0.55;
        const MAX_ZOOM = 2;
        const ZOOM_STEP = 0.15;
        let zoom = 1;
        let baseWidth = 0;
        let baseHeight = 0;
        let dragging = false;
        let moved = false;
        let startX = 0;
        let startY = 0;
        let scrollLeft = 0;
        let scrollTop = 0;
        let pinchStartDist = 0;
        let pinchStartZoom = 1;
        const activePointers = new Map();

        const clampZoom = (value) => Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, value));

        const measureBase = () => {
            const prevTransform = board.style.transform;
            const prevMarginRight = board.style.marginRight;
            const prevMarginBottom = board.style.marginBottom;

            board.style.transform = 'none';
            board.style.marginRight = '0';
            board.style.marginBottom = '0';

            baseWidth = Math.ceil(Math.max(board.scrollWidth, board.offsetWidth));
            baseHeight = Math.ceil(Math.max(board.scrollHeight, board.offsetHeight));

            board.style.transform = prevTransform;
            board.style.marginRight = prevMarginRight;
            board.style.marginBottom = prevMarginBottom;
        };

        const paintZoom = () => {
            board.style.transformOrigin = '0 0';
            board.style.transform = zoom === 1 ? 'none' : `scale(${zoom})`;
            board.style.marginRight = zoom === 1 ? '0' : `${Math.max(0, Math.ceil(baseWidth * (zoom - 1)))}px`;
            board.style.marginBottom = zoom === 1 ? '0' : `${Math.max(0, Math.ceil(baseHeight * (zoom - 1)))}px`;
            space.style.width = 'max-content';
            space.style.height = 'max-content';
            space.style.zoom = '';
            space.style.transform = 'none';
        };

        const syncZoomChrome = () => {
            if (zoomLabel) zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
            if (zoomOutBtn) zoomOutBtn.disabled = zoom <= MIN_ZOOM + 0.001;
            if (zoomInBtn) zoomInBtn.disabled = zoom >= MAX_ZOOM - 0.001;
        };

        const applyZoom = (nextZoom, focusClientX, focusClientY, { force = false, preserveScroll = false } = {}) => {
            measureBase();
            if (!baseWidth || !baseHeight) return;

            const clamped = clampZoom(Number(nextZoom.toFixed(3)));
            const changed = Math.abs(clamped - zoom) >= 0.001;
            if (!changed && !force) {
                syncZoomChrome();
                return;
            }

            const prevScrollLeft = canvas.scrollLeft;
            const prevScrollTop = canvas.scrollTop;
            const rect = canvas.getBoundingClientRect();
            const focusX = Number.isFinite(focusClientX) ? focusClientX : rect.left + rect.width / 2;
            const focusY = Number.isFinite(focusClientY) ? focusClientY : rect.top + rect.height / 2;
            const offsetX = Math.min(Math.max(focusX - rect.left, 0), rect.width);
            const offsetY = Math.min(Math.max(focusY - rect.top, 0), rect.height);
            const contentX = (prevScrollLeft + offsetX) / zoom;
            const contentY = (prevScrollTop + offsetY) / zoom;

            zoom = clamped;
            paintZoom();

            if (preserveScroll || !changed) {
                canvas.scrollLeft = prevScrollLeft;
                canvas.scrollTop = prevScrollTop;
            } else {
                canvas.scrollLeft = Math.max(0, contentX * zoom - offsetX);
                canvas.scrollTop = Math.max(0, contentY * zoom - offsetY);
            }
            syncZoomChrome();
        };

        const bumpZoom = (delta, focusClientX, focusClientY) => {
            applyZoom(zoom + delta, focusClientX, focusClientY);
        };

        const pointerDistance = () => {
            if (activePointers.size < 2) return 0;
            const points = [...activePointers.values()];
            const dx = points[0].x - points[1].x;
            const dy = points[0].y - points[1].y;
            return Math.hypot(dx, dy);
        };

        const pointerMidpoint = () => {
            const points = [...activePointers.values()];
            return {
                x: (points[0].x + points[1].x) / 2,
                y: (points[0].y + points[1].y) / 2,
            };
        };

        const onPointerDown = (event) => {
            if (event.target.closest('.rp-map-zoom-btn, [data-rp-map-zoom-bar]')) return;
            if (event.button !== undefined && event.button !== 0) return;

            activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (activePointers.size === 2) {
                dragging = false;
                canvas.classList.remove('is-dragging');
                pinchStartDist = pointerDistance();
                pinchStartZoom = zoom;
                moved = true;
                return;
            }

            dragging = true;
            moved = false;
            startX = event.clientX;
            startY = event.clientY;
            scrollLeft = canvas.scrollLeft;
            scrollTop = canvas.scrollTop;
            canvas.classList.add('is-dragging');
            try {
                canvas.setPointerCapture?.(event.pointerId);
            } catch (error) {
                /* ignore */
            }
        };

        const onPointerMove = (event) => {
            if (activePointers.has(event.pointerId)) {
                activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
            }

            if (activePointers.size >= 2 && pinchStartDist > 0) {
                const mid = pointerMidpoint();
                const dist = pointerDistance();
                if (dist > 0) {
                    applyZoom(pinchStartZoom * (dist / pinchStartDist), mid.x, mid.y);
                }
                return;
            }

            if (!dragging) return;
            const dx = event.clientX - startX;
            const dy = event.clientY - startY;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                moved = true;
            }
            canvas.scrollLeft = scrollLeft - dx;
            canvas.scrollTop = scrollTop - dy;
        };

        const onPointerUp = (event) => {
            activePointers.delete(event.pointerId);
            if (activePointers.size < 2) {
                pinchStartDist = 0;
            }
            if (activePointers.size === 1) {
                const remaining = [...activePointers.values()][0];
                dragging = true;
                moved = true;
                startX = remaining.x;
                startY = remaining.y;
                scrollLeft = canvas.scrollLeft;
                scrollTop = canvas.scrollTop;
                canvas.classList.add('is-dragging');
                return;
            }
            if (!dragging && activePointers.size === 0) {
                canvas.classList.remove('is-dragging');
                if (moved) {
                    canvas.dataset.rpMapSuppressClick = '1';
                    window.setTimeout(() => {
                        delete canvas.dataset.rpMapSuppressClick;
                    }, 0);
                }
                return;
            }
            if (!dragging) return;
            dragging = false;
            canvas.classList.remove('is-dragging');
            try {
                canvas.releasePointerCapture?.(event.pointerId);
            } catch (error) {
                /* ignore */
            }
            if (moved) {
                canvas.dataset.rpMapSuppressClick = '1';
                window.setTimeout(() => {
                    delete canvas.dataset.rpMapSuppressClick;
                }, 0);
            }
        };

        canvas.addEventListener('pointerdown', onPointerDown);
        canvas.addEventListener('pointermove', onPointerMove);
        canvas.addEventListener('pointerup', onPointerUp);
        canvas.addEventListener('pointercancel', onPointerUp);
        canvas.addEventListener('click', (event) => {
            if (canvas.dataset.rpMapSuppressClick === '1') {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        canvas.addEventListener('wheel', (event) => {
            if (!(event.ctrlKey || event.metaKey)) return;
            event.preventDefault();
            const direction = event.deltaY > 0 ? -ZOOM_STEP : ZOOM_STEP;
            bumpZoom(direction, event.clientX, event.clientY);
        }, { passive: false });

        zoomInBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            bumpZoom(ZOOM_STEP);
        });
        zoomOutBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            bumpZoom(-ZOOM_STEP);
        });
        zoomResetBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            applyZoom(1, undefined, undefined, { force: true });
            canvas.scrollLeft = 0;
            canvas.scrollTop = 0;
        });

        const refreshViewport = ({ resetScroll = false } = {}) => {
            measureBase();
            applyZoom(zoom, undefined, undefined, { force: true, preserveScroll: !resetScroll });
            if (resetScroll) {
                canvas.scrollLeft = 0;
                canvas.scrollTop = 0;
            }
        };

        refreshViewport({ resetScroll: true });

        if (typeof ResizeObserver !== 'undefined') {
            let frame = 0;
            const observer = new ResizeObserver(() => {
                if (frame) return;
                frame = window.requestAnimationFrame(() => {
                    frame = 0;
                    refreshViewport({ resetScroll: false });
                });
            });
            observer.observe(board);
        }

        root._rpMapRefreshViewport = () => refreshViewport({ resetScroll: false });
    };

    mapRoots.forEach((root) => initMapViewport(root));
    modalEl?.addEventListener('shown.bs.modal', () => {
        window.requestAnimationFrame(() => {
            mapRoots.forEach((root) => {
                root._rpMapRefreshViewport?.();
                const canvas = root.querySelector('[data-rp-map-canvas]');
                if (canvas) {
                    canvas.scrollLeft = 0;
                    canvas.scrollTop = 0;
                }
            });
        });
    });

    const pad = (n) => String(n).padStart(2, '0');
    const toYmd = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const parseYmd = (ymd) => new Date(`${ymd}T00:00:00`);
    const formatDisplay = (ymd) => (ymd ? parseYmd(ymd).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '');
    const todayYmd = toYmd(new Date());
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    let viewYear = new Date().getFullYear();
    let viewMonth = new Date().getMonth() + 1;
    let occupiedSet = new Set();
    let pendingCheckIn = checkInInput?.value || '';
    let pendingCheckOut = checkOutInput?.value || '';
    let selectingCheckout = false;

    const updateApplyState = () => {
        const valid = pendingCheckIn && pendingCheckOut && pendingCheckOut > pendingCheckIn;
        applyBtn.disabled = !valid;
    };

    const formatSelection = () => {
        if (!pendingCheckIn) {
            return 'Pick a check-in date, then a check-out date.';
        }
        if (!pendingCheckOut) {
            return `Check-in: ${pendingCheckIn}. Now pick a check-out date.`;
        }
        return `Check-in: ${pendingCheckIn} · Check-out: ${pendingCheckOut}`;
    };

    const isPast = (ymd) => ymd < todayYmd;
    const isOccupied = (ymd) => occupiedSet.has(ymd);

    const rangeHasOccupied = (startYmd, endYmd) => {
        let cursor = parseYmd(startYmd);
        const end = parseYmd(endYmd);
        while (cursor < end) {
            if (isOccupied(toYmd(cursor)) || isPast(toYmd(cursor))) {
                return true;
            }
            cursor.setDate(cursor.getDate() + 1);
        }
        return false;
    };

    const fetchOccupied = async (year, month) => {
        const selectedAccommodation = accommodationSelect?.value || '';
        const resolvedOccupiedUrl = (occupiedUrl || '').replace('__ACCOMMODATION__', encodeURIComponent(selectedAccommodation));
        if (!resolvedOccupiedUrl || resolvedOccupiedUrl.includes('__ACCOMMODATION__')) {
            return { occupied: [] };
        }
        const url = new URL(resolvedOccupiedUrl, window.location.origin);
        url.searchParams.set('year', String(year));
        url.searchParams.set('month', String(month));
        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
            throw new Error('Could not load availability');
        }
        return response.json();
    };

    const renderCalendar = () => {
        titleEl.textContent = `${monthNames[viewMonth - 1]} ${viewYear}`;
        daysEl.innerHTML = '';

        const firstDay = new Date(viewYear, viewMonth - 1, 1);
        const startOffset = firstDay.getDay();
        const daysInMonth = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = 0; i < startOffset; i += 1) {
            const spacer = document.createElement('span');
            spacer.className = 'rp-availability-day is-empty';
            spacer.setAttribute('aria-hidden', 'true');
            daysEl.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const ymd = `${viewYear}-${pad(viewMonth)}-${pad(day)}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-availability-day';
            btn.textContent = String(day);
            btn.dataset.date = ymd;

            const past = isPast(ymd);
            const occupied = isOccupied(ymd);

            if (past) {
                btn.classList.add('is-past');
                btn.disabled = true;
            } else if (occupied) {
                btn.classList.add('is-occupied');
                btn.disabled = true;
                btn.title = 'Occupied';
            } else {
                btn.classList.add('is-available');
            }

            if (pendingCheckIn && ymd === pendingCheckIn) {
                btn.classList.add('is-check-in');
            }
            if (pendingCheckOut && ymd === pendingCheckOut) {
                btn.classList.add('is-check-out');
            }
            if (pendingCheckIn && pendingCheckOut && ymd > pendingCheckIn && ymd < pendingCheckOut) {
                btn.classList.add('is-in-range');
            }

            if (!btn.disabled) {
                btn.addEventListener('click', () => handleDayClick(ymd));
            }

            daysEl.appendChild(btn);
        }

        selectionEl.textContent = formatSelection();
        updateApplyState();
    };

    const handleDayClick = (ymd) => {
        if (isOccupied(ymd) || isPast(ymd)) {
            return;
        }

        if (!pendingCheckIn || selectingCheckout === false) {
            pendingCheckIn = ymd;
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        if (ymd <= pendingCheckIn) {
            pendingCheckIn = ymd;
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        if (rangeHasOccupied(pendingCheckIn, ymd)) {
            selectionEl.textContent = 'Those dates include occupied nights. Pick a different range.';
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        pendingCheckOut = ymd;
        selectingCheckout = false;
        renderCalendar();
    };

    const loadMonth = async () => {
        if (!occupiedUrl) {
            renderCalendar();
            return;
        }
        daysEl.classList.add('is-loading');
        try {
            const data = await fetchOccupied(viewYear, viewMonth);
            const occupied = data?.occupied || [];
            const unpaid = data?.unpaid_deposit || [];
            [...occupied, ...unpaid].forEach((date) => occupiedSet.add(date));
            renderCalendar();
        } catch {
            selectionEl.textContent = 'Unable to load availability. Please try again.';
        } finally {
            daysEl.classList.remove('is-loading');
        }
    };

    let lockedScrollY = 0;
    let scrollLocked = false;

    const lockPageScroll = () => {
        if (scrollLocked) return;
        lockedScrollY = window.scrollY || window.pageYOffset || 0;
        scrollLocked = true;
        document.body.dataset.rpAvailScrollY = String(lockedScrollY);
        document.body.style.position = 'fixed';
        document.body.style.top = `-${lockedScrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    };

    const unlockPageScroll = () => {
        if (!scrollLocked) return;
        const y = Number(document.body.dataset.rpAvailScrollY || lockedScrollY || 0);
        const root = document.documentElement;
        const previousScrollBehavior = root.style.scrollBehavior;
        scrollLocked = false;
        root.style.scrollBehavior = 'auto';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        delete document.body.dataset.rpAvailScrollY;
        window.scrollTo(0, y);
        requestAnimationFrame(() => {
            root.style.scrollBehavior = previousScrollBehavior;
        });
    };

    modalEl.addEventListener('show.bs.modal', lockPageScroll);
    modalEl.addEventListener('hidden.bs.modal', unlockPageScroll);

    const openCalendar = () => {
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }
        pendingCheckIn = checkInInput?.value || '';
        pendingCheckOut = checkOutInput?.value || '';
        selectingCheckout = Boolean(pendingCheckIn && !pendingCheckOut);
        occupiedSet = new Set();

        const base = pendingCheckIn ? parseYmd(pendingCheckIn) : new Date();
        viewYear = base.getFullYear();
        viewMonth = base.getMonth() + 1;

        populateUnitSelect();
        modal.show();
        loadMonth();
    };

    accommodationSelect?.addEventListener('change', () => {
        occupiedSet = new Set();
    });

    unitSelect?.addEventListener('change', () => {
        applySelectedUnit(unitSelect.value, { reload: true });
    });

    // Paint the page map immediately so guests see selectable rooms before opening the calendar.
    syncResortMaps(form.dataset.rpSelectedUnit || unitOptions[0]?.id);

    prevBtn?.addEventListener('click', () => {
        viewMonth -= 1;
        if (viewMonth < 1) {
            viewMonth = 12;
            viewYear -= 1;
        }
        loadMonth();
    });

    nextBtn?.addEventListener('click', () => {
        viewMonth += 1;
        if (viewMonth > 12) {
            viewMonth = 1;
            viewYear += 1;
        }
        loadMonth();
    });

    applyBtn?.addEventListener('click', () => {
        if (!pendingCheckIn || !pendingCheckOut) {
            return;
        }
        // Fill dates only; the booking form decides what happens on submit.
        checkInInput.value = pendingCheckIn;
        checkOutInput.value = pendingCheckOut;
        if (checkInDisplay) checkInDisplay.value = formatDisplay(pendingCheckIn);
        if (checkOutDisplay) checkOutDisplay.value = formatDisplay(pendingCheckOut);
        checkInInput.dispatchEvent(new Event('change', { bubbles: true }));
        modal.hide();
    });

    document.querySelectorAll('[data-rp-show-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openCalendar();
        });
    });

    form.querySelectorAll('[data-rp-open-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openCalendar();
        });
    });

    if (form.matches('[data-rp-browse-availability-form]')) {
        const errorEl = document.querySelector('[data-rp-browse-error]');
        const submitButton = form.querySelector('[type="submit"]');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const resultsGrid = document.querySelector('.rp-cottage-grid');
            if (!resultsGrid) return;

            const values = new FormData(form);
            const checkIn = String(values.get('check_in') || '');
            const checkOut = String(values.get('check_out') || '');
            if ((checkIn && !checkOut) || (!checkIn && checkOut)) {
                if (errorEl) {
                    errorEl.textContent = 'Choose both a check-in and check-out date, or clear both dates.';
                    errorEl.classList.remove('d-none');
                }
                return;
            }
            if (checkIn && checkOut && checkOut <= checkIn) {
                if (errorEl) {
                    errorEl.textContent = 'Check-out must be after check-in.';
                    errorEl.classList.remove('d-none');
                }
                return;
            }

            errorEl?.classList.add('d-none');
            const targetUrl = new URL(form.action, window.location.origin);
            values.forEach((value, key) => {
                if (String(value).trim() !== '') targetUrl.searchParams.set(key, String(value));
                else targetUrl.searchParams.delete(key);
            });

            const originalButtonText = submitButton?.textContent;
            const scrollY = window.scrollY;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Checking…';
            }
            form.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(targetUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                });
                if (!response.ok) throw new Error('Availability could not be updated. Please try again.');

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextGrid = nextDocument.querySelector('.rp-cottage-grid');
                if (!nextGrid) throw new Error('Availability results could not be displayed. Please try again.');

                resultsGrid.replaceWith(nextGrid);
                const currentPagination = document.getElementById('rpBrowsePagination');
                const nextPagination = nextDocument.getElementById('rpBrowsePagination');
                if (currentPagination && nextPagination) currentPagination.replaceWith(nextPagination);
                else if (currentPagination) currentPagination.remove();
                else if (nextPagination) nextGrid.insertAdjacentElement('afterend', nextPagination);

                window.history.replaceState({}, '', `${targetUrl.pathname}${targetUrl.search}`);
                requestAnimationFrame(() => window.scrollTo(0, scrollY));
            } catch (error) {
                if (errorEl) {
                    errorEl.textContent = error.message || 'Availability could not be updated. Please try again.';
                    errorEl.classList.remove('d-none');
                }
            } finally {
                form.removeAttribute('aria-busy');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText || 'Check Availability';
                }
            }
        });
    }

    // Restore the filters position only for non-AJAX GET forms.
    if (form.method?.toLowerCase() === 'get'
        && !form.hasAttribute('data-rp-no-auto-submit')
        && !form.matches('[data-rp-browse-availability-form]')) {
        form.addEventListener('submit', () => {
            try {
                sessionStorage.setItem('rp-browse-scroll', 'filters');
            } catch (e) { /* ignore */ }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        if (sessionStorage.getItem('rp-browse-scroll') !== 'filters') return;
        sessionStorage.removeItem('rp-browse-scroll');
    } catch (e) {
        return;
    }
    const target = document.getElementById('rpBrowseFilters');
    if (!target) return;
    const restore = () => target.scrollIntoView({ block: 'start' });
    requestAnimationFrame(restore);
    window.addEventListener('load', () => requestAnimationFrame(restore), { once: true });
});
