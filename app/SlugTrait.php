<?php

namespace App;

use Illuminate\Support\Str;

trait SlugTrait
{
    protected static function bootSlugTrait(): void
    {
        static::saving(function ($model) {
            $source = $model->slugSource();
            if (!$model->exists || $model->isDirty($source)) {
                $model->slug = $model->generateSlug();
            }
        });
    }

    protected function generateSlug(): string
    {
        $baseSlug = Str::slug($this->{$this->slugSource()});

        $slugs = static::where('slug', 'like', $baseSlug.'%')
            ->pluck('slug');

        if (!$slugs->contains($baseSlug)) {
            return $baseSlug;
        }

        $max = 0;

        foreach ($slugs as $slug) {
            if (preg_match('/^'.preg_quote($baseSlug, '/').'\-(\d+)$/', $slug, $m)) {
                $max = max($max, (int)$m[1]);
            }
        }

        return $baseSlug.'-'.($max + 1);
    }

    protected function slugSource(): string
    {
        return 'name';
    }
}
