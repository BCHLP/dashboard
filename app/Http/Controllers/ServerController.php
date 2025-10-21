<?php

namespace App\Http\Controllers;

use App\Actions\CreateServer;
use App\Enums\NodeTypeEnum;
use App\Http\Resources\NodeResource;
use App\Models\Node;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Node::where('node_type', NodeTypeEnum::SERVER)->get();

        return Inertia::render('servers', [
            'servers' => NodeResource::collection($servers),
        ]);
    }

    public function create()
    {
        return Inertia::render('servers-create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $action = app(CreateServer::class);

        return back()->with(['response' => $action($data['name'])]);
    }

    public function show(Node $server) {}

    public function edit(Node $server) {}

    public function update(Request $request, Node $server) {}

    public function destroy(Node $server) {}
}
