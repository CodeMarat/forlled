<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use App\Support\Products\ProductType;
use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'group_name',
        'type',
        'slug',
        'type_label',
        'hero_title',
        'hero_image',
        'sort_order',
        'is_active',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category_product')
            ->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
