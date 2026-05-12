<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'product_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('product_category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('product_categories')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('products', 'listing_description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('listing_description')->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn('products', 'size')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('size')->nullable()->after('listing_description');
            });
        }

        if (! Schema::hasColumn('products', 'hero_image')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('hero_image')->nullable()->after('size');
            });
        }

        if (! Schema::hasColumn('products', 'side_image')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('side_image')->nullable()->after('hero_image');
            });
        }

        if (! Schema::hasColumn('products', 'key_benefits')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('key_benefits')->nullable()->after('side_image');
            });
        }

        if (! Schema::hasColumn('products', 'detail_sections')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('detail_sections')->nullable()->after('key_benefits');
            });
        }

        if (! Schema::hasColumn('products', 'recommendations_title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('recommendations_title')->nullable()->after('detail_sections');
            });
        }

        if (! Schema::hasColumn('products', 'combine_with_title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('combine_with_title')->nullable()->after('recommendations_title');
            });
        }

        if (! Schema::hasColumn('products', 'combine_left_title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('combine_left_title')->nullable()->after('combine_with_title');
            });
        }

        if (! Schema::hasColumn('products', 'combine_left_text')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('combine_left_text')->nullable()->after('combine_left_title');
            });
        }

        if (! Schema::hasColumn('products', 'combine_right_title')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('combine_right_title')->nullable()->after('combine_left_text');
            });
        }

        if (! Schema::hasColumn('products', 'combine_right_text')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('combine_right_text')->nullable()->after('combine_right_title');
            });
        }

        if (! Schema::hasColumn('products', 'is_favorite')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_favorite')->default(false)->after('description');
            });
        }

        if (! Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_favorite');
            });
        }

        if (! Schema::hasColumn('products', 'is_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            });
        }

        if (! $this->hasIndex('products', 'products_slug_unique') && Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_category_id')) {
                $table->dropConstrainedForeignId('product_category_id');
            }

            if ($this->hasIndex('products', 'products_slug_unique')) {
                $table->dropUnique(['slug']);
            }

            $columnsToDrop = collect([
                'slug',
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
                'sort_order',
                'is_active',
            ])->filter(fn (string $column): bool => Schema::hasColumn('products', $column))->all();

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
