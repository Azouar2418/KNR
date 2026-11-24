<?php

namespace Tests\Unit;

use App\Helpers\ChapterHelper;
use PHPUnit\Framework\TestCase;

class ChapterHelperSecurityTest extends TestCase
{
    public function test_assert_safe_filename_accepts_valid_names(): void
    {
        $this->assertSame('image_01.webp', ChapterHelper::assertSafeFilename('image_01.webp'));
        $this->assertSame('abc-123.PNG', ChapterHelper::assertSafeFilename('abc-123.PNG'));
    }

    public function test_assert_safe_filename_rejects_path_traversal(): void
    {
        $this->expectException(\Exception::class);
        ChapterHelper::assertSafeFilename('../secret.png');
    }

    public function test_assert_safe_filename_rejects_directory_separators(): void
    {
        $this->expectException(\Exception::class);
        ChapterHelper::assertSafeFilename('foo/bar.png');
    }

    public function test_assert_safe_filename_rejects_dot_dot(): void
    {
        $this->expectException(\Exception::class);
        ChapterHelper::assertSafeFilename('..image.png');
    }
}
