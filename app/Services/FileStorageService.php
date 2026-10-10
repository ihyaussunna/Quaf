<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        self::syncFileToPublicLocations($cleanDir.'/'.$filename);

        return '/storage/'.$cleanDir.'/'.$filename;
    }

    /**
     * Store an online competition submission file, preserving metadata and ensuring dual-sync.
     *
     * @return array{relative_path: string, file_name: string, file_type: string, file_size: int}
     */
    public static function storeSubmissionFile(UploadedFile $file, int|string $programId): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $filename = Str::random(40).'.'.$extension;
        $cleanDir = 'submissions/'.trim((string) $programId, '/');
        $relativePath = $cleanDir.'/'.$filename;

        // Store to public disk
        $file->storeAs($cleanDir, $filename, 'public');

        // Dual sync to public storage
        self::syncFileToPublicLocations($relativePath);

        return [
            'relative_path' => $relativePath,
            'file_name' => $originalName,
            'file_type' => $extension,
            'file_size' => (int) $file->getSize(),
        ];
    }

    /**
     * Resolves an uploaded file's absolute path across standard Laravel storage paths,
     * cPanel / Hostinger public_html directories, and recursive fallback search.
     * If found in internal storage but missing in public/storage, auto-syncs it immediately.
     */
    public static function resolveFilePath(string $path): ?string
    {
        $clean = trim($path);
        // Remove leading slashes and storage prefixes
        $clean = preg_replace('#^/?(storage/)?#', '', $clean);

        if (empty($clean)) {
            return null;
        }

        $candidates = [];
        try {
            $candidates[] = Storage::disk('public')->path($clean);
        } catch (\Throwable $e) {
        }

        $candidates = array_merge($candidates, [
            storage_path('app/public/'.$clean),
            public_path('storage/'.$clean),
            public_path($clean),
            storage_path('app/private/'.$clean),
            storage_path('app/'.$clean),
            storage_path($clean),
            base_path('public_html/storage/'.$clean),
            base_path('../public_html/storage/'.$clean),
            base_path('public_html/'.$clean),
            base_path('../public_html/'.$clean),
            base_path('public/'.$clean),
        ]);

        foreach ($candidates as $candidate) {
            if (@file_exists($candidate) && @is_file($candidate)) {
                self::ensurePublicCopy($candidate, $clean);

                return $candidate;
            }
        }

        // Recursive filename search fallback if the path or subfolder differs
        $filename = basename($clean);
        if (! empty($filename)) {
            $searchRoots = [
                storage_path('app/public'),
                storage_path('app/private'),
                storage_path('app'),
                public_path('storage'),
            ];

            if (@file_exists(base_path('public_html'))) {
                $searchRoots[] = base_path('public_html');
            }
            if (@file_exists(base_path('../public_html'))) {
                $searchRoots[] = base_path('../public_html');
            }

            foreach ($searchRoots as $rootDir) {
                if (! @is_dir($rootDir)) {
                    continue;
                }

                try {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($rootDir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    $iterator->setMaxDepth(4);

                    foreach ($iterator as $item) {
                        if ($item->isFile() && $item->getFilename() === $filename) {
                            $resolved = $item->getRealPath();
                            if ($resolved && @file_exists($resolved)) {
                                self::ensurePublicCopy($resolved, $clean);

                                return $resolved;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Skip permission or iteration errors
                }
            }
        }

        return null;
    }

    /**
     * Dual-sync file from storage/app/public to web accessible public paths.
     */
    protected static function syncFileToPublicLocations(string $relativePath): void
    {
        $source = storage_path('app/public/'.$relativePath);
        if (! @file_exists($source) || ! @is_file($source)) {
            return;
        }

        self::ensurePublicCopy($source, $relativePath);
    }

    /**
     * Copy an absolute source file into public/storage and public_html/storage if missing.
     */
    protected static function ensurePublicCopy(string $sourcePath, string $relativePath): void
    {
        try {
            $targets = [
                public_path('storage/'.$relativePath),
            ];

            if (@file_exists(base_path('public_html'))) {
                $targets[] = base_path('public_html/storage/'.$relativePath);
            }
            if (@file_exists(base_path('../public_html'))) {
                $targets[] = base_path('../public_html/storage/'.$relativePath);
            }

            foreach ($targets as $target) {
                if (@file_exists($target)) {
                    continue;
                }
                $targetDir = dirname($target);
                if (! @file_exists($targetDir)) {
                    @mkdir($targetDir, 0775, true);
                }
                @copy($sourcePath, $target);
            }
        } catch (\Throwable $e) {
            // Silently suppress file system restrictions
        }
    }
}
