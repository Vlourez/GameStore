<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\RecordPlaySessionRequest;
use GameStore\Application\Interfaces\LibraryServiceInterface;
use GameStore\Application\Validators\RecordPlaySessionRequestValidator;

final class LibraryController
{
    public function __construct(private readonly LibraryServiceInterface $libraryService)
    {
    }

    public function index(Request $request): void
    {
        Response::json($this->libraryService->getByCustomer($request->authenticatedCustomerId));
    }

    public function recordPlaySession(Request $request): void
    {
        $dto = RecordPlaySessionRequest::fromArray($request->json());
        (new RecordPlaySessionRequestValidator())->assert($dto);

        $this->libraryService->recordPlaySession($request->authenticatedCustomerId, $dto);
        Response::noContent();
    }
}
