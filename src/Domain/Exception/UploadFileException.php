<?php

declare(strict_types=1);

namespace VictorCodigo\UploadFile\Domain\Exception;

class UploadFileException extends \Exception
{
    final protected function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromMessage(string $message): static
    {
        return new static($message, 0, null);
    }
}
