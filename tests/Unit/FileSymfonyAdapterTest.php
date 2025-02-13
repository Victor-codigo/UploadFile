<?php

declare(strict_types=1);

namespace VictorCodigo\UploadFile\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\File;
use VictorCodigo\UploadFile\Adapter\FileSymfonyAdapter;
use VictorCodigo\UploadFile\Domain\Exception\FileException;

class FileSymfonyAdapterTest extends TestCase
{
    private FileSymfonyAdapter $object;
    private File&MockObject $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = $this->createMock(File::class);
        $this->object = new FileSymfonyAdapter($this->file);
    }

    #[Test]
    public function itShouldMoveTheFileAndReturnNewOne(): void
    {
        $directory = 'file/path';
        $name = 'new_file_name.txt';
        $fileNew = $this->createMock(File::class);
        $fileSymfonyAdapterNew = new FileSymfonyAdapter($fileNew);

        $this->file
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willReturn($fileNew);

        $return = $this->object->move($directory, $name);

        self::assertEquals($fileSymfonyAdapterNew, $return);
    }

    #[Test]
    public function itShouldFailMovingTheFile(): void
    {
        $directory = 'file/path';
        $name = 'new_file_name.txt';

        $this->file
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new SymfonyFileException());

        $this->expectException(FileException::class);
        $this->object->move($directory, $name);
    }
}
