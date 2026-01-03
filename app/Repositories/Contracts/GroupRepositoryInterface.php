<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use Illuminate\Support\Collection;

/**
 * Interface for Group data access operations.
 */
interface GroupRepositoryInterface
{
    /**
     * Get all groups.
     *
     * @return Collection<int, Group>
     */
    public function all(): Collection;

    /**
     * Create a new group.
     *
     * @param array<string, mixed> $data
     * @return Group
     */
    public function create(array $data): Group;

    /**
     * Find a group by ID.
     *
     * @param int $id
     * @return Group|null
     */
    public function find(int $id): ?Group;

    /**
     * Find a group by ID or throw exception.
     *
     * @param int $id
     * @return Group
     */
    public function findOrFail(int $id): Group;

    /**
     * Update a group.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Group
     */
    public function update(int $id, array $data): Group;

    /**
     * Delete a group.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Add members to a group.
     *
     * @param int $groupId
     * @param array<int> $userIds
     * @return void
     */
    public function addMembers(int $groupId, array $userIds): void;

    /**
     * Remove a member from a group.
     *
     * @param int $groupId
     * @param int $userId
     * @return void
     */
    public function removeMember(int $groupId, int $userId): void;

    /**
     * Get groups for a specific user.
     *
     * @param int $userId
     * @return Collection<int, Group>
     */
    public function getByUser(int $userId): Collection;
}
