<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<int, string>> */
    private array $columns = [
        'about_us' => ['hero_image_alt', 'story_image_alt', 'bottom_image_alt'],
        'blog_pages' => ['hero_image_alt'],
        'blog_posts' => ['featured_image_alt'],
        'home_pages' => [
            'hero_image_alt',
            'duo_left_image_alt',
            'duo_right_image_alt',
            'person_photo_alt',
            'gallery_image_1_alt',
            'gallery_image_2_alt',
            'gallery_image_3_alt',
            'gallery_image_4_alt',
        ],
        'product_categories' => ['hero_image_alt'],
        'products' => ['hero_image_alt', 'side_image_alt'],
        'technology_pages' => ['delivery_system_image_alt', 'method_image_alt'],
        'treatment_pages' => ['hero_image_alt'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $tableName => $columns) {
            $this->dropColumns($tableName, $columns);
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $tableName => $columns) {
            $this->addColumns($tableName, $columns);
        }
    }

    /** @param  array<int, string>  $columns */
    private function dropColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }

    /** @param  array<int, string>  $columns */
    private function addColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
            foreach ($columns as $column) {
                if (! Schema::hasColumn($tableName, $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }
};
