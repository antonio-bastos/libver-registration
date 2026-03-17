<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function storeImage(UploadedFile $file, string $ownerType, int $ownerId): File
    {
        $path = $file->store('uploads', ['disk' => 'public']);

        return File::query()->create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'storage_path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'type' => 'image',
        ]);
    }

    public function getPublicUrl(int $fileId): string
    {
        $file = File::query()->whereKey($fileId)->firstOrFail();

        return Storage::disk('public')->url($file->storage_path);
    }
}
