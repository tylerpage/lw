<?php

use Illuminate\Support\Facades\Broadcast;

$broadcastConnection = (string) config('broadcasting.default', 'null');

if (
    in_array($broadcastConnection, ['reverb', 'pusher'], true)
    && ! filled(config("broadcasting.connections.{$broadcastConnection}.key"))
) {
    return;
}

Broadcast::channel('content-assistant.{userId}', fn ($user, int $userId): bool => (int) $user->id === $userId);
