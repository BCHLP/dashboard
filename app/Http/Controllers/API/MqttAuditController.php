<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MqttAudit;
use Illuminate\Http\Request;

class MqttAuditController extends Controller
{
    public function __invoke(Request $request)
    {
        $json = json_decode($request->getContent(), true);
        foreach($json as $audit) {
            MqttAudit::create([
                'client_id' => $audit["clientId"],
                'when' => $audit["when"],
                'unusual' => $audit["unusual"],
                'message' => $audit["message"],
            ]);
        }
    }
}
