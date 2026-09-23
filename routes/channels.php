<?php

use Illuminate\Support\Facades\Broadcast;

// Only logged-in staff (kitchen/POS PIN session) may subscribe to the
// full-detail kitchen channel. The public 'queue-display' channel needs
// no entry here at all — it's a plain public Channel, not a PrivateChannel.
Broadcast::channel('kitchen-orders', function ($user) {
    return session()->has('staff_id');
});
