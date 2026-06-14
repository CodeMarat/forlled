<?php

namespace Tests\Unit;

use App\Support\Slugs\SlugGenerator;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    public function test_it_generates_slug_from_single_title(): void
    {
        $this->assertSame('beauty-signals', SlugGenerator::fromParts(['Beauty Signals']));
    }

    public function test_it_generates_slug_from_multiple_parts(): void
    {
        $this->assertSame(
            'russia-moscow-ooo-daiseiko',
            SlugGenerator::fromParts(['Russia', 'Moscow', 'OOO "Daiseiko"']),
        );
    }

    public function test_it_ignores_empty_parts(): void
    {
        $this->assertSame('dubai-distributor', SlugGenerator::fromParts([null, 'Dubai', '', 'Distributor']));
    }
}
