<?php

namespace Tests\Unit;

use Tests\TestCase;

class ImagePipelineConfigTest extends TestCase
{
    public function test_image_pipeline_config_contains_expected_server_side_contract(): void
    {
        /** @var array<string, mixed> $config */
        $config = config('image_pipeline');
        /** @var array<string, mixed> $livewireConfig */
        $livewireConfig = config('livewire');

        $this->assertSame(102400, $config['max_upload_kb']);
        $this->assertSame(3840, $config['main_width']);
        $this->assertSame(88, $config['jpeg_quality']);
        $this->assertSame(86, $config['webp_quality']);
        $this->assertTrue($config['store_originals']);
        $this->assertSame('originals', $config['originals_directory']);
        $this->assertArrayNotHasKey('client_resize_width', $config);
        $this->assertArrayNotHasKey('client_transform_quality', $config);
        $this->assertSame(['required', 'file', 'max:102400'], $livewireConfig['temporary_file_upload']['rules']);
        $this->assertSame(10, $livewireConfig['temporary_file_upload']['max_upload_time']);
    }
}
