<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function upload(Request $request, FileService $fileService): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp'],
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required', 'integer'],
        ]);

        $file = $fileService->storeImage(
            $request->file('file'),
            $data['owner_type'],
            (int) $data['owner_id']
        );

        return response()->json([
            'file_id' => $file->id,
            'url' => $fileService->getPublicUrl($file->id),
        ]);
    }
}
