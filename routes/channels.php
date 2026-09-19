<?php

use Illuminate\Support\Facades\Broadcast;

// Channel publik "queue" — bebas didengarkan layar display, operator, kios.
Broadcast::channel('queue', fn () => true);
