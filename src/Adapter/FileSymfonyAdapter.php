<?php

declare(strict_types=1);

namespace VictorCodigo\UploadFile\Adapter;

use App\Service\UploadFile\Domain\Exception\FileException;
use App\Service\UploadFile\Domain\FileInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\File;

class FileSymfonyAdapter implements FileInterface
{
    protected File $file;

    #[\Override]
    public function getFile(): File
    {
        return $this->file;
    }

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getMimeType()
     * to guess the file extension.
     *
     * @see MimeTypes
     * @see getMimeType()
     */
    #[\Override]
    public function guessExtension(): ?string
    {
        return $this->file->guessExtension();
    }

    /**
     * Returns the mime type of the file.
     *
     * The mime type is guessed using a MimeTypeGuesserInterface instance,
     * which uses finfo_file() then the "file" system binary,
     * depending on which of those are available.
     *
     * @see MimeTypes
     */
    #[\Override]
    public function getMimeType(): ?string
    {
        return $this->file->getMimeType();
    }

    /**
     * Moves the file to a new location.
     *
     * @throws FileException if the target file could not be created
     */
    #[\Override]
    public function move(string $directory, ?string $name = null): FileInterface
    {
        try {
            $fileMoved = $this->file->move($directory, $name);

            return new FileSymfonyAdapter($fileMoved);
        } catch (SymfonyFileException $e) {
            throw FileException::fromMessage($e->getMessage());
        }
    }

    #[\Override]
    public function getContent(): string
    {
        return $this->file->getContent();
    }
}
