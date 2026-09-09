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

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
