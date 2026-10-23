<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\Exceptions\ValidationException;

/**
 * Minimal stand-in for FluentValidation's AbstractValidator<T>.
 * Concrete validators implement rules() and return an array of
 * human-readable error messages (empty array = valid).
 */
abstract class AbstractValidator
{
    /** @return string[] */
    abstract public function rules(object $request): array;

    public function assert(object $request): void
    {
        $errors = $this->rules($request);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    protected function notEmpty(string $value, string $field, array &$errors, int $maxLength = 0): void
    {
        if (trim($value) === '') {
            $errors[] = "{$field} must not be empty.";
            return;
        }

        if ($maxLength > 0 && mb_strlen($value) > $maxLength) {
            $errors[] = "{$field} must be {$maxLength} characters or fewer.";
        }
    }

    protected function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Aceita somente datas reais no formato YYYY-MM-DD (rejeita, por exemplo, 2024-02-31). */
    protected function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    /** Regras de senha compartilhadas entre cadastro e troca de senha. */
    protected function checkPassword(string $password, string $field, array &$errors): void
    {
        if (mb_strlen($password) < 8) {
            $errors[] = "{$field} must be at least 8 characters long.";
        }

        // bcrypt ignora silenciosamente tudo que passa de 72 bytes.
        if (strlen($password) > 72) {
            $errors[] = "{$field} must be 72 bytes or fewer.";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "{$field} must have at least one uppercase letter.";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "{$field} must have at least one digit.";
        }
    }

    /** Data de nascimento opcional: se vier preenchida, precisa ser uma data real e não pode ser futura. */
    protected function checkBirthDate(?string $value, array &$errors): void
    {
        if ($value === null) {
            return;
        }

        if (!$this->isDate($value)) {
            $errors[] = 'birthDate must be a valid date in the format YYYY-MM-DD.';
        } elseif ($value > gmdate('Y-m-d')) {
            $errors[] = 'birthDate must not be in the future.';
        }
    }
}
