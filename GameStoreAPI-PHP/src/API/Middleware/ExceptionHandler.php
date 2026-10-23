<?php

declare(strict_types=1);

namespace GameStore\API\Middleware;

use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Response\ErrorResponse;
use GameStore\Application\Exceptions\BusinessRuleException;
use GameStore\Application\Exceptions\ConflictException;
use GameStore\Application\Exceptions\ForbiddenException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Exceptions\UnauthorizedException;
use GameStore\Application\Exceptions\ValidationException;
use GameStore\Domain\Common\DomainException;
use GameStore\Support\Env;
use Throwable;

/**
 * Mirrors the original ASP.NET Core ExceptionHandlingMiddleware: maps
 * exception types to HTTP status codes and a consistent JSON error body.
 */
final class ExceptionHandler
{
    public static function handle(Throwable $e): void
    {
        [$status, $message, $errors] = match (true) {
            $e instanceof ValidationException => [422, $e->getMessage(), $e->getErrors()],
            $e instanceof NotFoundException => [404, $e->getMessage(), null],
            $e instanceof ConflictException => [409, $e->getMessage(), null],
            $e instanceof UnauthorizedException => [401, $e->getMessage(), null],
            $e instanceof ForbiddenException => [403, $e->getMessage(), null],
            $e instanceof BusinessRuleException => [400, $e->getMessage(), null],
            $e instanceof DomainException => [400, $e->getMessage(), null],
            default => [
                500,
                Env::get('APP_DEBUG', false)
                    ? 'Unexpected error: ' . $e->getMessage()
                    : 'An unexpected error occurred.',
                null,
            ],
        };

        if ($status === 500) {
            error_log((string) $e);
        }

        Response::json(new ErrorResponse($message, $errors), $status);
    }
}
