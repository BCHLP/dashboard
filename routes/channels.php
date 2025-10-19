<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Node.{id}', function ($user, $id) {
    return true;
});

Broadcast::channel('NewDatapointEvent.{node}', function ($user, $node) {
    return true;
});

Broadcast::channel('NewNodePhoto.{node}', function ($user, $nodePhoto) {
    return true;
});
