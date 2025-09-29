<?php

namespace App\Http\Controllers;

use App\Enums\NodeTypeEnum;
use App\Models\Node;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SensorController extends Controller
{
    public function index()
    {
        $sensors = Node::where('node_type', NodeTypeEnum::SENSOR)->get();
        return Inertia::render('Sensors/Index', ['sensors' => $sensors]);
    }

    public function create()
    {
        return Inertia::render('Sensors/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string']);
        $data['node_type'] = NodeTypeEnum::SENSOR;
        $sensor = Node::create($data);
        return redirect()->route('sensors.index');
    }

    public function show(Node $sensor)
    {
    }

    public function edit(Node $sensor)
    {
        return Inertia::render('Sensors/Edit', ['sensor' => $sensor]);
    }

    public function update(Request $request, Node $sensor)
    {
        $data = $request->validate(['name' => 'required|string']);
        $sensor->update($data);
        return redirect()->route('sensors.index');
    }

    public function destroy(Node $sensor)
    {
        $sensor->delete();
        return redirect()->route('sensors.index');
    }
}
