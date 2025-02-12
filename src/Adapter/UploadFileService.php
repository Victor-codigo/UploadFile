<?php

declare(strict_types=1);

namespace VictorCodigo\UploadFile\Adapter;

use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\String\Slugger\SluggerInterface;
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
use VictorCodigo\UploadFile\Domain\FileInterface;
use VictorCodigo\UploadFile\Domain\UploadedFileInterface;

class UploadFileService
{
    private string $fileName;

    final public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * @throws LogicException
     */
    public function getFileName(): string
    {
        if (!isset($this->fileName)) {
            throw LogicException::fromMessage('There is no file uploaded. Call method upload first');
        }

        return $this->fileName;
    }

    /**
     * @param string $fileNameToReplace Name of the file to replace. File must be in "$pathToSaveFile" path.
     *
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileUploadReplaceException
     */
    public function __invoke(UploadedFileInterface $file, string $pathToSaveFile, ?string $fileNameToReplace = null): FileInterface
    {
        try {
            $this->fileName = $this->generateFileName($file);

            if (null !== $fileNameToReplace) {
                $this->removeImage($pathToSaveFile, $fileNameToReplace);
            }

            return $file->move($pathToSaveFile, $this->fileName);
        } catch (CannotWriteFileException) {
            throw FileUploadCanNotWriteException::fromMessage('UPLOAD_ERR_CANT_WRITE error occurred');
        } catch (ExtensionFileException) {
            throw FileUploadExtensionFileException::fromMessage('UPLOAD_ERR_EXTENSION error occurred');
        } catch (FormSizeFileException) {
            throw FileUploadSizeException::fromMessage('UPLOAD_ERR_FORM_SIZE error occurred');
        } catch (IniSizeFileException) {
            throw FileUploadIniSizeException::fromMessage('UPLOAD_ERR_INI_SIZE error occurred');
        } catch (NoFileException) {
            throw FileUploadNoFileException::fromMessage('UPLOAD_ERR_NO_FILE error occurred');
        } catch (NoTmpDirFileException) {
            throw FileUploadTmpDirFileException::fromMessage('UPLOAD_ERR_NO_TMP_DIR error occurred');
        } catch (PartialFileException) {
            throw FileUploadPartialFileException::fromMessage('UPLOAD_ERR_PARTIAL error occurred');
        } catch (FileException $e) {
            throw FileUploadException::fromMessage($e->getMessage());
        }
    }

    private function generateFileName(UploadedFileInterface $file): string
    {
        $originalFileName = pathinfo($file->getClientOriginalExtension(), PATHINFO_FILENAME);
        $safeFileName = $this->slug($originalFileName);

        return sprintf('%s-%s.%s', $safeFileName, $this->uniqid(), $file->guessClientExtension());
    }

    protected function uniqid(): string
    {
        return uniqid();
    }

    /**
     * This method exits, just because phpunit error: eval emits an error that shows in console all code of the class.
     */
    protected function slug(string $string, string $separator = '-', ?string $locale = null): string
    {
        return (string) $this->slugger->slug($string, $separator, $locale);
    }

    public function getNewInstance(): static
    {
        return new static($this->slugger);
    }

    /**
     * @throws FileUploadReplaceException
     */
    private function removeImage(string $imagesPath, string $fileName): void
    {
        $file = "{$imagesPath}/{$fileName}";

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw FileUploadReplaceException::fromMessage(sprintf('File [%s] could not be Replaced', $file));
        }
    }
}
