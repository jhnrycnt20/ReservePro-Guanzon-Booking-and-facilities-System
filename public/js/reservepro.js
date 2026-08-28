document.addEventListener('DOMContentLoaded', () => {
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

    const toastEl = document.getElementById('rpToast');
    if (toastEl && window.bootstrap) {
        new bootstrap.Toast(toastEl).show();
    }

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

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    togglePassword?.addEventListener('click', () => {
        if (!passwordInput) return;
        const show = passwordInput.type === 'password';
        passwordInput.type = show ? 'text' : 'password';
        togglePassword.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        togglePasswordIcon?.classList.toggle('bi-eye', !show);
        togglePasswordIcon?.classList.toggle('bi-eye-slash', show);
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installBtn?.classList.remove('d-none');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        installBtn.classList.add('d-none');
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        installBtn?.classList.add('d-none');
    });

    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    const iosInstallHelp = document.getElementById('iosInstallHelp');

    if (isIos && !isStandalone) {
        iosInstallHelp?.classList.remove('d-none');
    }
});
