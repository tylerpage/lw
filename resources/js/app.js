import '../css/app.css';

document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-analytics-event]');
    if (!target || !window.lwTrack) {
        return;
    }

    window.lwTrack(target.dataset.analyticsEvent, {
        placement: target.dataset.analyticsPlacement || 'unknown',
        label: target.dataset.analyticsLabel || target.textContent?.trim(),
    });
});
