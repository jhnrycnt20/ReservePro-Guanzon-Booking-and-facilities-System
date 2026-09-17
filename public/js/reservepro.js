document.addEventListener('DOMContentLoaded', () => {
    const publicNav = document.querySelector('.rp-public-nav');

    if (publicNav) {
        const scrollThreshold = 120;
        let navIsScrolled = false;

        const updateNavOnScroll = () => {
            const shouldBeScrolled = window.scrollY > scrollThreshold;
            if (shouldBeScrolled !== navIsScrolled) {
                navIsScrolled = shouldBeScrolled;
                publicNav.classList.toggle('rp-nav-scrolled', navIsScrolled);
            }
        };

        updateNavOnScroll();
        window.addEventListener('scroll', updateNavOnScroll, { passive: true });
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

    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    };

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        backdrop?.classList.toggle('show');
    });

    backdrop?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-rp-history-back]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    });

    const openGCashApp = ({ number, amount }) => {
        const phone = String(number || '').replace(/\D/g, '');
        const payAmount = String(amount || '').trim();

        if (phone && navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(phone).catch(() => {});
        }

        const isAndroid = /Android/i.test(navigator.userAgent);
        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

        // Best-effort deep links. Personal receive QR can't auto-charge without GCash merchant API,
        // so we open the app and copy the number for Send Money.
        const candidates = [];
        if (phone) {
            candidates.push(`gcash://send?phone=${encodeURIComponent(phone)}${payAmount ? `&amount=${encodeURIComponent(payAmount)}` : ''}`);
            candidates.push(`gcash://express/send?phone=${encodeURIComponent(phone)}`);
        }
        candidates.push('gcash://');

        if (isAndroid) {
            candidates.push('intent://send#Intent;scheme=gcash;package=com.globe.gcash.android;end');
            candidates.push('https://play.google.com/store/apps/details?id=com.globe.gcash.android');
        } else if (isIOS) {
            candidates.push('https://apps.apple.com/app/gcash/id520020791');
        } else {
            candidates.push('https://www.gcash.com/');
        }

        let opened = false;
        const tryOpen = (url) => {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            document.body.appendChild(iframe);
            setTimeout(() => iframe.remove(), 1500);
            window.location.href = url;
            opened = true;
        };

        tryOpen(candidates[0]);

        // If the custom scheme fails on desktop/mobile webview, fall back shortly.
        setTimeout(() => {
            if (document.hidden || opened === false) return;
            const fallback = candidates[candidates.length - 1];
            if (fallback && fallback !== candidates[0]) {
                window.location.href = fallback;
            }
        }, 1200);

        const note = phone
            ? `GCash number ${phone} copied. Open Send Money, paste the number${payAmount ? `, enter ₱${payAmount}` : ''}, then return here to upload proof.`
            : 'Opening GCash. After paying, return here to upload your proof.';

        if (window.bootstrap && document.getElementById('rpToast')) {
            // optional toast container may not exist
        }
        window.alert(note);
    };

    document.querySelectorAll('[data-rp-open-gcash]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openGCashApp({
                number: button.dataset.gcashNumber || '09505584607',
                amount: button.dataset.gcashAmount || '',
            });

            const methodSelect = document.getElementById('paymentMethod');
            if (methodSelect) {
                methodSelect.value = 'gcash';
                methodSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });

    document.querySelectorAll('[data-rp-print-receipt]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.rpPrintReceipt || '#rpPaymentReceiptPrint');
            if (!target) return;

            const clone = target.cloneNode(true);
            clone.querySelectorAll('.no-print').forEach((el) => el.remove());

            // Do not use noopener here — it makes window.open() return null.
            const printWindow = window.open('', '_blank', 'width=720,height=900');
            if (!printWindow) {
                window.alert('Please allow pop-ups to print the receipt.');
                return;
            }

            printWindow.document.write(`<!DOCTYPE html><html><head><title>Payment Receipt</title>
                <style>
                    @page { size: A4; margin: 16mm; }
                    html, body { margin: 0; padding: 0; height: auto; overflow: visible; }
                    body { font-family: Georgia, "Times New Roman", serif; color: #111; }
                    .rp-receipt-card { max-width: 480px; margin: 0 auto; border: 1px solid #ddd; padding: 24px; }
                    .rp-receipt-brand { font-size: 22px; font-weight: 700; }
                    .rp-receipt-meta { color: #666; font-size: 14px; }
                    .rp-receipt-row { display: flex; justify-content: space-between; gap: 16px; margin: 8px 0; }
                    hr { border: none; border-top: 1px solid #ddd; margin: 16px 0; }
                </style></head><body>${clone.outerHTML}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        });
    });

    document.querySelectorAll('[data-rp-payment-form]').forEach((form) => {
        const amountInput = form.querySelector('#paymentAmount');
        const methodSelect = form.querySelector('#paymentMethod');
        const refWrap = form.querySelector('[data-rp-pay-ref-wrap]');
        const proofWrap = form.querySelector('[data-rp-pay-proof-wrap]');
        const qrWrap = form.querySelector('[data-rp-pay-qr-wrap]');

        form.querySelectorAll('[data-rp-pay-amount]').forEach((button) => {
            button.addEventListener('click', () => {
                if (amountInput) {
                    amountInput.value = button.dataset.rpPayAmount || '';
                    amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        });

        const syncProofFields = () => {
            const method = methodSelect?.value || '';
            const needsProof = method === 'gcash';
            if (refWrap) refWrap.classList.toggle('d-none', !needsProof && method === 'cash');
            if (proofWrap) proofWrap.classList.toggle('d-none', !needsProof);
            if (qrWrap) qrWrap.classList.toggle('d-none', !needsProof);
            const refInput = refWrap?.querySelector('input');
            const proofInput = proofWrap?.querySelector('input');
            if (refInput) refInput.required = needsProof;
            if (proofInput) proofInput.required = needsProof;
        };

        methodSelect?.addEventListener('change', syncProofFields);
        syncProofFields();
    });

    document.querySelectorAll('[data-rp-live-filter]').forEach((form) => {
        let timer = null;
        const applyFilter = () => {
            const action = form.getAttribute('action') || window.location.pathname;
            const params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach((key) => {
                if (!(params.get(key) || '').trim()) {
                    params.delete(key);
                }
            });
            const query = params.toString();
            window.location.assign(query ? `${action}?${query}` : action);
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

    const closeNavOverlay = () => {
        navMenuBtn?.classList.remove('is-open');
        navOverlay?.classList.remove('is-open');
        navMenuBtn?.setAttribute('aria-expanded', 'false');
    };

    navMenuBtn?.addEventListener('click', () => {
        const isOpen = navMenuBtn.classList.toggle('is-open');
        navOverlay?.classList.toggle('is-open', isOpen);
        navMenuBtn.setAttribute('aria-expanded', String(isOpen));
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

    window.addEventListener('scroll', () => {
        if (navOverlay?.classList.contains('is-open') && publicNav?.classList.contains('rp-nav-scrolled')) {
            closeNavOverlay();
        }
    }, { passive: true });

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

    if (loginModalEl?.getAttribute('data-rp-autoshow') === '1' && window.bootstrap) {
        new bootstrap.Modal(loginModalEl).show();
    }
    if (registerModalEl?.getAttribute('data-rp-autoshow') === '1' && window.bootstrap) {
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
        setTimeout(dismiss, 4000);
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
        const adults = Math.max(1, parseInt(adultsEl.value || '1', 10) || 1);
        const children = Math.max(0, parseInt(childrenEl.value || '0', 10) || 0);
        const total = adults + children;

        adultsEl.value = String(adults);
        childrenEl.value = String(children);
        totalEl.value = String(total);

        if (capacity > 0) {
            adultsEl.max = String(capacity);
            childrenEl.max = String(capacity);
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

    document.querySelectorAll('[data-rp-promo-input]').forEach((input) => {
        const wrap = input.closest('form') || document;
        const button = wrap.querySelector('[data-rp-promo-check]');
        const feedback = wrap.querySelector('[data-rp-promo-feedback]');
        const estimate = wrap.querySelector('[data-rp-promo-estimate]');
        const totalEl = wrap.querySelector('[data-calc-total]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const nightsFromForm = () => {
            const checkIn = wrap.querySelector('[data-calc-check-in], [name="check_in_date"]')?.value;
            const checkOut = wrap.querySelector('[data-calc-check-out], [name="check_out_date"]')?.value;
            if (!checkIn || !checkOut) return 1;
            const start = new Date(`${checkIn}T00:00:00`);
            const end = new Date(`${checkOut}T00:00:00`);
            const days = Math.round((end - start) / 86400000);
            return Math.max(1, days);
        };

        const applyCheck = async () => {
            const code = (input.value || '').trim();
            if (!code) {
                if (feedback) feedback.textContent = 'Leave blank if you don’t have a promo.';
                if (estimate) estimate.textContent = '';
                return;
            }

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
                        rate: Number(input.dataset.rpPromoRate || totalEl?.dataset.calcRate || 0),
                        nights: nightsFromForm(),
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    const message = data?.errors?.promo_code?.[0] || data?.message || 'Invalid promo code.';
                    if (feedback) feedback.textContent = message;
                    if (estimate) estimate.textContent = '';
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
                if (feedback) feedback.textContent = 'Could not validate promo code right now.';
            }
        };

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

    document.querySelectorAll('[data-rp-register-form]').forEach((form) => {
        const field = (name) => form.querySelector(`[data-rp-register-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-register-feedback="${name}"]`);
        const hint = (name) => form.querySelector(`[data-rp-register-hint="${name}"]`);
        const touched = new Set();

        const normalizePhone = (value) => String(value || '').replace(/[\s\-()]/g, '');

        const validators = {
            name: (value) => {
                const trimmed = value.trim();
                if (!trimmed) return 'Please enter your full name.';
                if (trimmed.length < 2) return 'Full name must be at least 2 characters.';
                if (!/^[\p{L}]+(?:[ '\-.][\p{L}]+)*$/u.test(trimmed)) {
                    return 'Full name may only include letters, spaces, hyphens, and apostrophes.';
                }
                return '';
            },
            email: (value) => {
                const trimmed = value.trim();
                if (!trimmed) return 'Please enter your email address.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                    return 'Enter a valid email address (e.g. you@email.com).';
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
            address: (value) => {
                if (value.trim().length > 1000) return 'Address must not exceed 1000 characters.';
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
            const showValid = !message && name !== 'address' && (force || touched.has(name));

            input.classList.toggle('is-invalid', showError);
            input.classList.toggle('is-valid', showValid);

            if (showError) {
                messageEl.textContent = message;
                messageEl.classList.add('d-block');
                hintEl?.classList.add('d-none');
            } else {
                messageEl.classList.remove('d-block');
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
            return ['name', 'email', 'phone', 'address', 'password', 'password_confirmation']
                .map((name) => validateField(name, options))
                .every(Boolean);
        };

        ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => {
            const input = field(name);
            if (!input) return;

            input.addEventListener('input', () => {
                if (name === 'password') {
                    updatePasswordChecks();
                    if (touched.has('password_confirmation') || field('password_confirmation')?.value) {
                        validateField('password_confirmation');
                    }
                }
                if (touched.has(name) || input.classList.contains('is-invalid')) {
                    validateField(name);
                } else if (name === 'password') {
                    updatePasswordChecks();
                }
            });

            input.addEventListener('blur', () => {
                touched.add(name);
                validateField(name, { force: true });
            });
        });

        form.addEventListener('submit', (event) => {
            ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => touched.add(name));
            if (!validateAll({ force: true })) {
                event.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                firstInvalid?.focus();
            }
        });

        updatePasswordChecks();
        if (form.querySelector('.is-invalid')) {
            ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => {
                const input = field(name);
                if (input?.classList.contains('is-invalid')) {
                    touched.add(name);
                    validateField(name, { force: true });
                }
            });
        }
    });

    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const scope = button.closest('.rp-login-modal-body, .rp-auth-card') || document;
            const emailInput = scope.querySelector('input[type="email"]') || document.getElementById('email');
            const demoPasswordInput = scope.querySelector('input[type="password"]') || document.getElementById('password');
            if (!emailInput || !demoPasswordInput) return;

            emailInput.value = button.dataset.demoEmail || '';
            demoPasswordInput.value = button.dataset.demoPassword || '';
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            demoPasswordInput.dispatchEvent(new Event('input', { bubbles: true }));
            emailInput.focus();
        });
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');
    const iosInstallHelp = document.getElementById('iosInstallHelp');
    const androidInstallHelp = document.getElementById('androidInstallHelp');
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    if (isStandalone) {
        installBtn?.classList.add('d-none');
        iosInstallHelp?.classList.add('d-none');
        androidInstallHelp?.classList.add('d-none');
    } else if (isIos) {
        iosInstallHelp?.classList.remove('d-none');
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installBtn?.classList.remove('d-none');
        androidInstallHelp?.classList.add('d-none');
    });

    installBtn?.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            installBtn.classList.add('d-none');
            return;
        }

        if (isIos) {
            iosInstallHelp?.classList.remove('d-none');
            iosInstallHelp?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        androidInstallHelp?.classList.remove('d-none');
        androidInstallHelp?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        installBtn?.classList.add('d-none');
        iosInstallHelp?.classList.add('d-none');
        androidInstallHelp?.classList.add('d-none');
    });

    initAvailabilityCalendar();
});

function initAvailabilityCalendar() {
    const modalEl = document.getElementById('rpAvailabilityModal');
    const form = document.querySelector('[data-rp-availability-form]');
    const calendarRoot = document.querySelector('[data-rp-availability-calendar]');

    if (!modalEl || !form || !calendarRoot || !window.bootstrap) {
        return;
    }

    const occupiedUrl = form.dataset.occupiedUrl;
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
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

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
        const url = new URL(occupiedUrl, window.location.origin);
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

    const openCalendar = () => {
        pendingCheckIn = checkInInput?.value || '';
        pendingCheckOut = checkOutInput?.value || '';
        selectingCheckout = Boolean(pendingCheckIn && !pendingCheckOut);
        occupiedSet = new Set();

        const base = pendingCheckIn ? parseYmd(pendingCheckIn) : new Date();
        viewYear = base.getFullYear();
        viewMonth = base.getMonth() + 1;

        modal.show();
        loadMonth();
    };

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
        checkInInput.value = pendingCheckIn;
        checkOutInput.value = pendingCheckOut;
        if (checkInDisplay) checkInDisplay.value = formatDisplay(pendingCheckIn);
        if (checkOutDisplay) checkOutDisplay.value = formatDisplay(pendingCheckOut);
        checkInInput.dispatchEvent(new Event('change', { bubbles: true }));
        modal.hide();
        if (!form.hasAttribute('data-rp-no-auto-submit')) {
            form.submit();
        }
    });

    document.querySelectorAll('[data-rp-show-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openCalendar();
        });
    });

    form.querySelectorAll('[data-rp-open-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openCalendar();
        });
    });
}
