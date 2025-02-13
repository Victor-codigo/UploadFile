<?php

declare(strict_types=1);

namespace VictorCodigo\UploadFile\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use VictorCodigo\UploadFile\Adapter\FileSymfonyAdapter;
use VictorCodigo\UploadFile\Adapter\UploadedFileSymfonyAdapter;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadCanNotWriteException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadExtensionFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadIniSizeException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadNoFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadPartialFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadSizeException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadTmpDirFileException;

class UploadedFileSymfonyAdapterTest extends TestCase
{
    private UploadedFileSymfonyAdapter $object;
    private UploadedFile&MockObject $uploadedFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploadedFile = $this->createMock(UploadedFile::class);
        $this->object = new UploadedFileSymfonyAdapter($this->uploadedFile);
    }

    #[Test]
    public function itShouldMoveUploadedFileAndReturnNewOne(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';
        $uploadedFileNew = $this->createMock(UploadedFile::class);
        $uploadedFileSymfonyAdapterNew = new FileSymfonyAdapter($uploadedFileNew);

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willReturn($uploadedFileNew);

        $return = $this->object->move($directory, $name);

        self::assertEquals($uploadedFileSymfonyAdapterNew, $return);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorCannotWriteFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new CannotWriteFileException());

        $this->expectException(FileUploadCanNotWriteException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorExtensionFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new ExtensionFileException());

        $this->expectException(FileUploadExtensionFileException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorFormSizeFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new FormSizeFileException());

        $this->expectException(FileUploadSizeException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorIniSizeFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new IniSizeFileException());

        $this->expectException(FileUploadIniSizeException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorNoFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new NoFileException());

        $this->expectException(FileUploadNoFileException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorNoTmpDirFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new NoTmpDirFileException());

        $this->expectException(FileUploadTmpDirFileException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorPartialFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new PartialFileException());

        $this->expectException(FileUploadPartialFileException::class);
        $this->object->move($directory, $name);
    }

    #[Test]
    public function itShouldFailMovingUploadedFileErrorFileException(): void
    {
        $directory = 'path/to/directory';
        $name = 'new_file_name.txt';

        $this->uploadedFile
            ->expects($this->once())
            ->method('move')
            ->with($directory, $name)
            ->willThrowException(new FileException());

        $this->expectException(FileUploadException::class);
        $this->object->move($directory, $name);
    }
}
