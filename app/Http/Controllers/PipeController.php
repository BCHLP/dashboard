<?php

namespace App\Http\Controllers;

use App\Http\Requests\PipeRequest;
use App\Http\Resources\PipeResource;
use App\Models\Pipe;

class PipeController extends Controller
{
    public function index()
    {
        return PipeResource::collection(Pipe::all());
    }

    public function store(PipeRequest $request)
    {
        return new PipeResource(Pipe::create($request->validated()));
    }

    public function show(Pipe $pipe)
    {
        return new PipeResource($pipe);
    }

    public function update(PipeRequest $request, Pipe $pipe)
    {
        $pipe->update($request->validated());

        return new PipeResource($pipe);
    }

    public function destroy(Pipe $pipe)
    {
        $pipe->delete();

        return response()->json();
    }
}
