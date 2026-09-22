<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'listing_description',
        'size',
        'hero_image',
        'side_image',
        'key_benefits',
        'detail_sections',
        'recommendations_title',
        'combine_with_title',
        'combine_left_title',
        'combine_left_text',
        'combine_right_title',
        'combine_right_text',
        'is_favorite',
        'sort_order',
        'is_active',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_product')
            ->withTimestamps();
    }

    public function productRecommendations(): HasMany
    {
        return $this->hasMany(ProductRecommendation::class)
            ->orderBy('sort_order');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key_benefits' => 'array',
            'detail_sections' => 'array',
            'is_favorite' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
