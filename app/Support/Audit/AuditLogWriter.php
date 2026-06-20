<?php

namespace App\Support\Audit;

use App\Models\AboutUs;
use App\Models\AuditLog;
use App\Models\BecomePartnerPage;
use App\Models\BlogPage;
use App\Models\BlogPost;
use App\Models\ContactUs;
use App\Models\ContactUsRequest;
use App\Models\FeaturedInPage;
use App\Models\HomePage;
use App\Models\Location;
use App\Models\LocationsPage;
use App\Models\PartnerRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SocialMedia;
use App\Models\TechnologyPage;
use App\Models\Treatment;
use App\Models\TreatmentPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogWriter
{
    /**
     * @var array<int, string>
     */
    protected array $excludedFields = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $maskedFields = [
        'password',
        'remember_token',
    ];

    public function write(Model $model, string $event): void
    {
        if (! auth()->check()) {
            return;
        }

        [$oldValues, $newValues] = match ($event) {
            'created' => [null, $this->filterAttributes($model->getAttributes())],
            'updated' => $this->updatedValues($model),
            'deleted' => [$this->filterAttributes($model->getAttributes()), null],
            default => [null, null],
        };

        $changedFields = array_values(array_unique(array_merge(
            array_keys($oldValues ?? []),
            array_keys($newValues ?? []),
        )));

        if ($changedFields === []) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'page' => $this->pageLabel($model),
            'record_title' => $this->recordTitle($model),
            'event' => $event,
            'changed_fields' => $changedFields,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'request_url' => request()?->getRequestUri(),
        ]);
    }

    /**
     * @return array{0: array<string, mixed> | null, 1: array<string, mixed> | null}
     */
    protected function updatedValues(Model $model): array
    {
        $newValues = $this->filterAttributes($model->getChanges());

        if ($newValues === []) {
            return [null, null];
        }

        $oldValues = Arr::only($this->filterAttributes($model->getPrevious()), array_keys($newValues));

        return [$oldValues, $newValues];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function filterAttributes(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->excludedFields, true)) {
                continue;
            }

            $filtered[$key] = $this->normalizeValue($key, $value);
        }

        return $filtered;
    }

    protected function normalizeValue(?string $key, mixed $value): mixed
    {
        if ($key !== null && in_array($key, $this->maskedFields, true)) {
            return '[REDACTED]';
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $nestedKey => $nestedValue) {
                $normalized[$nestedKey] = $this->normalizeValue(
                    is_string($nestedKey) ? $nestedKey : null,
                    $nestedValue,
                );
            }

            return $normalized;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }

    protected function pageLabel(Model $model): string
    {
        return match ($model::class) {
            HomePage::class => 'Home page',
            Product::class => 'Products',
            ProductCategory::class => 'Product Categories',
            BlogPage::class => 'Blog posts page settings',
            BlogPost::class => 'Blog posts',
            TechnologyPage::class => 'Technology',
            BecomePartnerPage::class => 'Become Partner Page',
            ContactUs::class => 'Contact Us Page',
            ContactUsRequest::class => 'Contact Us Requests',
            AboutUs::class => 'About Us',
            FeaturedInPage::class => 'Featured In',
            SocialMedia::class => 'Social Media',
            LocationsPage::class => 'Locations page settings',
            Location::class => 'Locations',
            PartnerRequest::class => 'Partner Requests',
            TreatmentPage::class => 'Treatments page settings',
            Treatment::class => 'Treatments',
            User::class => 'Users',
            default => Str::headline(class_basename($model)),
        };
    }

    protected function recordTitle(Model $model): string
    {
        $singletonLabels = match ($model::class) {
            HomePage::class => 'Home page settings',
            BlogPage::class => 'Blog page settings',
            TechnologyPage::class => 'Technology page settings',
            BecomePartnerPage::class => 'Become Partner Page settings',
            ContactUs::class => 'Contact Us Page settings',
            AboutUs::class => 'About Us settings',
            FeaturedInPage::class => 'Featured In settings',
            SocialMedia::class => 'Social Media settings',
            LocationsPage::class => 'Locations page settings',
            TreatmentPage::class => 'Treatments page settings',
            default => null,
        };

        if ($singletonLabels !== null) {
            return $singletonLabels;
        }

        foreach (['title', 'name', 'meta_title', 'slug', 'company', 'email'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (filled($value)) {
                return Str::limit((string) $value, 120);
            }
        }

        if ($model instanceof Location) {
            return Str::limit(trim(implode(' / ', array_filter([
                $model->country,
                $model->city,
                $model->company,
            ]))), 120);
        }

        return '#'.$model->getKey();
    }
}
