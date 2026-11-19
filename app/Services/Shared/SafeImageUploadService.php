<?php

namespace App\Services\Shared;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SafeImageUploadService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

    /**
     * Store a validated uploaded image/document inside public/uploads/{folder}
     * using sanitized random filenames (prevents legacy arbitrary file upload RCE).
     */
    public function store(UploadedFile|TemporaryUploadedFile $file, string $folder, ?string $oldPath = null, bool $allowPdf = false): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');
        $allowed = $allowPdf ? self::ALLOWED_EXTENSIONS : ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (! in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException("Unsupported file type: .{$ext}");
        }

        $safeFolder = trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $folder), '/');
        $targetDir = public_path("uploads/{$safeFolder}");

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = time().'-'.Str::random(10).'.'.$ext;
        $relativePath = "uploads/{$safeFolder}/{$filename}";

        if ($file instanceof TemporaryUploadedFile) {
            copy($file->getRealPath(), $targetDir.DIRECTORY_SEPARATOR.$filename);
        } else {
            $file->move($targetDir, $filename);
        }

        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $relativePath;
    }

    public function delete(?string $relativePath): void
    {
        if (! $relativePath || ! str_starts_with($relativePath, 'uploads/')) {
            return;
        }

        $fullPath = realpath(public_path($relativePath));
        $uploadsRoot = realpath(public_path('uploads'));

        if ($fullPath && $uploadsRoot && str_starts_with($fullPath, $uploadsRoot) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
