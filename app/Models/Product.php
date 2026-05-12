<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_category_id',
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

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
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
