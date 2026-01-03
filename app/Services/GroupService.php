<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * GroupService - Business Logic Layer for Groups
 */
class GroupService
{
    public function __construct(
        private readonly GroupRepositoryInterface $repository
    ) {}

    /**
     * Get all groups.
     *
     * @return Collection<int, Group>
     */
    public function getAllGroups(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Create a new group and add the creator as a member.
     *
     * @param array<string, mixed> $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        return DB::transaction(function () use ($data) {
            $group = $this->repository->create($data);
            
            // Auto-add creator as a member
            $this->repository->addMembers($group->id, [$data['created_by']]);
            
            return $group->load(['creator', 'members']);
        });
    }

    /**
     * Get a group by ID.
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroup(int $id): ?Group
    {
        return $this->repository->find($id);
    }

    /**
     * Update a group.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Group
     */
    public function updateGroup(int $id, array $data): Group
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a group.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroup(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Add members to a group.
     *
     * @param int $groupId
     * @param array<int> $userIds
     * @return Group
     */
    public function addMembers(int $groupId, array $userIds): Group
    {
        $this->repository->addMembers($groupId, $userIds);
        return $this->repository->findOrFail($groupId);
    }

    /**
     * Remove a member from a group.
     *
     * @param int $groupId
     * @param int $userId
     * @return Group
     */
    public function removeMember(int $groupId, int $userId): Group
    {
        $this->repository->removeMember($groupId, $userId);
        return $this->repository->findOrFail($groupId);
    }

    /**
     * Get groups for a specific user.
     *
     * @param int $userId
     * @return Collection<int, Group>
     */
    public function getGroupsByUser(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }
}
