const OFFLINE_MESSAGE = 'Sin conexion. Esta accion requiere internet.';

const HEALTH_CHECK_URL = '/health';
const OFFLINE_CONFIRMATION_DELAY = 2000;
let isBackendOnline = navigator.onLine;
let offlineConfirmationTimer = null;

function showOfflineFeedback() {
    const banner = document.getElementById('offline-banner');

    if (banner) {
        banner.classList.remove('hidden');
    }

    alert(OFFLINE_MESSAGE);
}

function setConnectionStatus(isOnline) {
    isBackendOnline = isOnline;
    updateOfflineStatus();
}

async function checkBackendConnection() {
    try {
        const response = await originalFetch(HEALTH_CHECK_URL, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json'
            }
        });

        return response.ok;
    } catch {
        return false;
    }
}

function confirmOfflineAfterDelay() {
    if (offlineConfirmationTimer) {
        return;
    }

    offlineConfirmationTimer = setTimeout(async () => {
        const backendOnline = await checkBackendConnection();

        offlineConfirmationTimer = null;
        setConnectionStatus(backendOnline);
    }, OFFLINE_CONFIRMATION_DELAY);
}

function updateOfflineStatus() {
    const banner = document.getElementById('offline-banner');

    if (!banner) {
        return;
    }

    if (isBackendOnline) {
        banner.classList.add('hidden');
        document.body.classList.remove('pt-12');
    } else {
        banner.classList.remove('hidden');
        document.body.classList.add('pt-12');
    }

    document.querySelectorAll('[data-requires-online]').forEach((element) => {
        if (!isBackendOnline) {
            if (!element.disabled) {
                element.dataset.offlineDisabled = 'true';
                element.disabled = true;
            }
        } else if (element.dataset.offlineDisabled === 'true') {
            element.disabled = false;
            delete element.dataset.offlineDisabled;
        }

        element.classList.toggle('opacity-50', !isBackendOnline);
        element.classList.toggle('cursor-not-allowed', !isBackendOnline);
    });
}

const originalFetch = window.fetch.bind(window);

window.fetch = (resource, options = {}) => {
    const requestMethod = options.method || (resource instanceof Request ? resource.method : 'GET');
    const method = requestMethod.toUpperCase();

    if (!isBackendOnline && method !== 'GET') {
        showOfflineFeedback();
        return Promise.reject(new Error(OFFLINE_MESSAGE));
    }

    return originalFetch(resource, options).catch((error) => {
        confirmOfflineAfterDelay();
        throw error;
    });
};

document.addEventListener('submit', (event) => {
    if (isBackendOnline) {
        return;
    }

    const form = event.target;
    const method = (form.getAttribute('method') || 'POST').toUpperCase();

    if (method !== 'GET') {
        event.preventDefault();
        event.stopPropagation();
        showOfflineFeedback();
    }
}, true);

document.addEventListener('click', (event) => {
    const onlineOnlyElement = event.target.closest('[data-requires-online]');

    if (!onlineOnlyElement || isBackendOnline) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    showOfflineFeedback();
}, true);

window.addEventListener('online', async () => {
    setConnectionStatus(await checkBackendConnection());
});

window.addEventListener('offline', () => {
    setConnectionStatus(false);
});
window.addEventListener('load', updateOfflineStatus);
window.addEventListener('offline-status:refresh', updateOfflineStatus);
