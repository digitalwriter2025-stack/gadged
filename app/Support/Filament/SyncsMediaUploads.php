<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Illuminate\Support\Facades\Storage;

trait SyncsMediaUploads
{
    /**
     * Synchronize a file upload field with a Spatie media collection.
     */
    protected function syncMediaCollection(string $fieldName, string $collectionName, bool $isMultiple = false): void
    {
        $uploaded = $this->data[$fieldName] ?? null;

        if (blank($uploaded)) {
            return;
        }

        $disk = Storage::disk('public');

        if ($isMultiple && is_array($uploaded)) {
            foreach ($uploaded as $path) {
                if (is_string($path) && $disk->exists($path)) {
                    $this->record->addMedia($disk->path($path))->toMediaCollection($collectionName);
                }
            }

            return;
        }

        if (is_string($uploaded) && $disk->exists($uploaded)) {
            $this->record->clearMediaCollection($collectionName);
            $this->record->addMedia($disk->path($uploaded))->toMediaCollection($collectionName);
        }
    }
}
