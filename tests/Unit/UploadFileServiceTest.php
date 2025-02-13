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
use VictorCodigo\UploadFile\Adapter\BuiltInFunctionsReturn;
use VictorCodigo\UploadFile\Adapter\UploadFileService;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadCanNotWriteException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadExtensionFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadIniSizeException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadNoFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadPartialFileException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadReplaceException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadSizeException;
use VictorCodigo\UploadFile\Domain\Exception\FileUploadTmpDirFileException;
use VictorCodigo\UploadFile\Domain\Exception\LogicException;
use VictorCodigo\UploadFile\Domain\UploadedFileInterface;

class UploadFileServiceTest extends TestCase
{
    private UploadFileService&MockObject $object;
    private UploadedFileInterface&MockObject $file;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->file = $this->createMock(UploadedFileInterface::class);
        $this->object = $this->createPartialMock(UploadFileService::class, ['uniqid', 'slug']);
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    #[Test]
    public function istShouldThrowLogicExceptionGettingTheFileName(): void
    {
        $this->expectException(LogicException::class);

        $this->object->getFileName();
    }

    #[Test]
    public function istShouldGettingTheFileName(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        $this->object->__invoke($this->file, $pathToSaveFile);
        $return = $this->object->getFileName();

        $this->assertSame($slugFileName, $return);
    }

    private function mock__invokeStubs(string $originalFileName, string $safeFileName, string $slugFileName, string $pathToSaveFile, string $uniqid, \Exception|UploadedFileInterface $moveReturn): void
    {
        $this->file
            ->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn($originalFileName);

        $this->file
            ->expects($this->once())
            ->method('move')
            ->with($pathToSaveFile, $slugFileName)
            ->willReturnCallback(fn (): UploadedFileInterface => $moveReturn instanceof \Throwable ? throw $moveReturn : $moveReturn);

        $this->file
            ->expects($this->once())
            ->method('guessExtension')
            ->willReturn(null);

        $this->object
            ->expects($this->once())
            ->method('uniqid')
            ->willReturn($uniqid);

        $this->object
            ->expects($this->once())
            ->method('slug')
            ->willReturn($safeFileName);
    }

    #[Test]
    public function itShouldUploadTheFile(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldUploadTheFileAndRemoveFileToReplace(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $pathToReplaceFile = 'path/to/replace/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $this->object->__invoke($this->file, $pathToSaveFile, $pathToReplaceFile);
    }

    #[Test]
    public function itShouldUploadTheFileAndRemoveFileToReplaceFileToReplaceNotExists(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $pathToReplaceFile = 'path/to/replace/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        BuiltInFunctionsReturn::$file_exists = false;
        BuiltInFunctionsReturn::$unlink = true;
        $this->object->__invoke($this->file, $pathToSaveFile, $pathToReplaceFile);
    }

    #[Test]
    public function itShouldThrowFileUploadReplaceException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $pathToReplaceFile = 'path/to/replace/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';

        $this->file
            ->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn($originalFileName);

        $this->file
            ->expects($this->never())
            ->method('move');

        $this->file
            ->expects($this->once())
            ->method('guessExtension')
            ->willReturn(null);

        $this->object
            ->expects($this->once())
            ->method('uniqid')
            ->willReturn($uniqid);

        $this->object
            ->expects($this->once())
            ->method('slug')
            ->willReturn($safeFileName);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;

        $this->expectException(FileUploadReplaceException::class);
        $this->object->__invoke($this->file, $pathToSaveFile, $pathToReplaceFile);
    }

    #[Test]
    public function itShouldThrowFileUploadCanNotWriteException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new CannotWriteFileException()
        );

        $this->expectException(FileUploadCanNotWriteException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadExtensionFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new ExtensionFileException()
        );

        $this->expectException(FileUploadExtensionFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadSizeException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new FormSizeFileException()
        );

        $this->expectException(FileUploadSizeException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadIniSizeException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new IniSizeFileException()
        );

        $this->expectException(FileUploadIniSizeException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadNoFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new NoFileException()
        );

        $this->expectException(FileUploadNoFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadTmpDirFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new NoTmpDirFileException()
        );

        $this->expectException(FileUploadTmpDirFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadPartialFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new PartialFileException()
        );

        $this->expectException(FileUploadPartialFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    #[Test]
    public function itShouldThrowFileUploadException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new FileException()
        );

        $this->expectException(FileUploadException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }
}
