<?php

declare(strict_types=1);

namespace GameStore\Application\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /** @var string[] */
    private array $errors;

    /** @param string[] $errors */
    public function __construct(array $errors, string $message = 'Validation failed.')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /** @return string[] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
