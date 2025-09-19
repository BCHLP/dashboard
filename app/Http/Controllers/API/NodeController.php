<?php

namespace App\Http\Controllers\API;

use App\Actions\CreateRouter;
use App\Actions\CreateServer;
use App\Enums\NodeTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function index()
    {

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'node_type' => 'required|integer',
        ]);

        $nodeType = NodeTypeEnum::tryFrom($data['node_type']);
        switch ($nodeType) {
            case NodeTypeEnum::SERVER:
                $action = app(CreateServer::class);
                return $action($data['name']);
            case NodeTypeEnum::ROUTER:
                $action = app(CreateRouter::class);
                return $action($data['name']);
        }

        return response()->json([]);
    }

    public function show(Node $node)
    {
    }

    public function update(Request $request, Node $node)
    {
    }

    public function destroy(Node $node)
    {
    }
}
