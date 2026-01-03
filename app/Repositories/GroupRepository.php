<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of the GroupRepositoryInterface.
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * Get all groups.
     *
     * @return Collection<int, Group>
     */
    public function all(): Collection
    {
        return Group::with(['creator', 'members'])->get();
    }

    /**
     * Create a new group.
     *
     * @param array<string, mixed> $data
     * @return Group
     */
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    /**
     * Find a group by ID.
     *
     * @param int $id
     * @return Group|null
     */
    public function find(int $id): ?Group
    {
        return Group::with(['creator', 'members'])->find($id);
    }

    /**
     * Find a group by ID or throw exception.
     *
     * @param int $id
     * @return Group
     */
    public function findOrFail(int $id): Group
    {
        return Group::with(['creator', 'members'])->findOrFail($id);
    }

    /**
     * Update a group.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Group
     */
    public function update(int $id, array $data): Group
    {
        $group = Group::findOrFail($id);
        $group->update($data);
        return $group->fresh(['creator', 'members']);
    }

    /**
     * Delete a group.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Group::destroy($id) > 0;
    }

    /**
     * Add members to a group.
     *
     * @param int $groupId
     * @param array<int> $userIds
     * @return void
     */
    public function addMembers(int $groupId, array $userIds): void
    {
        $group = Group::findOrFail($groupId);
        $group->members()->syncWithoutDetaching($userIds);
    }

    /**
     * Remove a member from a group.
     *
     * @param int $groupId
     * @param int $userId
     * @return void
     */
    public function removeMember(int $groupId, int $userId): void
    {
        $group = Group::findOrFail($groupId);
        $group->members()->detach($userId);
    }

    /**
     * Get groups for a specific user.
     *
     * @param int $userId
     * @return Collection<int, Group>
     */
    public function getByUser(int $userId): Collection
    {
        return Group::with(['creator', 'members'])
            ->whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->get();
    }
}
