<?php

namespace App\Http\Controllers;

use App\Models\NodePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function __invoke(Request $request, string $path)
    {
        $photo = NodePhoto::where('location', $path)->first();
        abort_if(is_null($photo), 404);

        $filePath = Storage::path($photo->location);

        abort_if(!file_exists($filePath), 404);

        return response()->file($filePath);
    }
}
