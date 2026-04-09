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
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',  // 5MB
                'image',     // Must be valid image
            ],
            'owner_type' => ['required', 'string', 'in:user,child,activity'],
            'owner_id' => ['required', 'integer', 'min:1'],
        ]);

        // Verify file is actually an image (not just extension spoofing)
        $uploadedFile = $request->file('file');
        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return response()->json(['error' => 'Invalid file upload'], 422);
        }

        // Double-check MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($uploadedFile->getRealPath());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            return response()->json(['error' => 'File MIME type is not allowed'], 422);
        }

        if ($data['owner_type'] === 'child') {
            $child = \App\Models\Child::findOrFail($data['owner_id']);
            if ($request->user()->role !== 'admin' && $child->user_id !== $request->user()->id) {
                abort(403, 'Unauthorized to attach media to this child.');
            }
        } elseif ($data['owner_type'] === 'user') {
            if ($request->user()->role !== 'admin' && (int)$data['owner_id'] !== $request->user()->id) {
                abort(403, 'Unauthorized to attach media to this user profile.');
            }
        } elseif ($data['owner_type'] === 'activity') {
            // Only admin or instructor can upload activity media
            if (!in_array($request->user()->role, ['admin', 'instructor'], true)) {
                abort(403, 'Unauthorized to attach media to activities.');
            }
        }

        $file = $fileService->storeImage(
            $uploadedFile,
            $data['owner_type'],
            (int) $data['owner_id']
        );

        return response()->json([
            'file_id' => $file->id,
            'url' => $fileService->getPublicUrl($file->id),
        ]);
    }
}
