<?php

namespace App\Http\Controllers;

use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * GroupController - HTTP Layer for Group Management
 * 
 * @OA\Tag(
 *     name="Groups",
 *     description="Group management endpoints"
 * )
 * 
 * @OA\Schema(
 *     schema="Group",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Roommates"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Shared apartment expenses"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="members",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="email", type="string")
 *         )
 *     )
 * )
 */
class GroupController extends Controller
{
    public function __construct(
        private readonly GroupService $groupService
    ) {}

    /**
     * List all groups.
     *
     * @OA\Get(
     *     path="/groups",
     *     operationId="listGroups",
     *     tags={"Groups"},
     *     summary="Get all groups",
     *     description="Returns a list of all groups",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Group")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $groups = $this->groupService->getAllGroups();

        return response()->json([
            'data' => $groups,
        ]);
    }

    /**
     * Store a newly created group.
     *
     * @OA\Post(
     *     path="/groups",
     *     operationId="createGroup",
     *     tags={"Groups"},
     *     summary="Create a new group",
     *     description="Creates a new group and adds the creator as the first member",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "created_by"},
     *             @OA\Property(property="name", type="string", example="Trip to Paris", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true, example="Expenses for our Paris vacation"),
     *             @OA\Property(property="created_by", type="integer", example=1, description="User ID of the creator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Group created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Group created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'required|integer|exists:users,id',
        ]);

        $group = $this->groupService->createGroup($validated);

        return response()->json([
            'message' => 'Group created successfully',
            'data' => $group,
        ], 201);
    }

    /**
     * Display the specified group.
     *
     * @OA\Get(
     *     path="/groups/{id}",
     *     operationId="getGroup",
     *     tags={"Groups"},
     *     summary="Get group by ID",
     *     description="Returns a single group with its members",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Group not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $group = $this->groupService->getGroup($id);

        if (!$group) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }

        return response()->json([
            'data' => $group,
        ]);
    }

    /**
     * Update the specified group.
     *
     * @OA\Put(
     *     path="/groups/{id}",
     *     operationId="updateGroup",
     *     tags={"Groups"},
     *     summary="Update a group",
     *     description="Updates group name and/or description",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Group Name"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Group updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Group updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $group = $this->groupService->updateGroup($id, $validated);

            return response()->json([
                'message' => 'Group updated successfully',
                'data' => $group,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }
    }

    /**
     * Remove the specified group.
     *
     * @OA\Delete(
     *     path="/groups/{id}",
     *     operationId="deleteGroup",
     *     tags={"Groups"},
     *     summary="Delete a group",
     *     description="Deletes a group and all its expenses (cascade)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Group deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Group deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->groupService->deleteGroup($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Group deleted successfully',
        ]);
    }

    /**
     * Add members to a group.
     *
     * @OA\Post(
     *     path="/groups/{id}/members",
     *     operationId="addGroupMembers",
     *     tags={"Groups"},
     *     summary="Add members to a group",
     *     description="Adds one or more users as members of the group",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_ids"},
     *             @OA\Property(
     *                 property="user_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={2, 3, 4}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Members added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Members added successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     )
     * )
     */
    public function addMembers(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $group = $this->groupService->addMembers($id, $validated['user_ids']);

            return response()->json([
                'message' => 'Members added successfully',
                'data' => $group,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }
    }

    /**
     * Remove a member from a group.
     *
     * @OA\Delete(
     *     path="/groups/{id}/members/{userId}",
     *     operationId="removeGroupMember",
     *     tags={"Groups"},
     *     summary="Remove a member from a group",
     *     description="Removes a user from the group membership",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID to remove",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Member removed successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     )
     * )
     */
    public function removeMember(int $id, int $userId): JsonResponse
    {
        try {
            $group = $this->groupService->removeMember($id, $userId);

            return response()->json([
                'message' => 'Member removed successfully',
                'data' => $group,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }
    }
}
