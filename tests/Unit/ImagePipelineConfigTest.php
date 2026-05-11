<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ImagePipelineConfigTest extends TestCase
{
    public function test_image_pipeline_config_contains_expected_frontend_contract(): void
    {
        /** @var array<string, mixed> $config */
        $config = require dirname(__DIR__, 2).'/config/image_pipeline.php';

        $this->assertSame(51200, $config['max_upload_kb']);
        $this->assertSame(3200, $config['main_width']);
        $this->assertSame(90, $config['quality']);
        $this->assertSame(3200, $config['client_resize_width']);
        $this->assertSame(90, $config['client_transform_quality']);
        $this->assertSame([
            'desktop' => 2560,
            'tablet' => 1600,
            'mobile' => 960,
        ], $config['variants']);
    }
}
