<?php

namespace Tests\Unit;

use App\Filament\Pages\AboutUsPage;
use App\Filament\Pages\BecomePartnerPage;
use App\Filament\Pages\ContactUsPage;
use App\Filament\Pages\HomePage;
use App\Filament\Pages\TechnologyPage;
use App\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Filament\Resources\LocationResource\Pages\CreateLocation;
use App\Filament\Resources\LocationResource\Pages\EditLocation;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\PartnerRequestResource\Pages\EditPartnerRequest;
use App\Filament\Resources\PartnerRequestResource\Pages\ListPartnerRequests;
use App\Filament\Resources\ProductCategoryResource\Pages\CreateProductCategory;
use App\Filament\Resources\ProductCategoryResource\Pages\EditProductCategory;
use App\Filament\Resources\ProductCategoryResource\Pages\ListProductCategories;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\TreatmentResource\Pages\CreateTreatment;
use App\Filament\Resources\TreatmentResource\Pages\EditTreatment;
use App\Filament\Resources\TreatmentResource\Pages\ListTreatments;
use Filament\Support\Enums\Alignment;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class FilamentActionsAlignmentTest extends TestCase
{
    public function test_resource_create_and_edit_pages_align_form_actions_to_the_end(): void
    {
        foreach ($this->formActionPageClasses() as $pageClass) {
            $this->assertSame(Alignment::End, $pageClass::$formActionsAlignment);
        }
    }

    public function test_resource_list_and_edit_pages_align_header_actions_to_the_end(): void
    {
        foreach ($this->headerActionPageClasses() as $pageClass) {
            $reflectionClass = new ReflectionClass($pageClass);
            $instance = $reflectionClass->newInstanceWithoutConstructor();
            $property = $reflectionClass->getProperty('headerActionsAlignment');
            $property->setAccessible(true);

            $this->assertSame(Alignment::End, $property->getValue($instance));
        }
    }

    public function test_treatment_edit_page_uses_form_footer_actions_instead_of_header_delete_action(): void
    {
        foreach ($this->createPageClasses() as $pageClass) {
            $formContentMethod = new ReflectionMethod($pageClass, 'getFormContentComponent');

            $this->assertSame($pageClass, $formContentMethod->getDeclaringClass()->getName());
        }

        foreach ($this->editPageClasses() as $pageClass) {
            $headerActionsMethod = new ReflectionMethod($pageClass, 'getHeaderActions');
            $formActionsMethod = new ReflectionMethod($pageClass, 'getFormActions');
            $formContentMethod = new ReflectionMethod($pageClass, 'getFormContentComponent');

            $this->assertSame($pageClass, $headerActionsMethod->getDeclaringClass()->getName());
            $this->assertSame($pageClass, $formActionsMethod->getDeclaringClass()->getName());
            $this->assertSame($pageClass, $formContentMethod->getDeclaringClass()->getName());
        }
    }

    public function test_singleton_and_embedded_settings_pages_override_form_content_component(): void
    {
        foreach ($this->singletonPageClasses() as $pageClass) {
            $formContentMethod = new ReflectionMethod($pageClass, 'getFormContentComponent');

            $this->assertSame($pageClass, $formContentMethod->getDeclaringClass()->getName());
        }

        $this->assertSame(
            ListBlogPosts::class,
            (new ReflectionMethod(ListBlogPosts::class, 'getBlogPageFormContentComponent'))->getDeclaringClass()->getName(),
        );
        $this->assertSame(
            ListLocations::class,
            (new ReflectionMethod(ListLocations::class, 'getLocationsPageFormContentComponent'))->getDeclaringClass()->getName(),
        );
        $this->assertSame(
            ListTreatments::class,
            (new ReflectionMethod(ListTreatments::class, 'getTreatmentsPageFormContentComponent'))->getDeclaringClass()->getName(),
        );
    }

    public function test_collapsible_settings_forms_keep_save_actions_inside_the_collapsible_section(): void
    {
        $projectRoot = dirname(__DIR__, 2);

        $this->assertStringNotContainsString(
            'footerActions(',
            file_get_contents($projectRoot.'/app/Filament/Resources/BlogPostResource/Pages/ListBlogPosts.php'),
        );
        $this->assertStringContainsString(
            'Actions::make([',
            file_get_contents($projectRoot.'/app/Filament/Resources/BlogPostResource/Pages/ListBlogPosts.php'),
        );

        $this->assertStringNotContainsString(
            'footerActions(',
            file_get_contents($projectRoot.'/app/Filament/Resources/LocationResource/Pages/ListLocations.php'),
        );
        $this->assertStringContainsString(
            'Actions::make([',
            file_get_contents($projectRoot.'/app/Filament/Resources/LocationResource.php'),
        );

        $this->assertStringNotContainsString(
            'footerActions(',
            file_get_contents($projectRoot.'/app/Filament/Resources/TreatmentResource/Pages/ListTreatments.php'),
        );
        $this->assertStringContainsString(
            'Actions::make([',
            file_get_contents($projectRoot.'/app/Filament/Resources/TreatmentResource.php'),
        );
    }

    /**
     * @return array<class-string>
     */
    protected function formActionPageClasses(): array
    {
        return [
            ...$this->createPageClasses(),
            ...$this->editPageClasses(),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function createPageClasses(): array
    {
        return [
            CreateBlogPost::class,
            CreateLocation::class,
            CreateProductCategory::class,
            CreateProduct::class,
            CreateTreatment::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function editPageClasses(): array
    {
        return [
            EditBlogPost::class,
            EditLocation::class,
            EditPartnerRequest::class,
            EditProductCategory::class,
            EditProduct::class,
            EditTreatment::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function headerActionPageClasses(): array
    {
        return [
            EditBlogPost::class,
            ListBlogPosts::class,
            EditLocation::class,
            ListLocations::class,
            EditPartnerRequest::class,
            ListPartnerRequests::class,
            EditProductCategory::class,
            ListProductCategories::class,
            EditProduct::class,
            ListProducts::class,
            EditTreatment::class,
            ListTreatments::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function singletonPageClasses(): array
    {
        return [
            HomePage::class,
            AboutUsPage::class,
            ContactUsPage::class,
            TechnologyPage::class,
            BecomePartnerPage::class,
        ];
    }
}
