<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Sensor.{id}', function ($user, $id) {
    \Illuminate\Support\Facades\Log::debug("User {$user->id} has logged in as Sensor {$id}");
    return true;
});

