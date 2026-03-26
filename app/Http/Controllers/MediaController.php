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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'owner_type' => ['required', 'string', 'in:user,child,activity'],
            'owner_id' => ['required', 'integer'],
        ]);

        if ($data['owner_type'] === 'child') {
            $child = \App\Models\Child::findOrFail($data['owner_id']);
            if ($request->user()->role !== 'admin' && $child->user_id !== $request->user()->id) {
                abort(403, 'Unauthorized to attach media to this child.');
            }
        } elseif ($data['owner_type'] === 'user') {
            if ($request->user()->role !== 'admin' && (int)$data['owner_id'] !== $request->user()->id) {
                abort(403, 'Unauthorized to attach media to this user profile.');
            }
        }

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
