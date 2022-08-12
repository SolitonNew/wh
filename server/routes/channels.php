<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('logout', function ($user) {
    return true;
});

Broadcast::channel('device-changes', function ($user) {
    return true;
});
