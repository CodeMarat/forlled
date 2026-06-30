<?php

namespace App\Models;

use App\Models\Concerns\HasAdminAudit;
use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasAdminAudit;

    use HasFactory;

    protected $fillable = [
        'name',
        'group_name',
        'slug',
        'type_label',
        'hero_title',
        'hero_image',
        'hero_image_alt',
        'sort_order',
        'is_active',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
