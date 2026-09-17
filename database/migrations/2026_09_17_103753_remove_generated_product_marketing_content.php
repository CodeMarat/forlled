<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->deleteGeneratedRecommendations();
        $this->clearGeneratedListingDescriptions();

        DB::table('products')
            ->whereIn('slug', [
                'hyalogy-p-effect-re-purance-wash',
                'hyalogy-lotion-for-daily-balance',
                'hyalogy-serum-for-radiance',
            ])
            ->delete();

        $this->clearExactValue(
            'recommendations_title',
            'HOME ROUTINE RECOMMENDATIONS',
        );
        $this->clearExactValue(
            'combine_with_title',
            'COMBINE WITH A TREATMENT',
        );
        $this->clearExactValue(
            'combine_left_title',
            'Recommended with professional treatments',
        );
        $this->clearExactValue(
            'combine_left_text',
            '<p>For optimal effectiveness, pair this product with a professional treatment protocol that supports the same skin concern.</p>',
        );
        $this->clearExactValue(
            'combine_right_title',
            'Elevate your results',
        );
        $this->clearExactValue(
            'combine_right_text',
            '<p>Use it consistently in the home routine to reinforce and prolong in-clinic results.</p>',
        );

        DB::table('products')
            ->whereIn('slug', [
                'hyalogy-creamy-wash',
                'hyalogy-purifying-lotion',
                'hyalogy-c20-essence',
                'hyalogy-royal-clay-pack',
                'hyalogy-daily-and-nightly-cream-for-eyes',
                'hyalogy-facial-massage-cream',
            ])
            ->where('is_favorite', true)
            ->update(['is_favorite' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Generated marketing content must not be restored.
    }

    private function clearExactValue(string $column, string $value): void
    {
        DB::table('products')
            ->where($column, $value)
            ->update([$column => null]);
    }

    private function deleteGeneratedRecommendations(): void
    {
        $productIds = DB::table('products')
            ->whereIn('slug', [
                'hyalogy-remover-for-point-make-up',
                'hyalogy-p-effect-clearance-cleansing',
                'hyalogy-creamy-wash',
            ])
            ->pluck('id');
        $relatedProductIds = DB::table('products')
            ->whereIn('slug', [
                'hyalogy-creamy-wash',
                'hyalogy-remover-for-point-make-up',
                'hyalogy-p-effect-re-purance-wash',
                'hyalogy-lotion-for-daily-balance',
                'hyalogy-serum-for-radiance',
            ])
            ->pluck('id');

        DB::table('product_related')
            ->whereIn('product_id', $productIds)
            ->whereIn('related_product_id', $relatedProductIds)
            ->delete();
    }

    private function clearGeneratedListingDescriptions(): void
    {
        DB::table('products')
            ->whereNotNull('listing_description')
            ->orderBy('id')
            ->chunkById(200, function ($products): void {
                foreach ($products as $product) {
                    $description = strip_tags((string) $product->description);
                    $decodedDescription = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $generatedValues = [
                        mb_substr($description, 0, 180),
                        rtrim(mb_substr($description, 0, 180)),
                        Str::limit($description, 180),
                        Str::limit($decodedDescription, 180),
                    ];

                    if (! in_array($product->listing_description, $generatedValues, true)) {
                        continue;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['listing_description' => null]);
                }
            });
    }
};
