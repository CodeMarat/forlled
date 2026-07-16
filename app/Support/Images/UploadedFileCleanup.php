<?php

namespace App\Support\Images;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UploadedFileCleanup
{
    /**
     * @var array<int, array<int, string>>
     */
    protected array $originalPathsByModel = [];

    public function rememberOriginalPaths(Model $model): void
    {
        $this->originalPathsByModel[$this->modelKey($model)] = $this->collectFilePaths($model->getOriginal());
    }

    public function deleteRemovedFiles(Model $model): void
    {
        $modelKey = $this->modelKey($model);
        $originalPaths = $this->originalPathsByModel[$modelKey] ?? $this->collectFilePaths($model->getOriginal());
        $currentPaths = $this->collectFilePaths($model->getAttributes());

        $this->deletePaths(array_values(array_diff($originalPaths, $currentPaths)));

        unset($this->originalPathsByModel[$modelKey]);
    }

    public function deleteModelFiles(Model $model): void
    {
        if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
            return;
        }

        $this->deletePaths($this->collectFilePaths($model->getAttributes()));

        unset($this->originalPathsByModel[$this->modelKey($model)]);
    }

    public function deletePathOnDisk(string $diskName, string $path): void
    {
        $normalizedPath = $this->normalizePath($path);

        if ($normalizedPath === null) {
            return;
        }

        $disk = Storage::disk($diskName);

        rescue(fn (): bool => $disk->delete($normalizedPath), report: false);

        $directory = trim(pathinfo($normalizedPath, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($normalizedPath, PATHINFO_FILENAME);
        $variantsDirectory = $this->joinPath($directory, (string) config('image_pipeline.variants_directory'));

        rescue(function () use ($disk, $variantsDirectory, $baseName): void {
            collect($disk->files($variantsDirectory))
                ->filter(fn (string $variantPath): bool => str_starts_with(pathinfo($variantPath, PATHINFO_FILENAME), "{$baseName}-"))
                ->each(fn (string $variantPath): bool => $disk->delete($variantPath));
        }, report: false);
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function deletePaths(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            $this->deletePath($path);
        }
    }

    protected function deletePath(string $path): void
    {
        $normalizedPath = $this->normalizePath($path);

        if ($normalizedPath === null) {
            return;
        }

        $disk = Storage::disk('public');

        rescue(fn (): bool => $disk->delete($normalizedPath), report: false);

        $directory = trim(pathinfo($normalizedPath, PATHINFO_DIRNAME), './');
        $baseName = pathinfo($normalizedPath, PATHINFO_FILENAME);
        $variantsDirectory = $this->joinPath($directory, (string) config('image_pipeline.variants_directory'));

        rescue(function () use ($disk, $variantsDirectory, $baseName): void {
            collect($disk->files($variantsDirectory))
                ->filter(fn (string $variantPath): bool => str_starts_with(pathinfo($variantPath, PATHINFO_FILENAME), "{$baseName}-"))
                ->each(fn (string $variantPath): bool => $disk->delete($variantPath));
        }, report: false);
    }

    protected function normalizePath(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            if (! is_string($parsedPath) || $parsedPath === '') {
                return null;
            }

            $path = $parsedPath;
        }

        $path = ltrim(explode('?', $path, 2)[0], '/');

        if (! $this->looksLikeStoredFilePath($path)) {
            return null;
        }

        return $path;
    }

    protected function looksLikeStoredFilePath(string $path): bool
    {
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        if (! str_contains($path, '/')) {
            return false;
        }

        return (bool) preg_match('/\.(jpg|jpeg|png|webp|gif|bmp|svg|avif|heic|heif|tif|tiff)$/i', $path);
    }

    /**
     * @return array<int, string>
     */
    protected function collectFilePaths(mixed $value): array
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return [];
            }

            if (in_array($trimmed[0], ['[', '{'], true)) {
                $decoded = json_decode($trimmed, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->collectFilePaths($decoded);
                }
            }

            return $this->looksLikeStoredFilePath($trimmed) ? [$trimmed] : [];
        }

        if (! is_array($value)) {
            return [];
        }

        $paths = [];

        foreach ($value as $item) {
            foreach ($this->collectFilePaths($item) as $path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    protected function joinPath(string $directory, string $path): string
    {
        return trim(collect([$directory, $path])->filter()->implode('/'), '/');
    }

    protected function modelKey(Model $model): int
    {
        return spl_object_id($model);
    }
}
