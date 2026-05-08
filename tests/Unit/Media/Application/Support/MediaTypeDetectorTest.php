<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Application\Support;

use App\Media\Application\Support\MediaTypeDetector;
use App\Media\Domain\Enums\MediaType;
use PHPUnit\Framework\TestCase;

class MediaTypeDetectorTest extends TestCase
{
    public function test_it_detects_media_type_by_mime_type_and_extension(): void
    {
        $detector = new MediaTypeDetector();

        $this->assertSame(MediaType::IMAGE, $detector->detect('image/jpeg', 'jpg'));
        $this->assertSame(MediaType::VIDEO, $detector->detect('video/mp4', 'mp4'));
        $this->assertSame(MediaType::DOCUMENT, $detector->detect('application/octet-stream', 'pdf'));
        $this->assertSame(MediaType::FILE, $detector->detect('application/octet-stream', 'bin'));
    }
}
