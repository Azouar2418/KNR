<?php

namespace Tests\Unit;

use App\Helpers\ChapterHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class ChapterHelperTest extends TestCase
{
    #[DataProvider('imageNames')]
    public function test_is_image_file(string $filename, bool $expected): void
    {
        $this->assertSame($expected, ChapterHelper::isImageFile($filename));
    }

    public static function imageNames(): array
    {
        return [
            'jpg' => ['page-01.jpg', true],
            'jpeg' => ['page-01.jpeg', true],
            'png' => ['page-01.png', true],
            'gif' => ['page-01.gif', true],
            'webp' => ['page-01.webp', true],
            'uppercase extension' => ['PAGE-01.JPG', true],
            'nested path' => ['chapter-1/page-01.png', true],
            'text file' => ['notes.txt', false],
            'php file' => ['shell.php', false],
            'no extension' => ['page-01', false],
        ];
    }

    public function test_get_zip_file_list_returns_sorted_names(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'knr_zip_');
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::OVERWRITE | ZipArchive::CREATE));
        $zip->addFromString('3.jpg', 'c');
        $zip->addFromString('1.jpg', 'a');
        $zip->addFromString('2.jpg', 'b');

        try {
            $this->assertSame(['1.jpg', '2.jpg', '3.jpg'], ChapterHelper::getZipFileList($zip));
        } finally {
            $zip->close();
            @unlink($path);
        }
    }
}
