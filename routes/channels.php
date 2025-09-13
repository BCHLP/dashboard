<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Node.{id}', function ($user, $id) {
    return true;
});
