<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EngineeringPlanFileService
{
    private const DISK      = 'public';
    private const DIRECTORY = 'engineering-plans';

    /**
     * Store the uploaded file and return its metadata.
     *
     * @return array{ name: string, path: string, type: string }
     */
    public function store(UploadedFile $file): array
    {
        $path = $file->store(self::DIRECTORY, self::DISK);

        if ($path === false) {
            throw new \RuntimeException('Failed to write file to storage.');
        }

        return [
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => strtoupper($file->getClientOriginalExtension()),
        ];
    }

    /**
     * Delete a file — used for rollback on DB failure.
     */
    public function delete(string $path): void
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}