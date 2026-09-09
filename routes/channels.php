<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('content-assistant.{userId}', fn ($user, int $userId): bool => (int) $user->id === $userId);
