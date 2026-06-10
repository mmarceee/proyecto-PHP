function updateOfflineStatus() {
    const banner = document.getElementById('offline-banner');

    if (!banner) {
        return;
    }

    if (navigator.onLine) {
        banner.classList.add('hidden');
        document.body.classList.remove('pt-12');
    } else {
        banner.classList.remove('hidden');
        document.body.classList.add('pt-12');
    }
}

window.addEventListener('online', updateOfflineStatus);
window.addEventListener('offline', updateOfflineStatus);
window.addEventListener('load', updateOfflineStatus);