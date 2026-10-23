<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\UpdateProfileRequest;
use GameStore\Application\DTOs\Response\CustomerDetailResponse;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\CustomerServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class CustomerService implements CustomerServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function getById(string $id): CustomerDetailResponse
    {
        $customer = $this->uow->customers()->getWithLibrary($id)
            ?? throw new NotFoundException("Customer {$id} not found.");

        return GameStoreMapper::customerToDetail($customer);
    }

    public function updateProfile(string $id, UpdateProfileRequest $req): CustomerDetailResponse
    {
        $customer = $this->uow->customers()->getById($id)
            ?? throw new NotFoundException("Customer {$id} not found.");

        $customer->updateProfile($req->fullName, $req->avatarUrl, $req->birthDate);
        $this->uow->customers()->update($customer);

        $refreshed = $this->uow->customers()->getWithLibrary($id);

        return GameStoreMapper::customerToDetail($refreshed);
    }

    public function getAchievements(string $customerId): array
    {
        $achievements = $this->uow->achievements()->getByCustomer($customerId);

        return array_map(GameStoreMapper::achievementToResponse(...), $achievements);
    }
}
