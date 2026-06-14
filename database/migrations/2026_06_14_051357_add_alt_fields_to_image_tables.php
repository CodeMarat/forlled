<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumns('about_us', [
            'hero_image_alt',
            'story_image_alt',
            'bottom_image_alt',
        ]);

        $this->addColumns('blog_pages', [
            'hero_image_alt',
        ]);

        $this->addColumns('blog_posts', [
            'featured_image_alt',
        ]);

        $this->addColumns('home_pages', [
            'hero_image_alt',
            'duo_left_image_alt',
            'duo_right_image_alt',
            'person_photo_alt',
            'gallery_image_1_alt',
            'gallery_image_2_alt',
            'gallery_image_3_alt',
            'gallery_image_4_alt',
        ]);

        $this->addColumns('product_categories', [
            'hero_image_alt',
        ]);

        $this->addColumns('products', [
            'hero_image_alt',
            'side_image_alt',
        ]);

        $this->addColumns('technology_pages', [
            'delivery_system_image_alt',
            'method_image_alt',
        ]);

        $this->addColumns('treatment_pages', [
            'hero_image_alt',
        ]);
    }

    public function down(): void
    {
        $this->dropColumns('about_us', [
            'hero_image_alt',
            'story_image_alt',
            'bottom_image_alt',
        ]);

        $this->dropColumns('blog_pages', [
            'hero_image_alt',
        ]);

        $this->dropColumns('blog_posts', [
            'featured_image_alt',
        ]);

        $this->dropColumns('home_pages', [
            'hero_image_alt',
            'duo_left_image_alt',
            'duo_right_image_alt',
            'person_photo_alt',
            'gallery_image_1_alt',
            'gallery_image_2_alt',
            'gallery_image_3_alt',
            'gallery_image_4_alt',
        ]);

        $this->dropColumns('product_categories', [
            'hero_image_alt',
        ]);

        $this->dropColumns('products', [
            'hero_image_alt',
            'side_image_alt',
        ]);

        $this->dropColumns('technology_pages', [
            'delivery_system_image_alt',
            'method_image_alt',
        ]);

        $this->dropColumns('treatment_pages', [
            'hero_image_alt',
        ]);
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function addColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                $table->string($column)->nullable();
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $columnsToDrop = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }
};
