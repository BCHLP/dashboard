<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Node;
use App\Models\NodePhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateNodePhoto
{
    public function __invoke(string $base64String, Node $node, bool $faceDetected): bool
    {
        // Remove the data URI scheme if present (e.g., "data:image/png;base64,")
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            $imageType = $matches[1];
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
        } else {
            $imageType = 'png'; // default
        }

        // we only store pngs
        if ($imageType !== 'png') {
            return false;
        }

        // Decode the base64 string
        $imageData = base64_decode($base64String);

        // Check if decoding was successful
        if ($imageData === false) {
            return false;
        }

        $outputPath = 'photos/'.Str::uuid()->toString().'.png';

        // Save the image to disk
        $result = Storage::put($outputPath, $imageData);

        if ($result === false) {
            return false;
        }

        $photo = NodePhoto::create([
            'node_id' => $node->id,
            'location' => $outputPath,
            'face_detected' => $faceDetected,
        ]);

        // return true if photo record was created
        return ! (is_null($photo));
    }
}
