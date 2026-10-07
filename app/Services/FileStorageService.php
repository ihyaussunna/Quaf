<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Store an uploaded file safely to both Laravel public storage and the public/ web folder
     * ensuring immediate static availability across any local or production server environment.
     */
    public static function storePublicFile(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::random(40).'.'.strtolower($extension);
        $cleanDir = trim($directory, '/');

        // 1. Store via standard Laravel storage disk 'public'
        $file->storeAs($cleanDir, $filename, 'public');

        // 2. Dual-sync directly into public_path('storage/' . $cleanDir) for direct web server static serving
        try {
            $publicDir = public_path('storage/'.$cleanDir);
            if (! file_exists($publicDir)) {
                @mkdir($publicDir, 0775, true);
            }
            $sourceFile = storage_path('app/public/'.$cleanDir.'/'.$filename);
            $destinationFile = $publicDir.'/'.$filename;
            if (file_exists($sourceFile) && ! file_exists($destinationFile)) {
                @copy($sourceFile, $destinationFile);
            }
        } catch (\Throwable $e) {
            // Silently proceed if filesystem permissions restrict copy
        }

        return '/storage/'.$cleanDir.'/'.$filename;
    }
}
