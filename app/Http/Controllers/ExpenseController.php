<?php

namespace App\Http\Controllers;

use App\DTOs\ExpenseDTO;
use App\Exceptions\InvalidSplitException;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * ExpenseController - HTTP Layer
 * 
 * This controller handles HTTP requests and responses only.
 * All business logic is delegated to ExpenseService.
 */
class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {}

    /**
     * Store a newly created expense.
     * 
     * @OA\Post(
     *     path="/expenses",
     *     operationId="createExpense",
     *     tags={"Expenses"},
     *     summary="Create a new expense",
     *     description="Creates a new expense with splits. The sum of all owed_share values must equal the total expense amount.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"description", "amount", "group_id", "paid_by", "splits"},
     *             @OA\Property(property="description", type="string", example="Dinner at restaurant", maxLength=255),
     *             @OA\Property(property="amount", type="number", format="float", example=300.00, minimum=0.01),
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="paid_by", type="integer", example=1, description="User ID who paid"),
     *             @OA\Property(property="currency_code", type="string", example="USD", maxLength=3),
     *             @OA\Property(property="expense_date", type="string", format="date", example="2026-01-03"),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(
     *                 property="splits",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     required={"user_id", "paid_share", "owed_share"},
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="paid_share", type="number", format="float", example=300.00, minimum=0),
     *                     @OA\Property(property="owed_share", type="number", format="float", example=100.00, minimum=0)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Expense created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Expense")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or split sum mismatch",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'group_id' => 'required|integer|exists:groups,id',
            'paid_by' => 'required|integer|exists:users,id',
            'currency_code' => 'nullable|string|size:3',
            'expense_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'splits' => 'required|array|min:1',
            'splits.*.user_id' => 'required|integer|exists:users,id',
            'splits.*.paid_share' => 'required|numeric|min:0',
            'splits.*.owed_share' => 'required|numeric|min:0',
        ]);

        try {
            $dto = ExpenseDTO::fromArray($validated);
            $expense = $this->expenseService->addExpense($dto);

            return response()->json([
                'message' => 'Expense created successfully',
                'data' => $expense,
            ], 201);
        } catch (InvalidSplitException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'splits' => [$e->getMessage()],
                ],
            ], 422);
        }
    }

    /**
     * Display the specified expense.
     *
     * @OA\Get(
     *     path="/expenses/{id}",
     *     operationId="getExpense",
     *     tags={"Expenses"},
     *     summary="Get expense by ID",
     *     description="Returns a single expense with its splits",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Expense")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $expense = $this->expenseService->getExpense($id);

        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found',
            ], 404);
        }

        return response()->json([
            'data' => $expense,
        ]);
    }

    /**
     * Get all expenses for a group.
     *
     * @OA\Get(
     *     path="/groups/{groupId}/expenses",
     *     operationId="getGroupExpenses",
     *     tags={"Expenses"},
     *     summary="Get all expenses for a group",
     *     description="Returns all expenses belonging to a specific group",
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         required=true,
     *         description="Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Expense")
     *             )
     *         )
     *     )
     * )
     */
    public function byGroup(int $groupId): JsonResponse
    {
        $expenses = $this->expenseService->getExpensesByGroup($groupId);

        return response()->json([
            'data' => $expenses,
        ]);
    }

    /**
     * Remove the specified expense.
     *
     * @OA\Delete(
     *     path="/expenses/{id}",
     *     operationId="deleteExpense",
     *     tags={"Expenses"},
     *     summary="Delete an expense",
     *     description="Deletes an expense and all its splits (cascade)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense not found")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->expenseService->deleteExpense($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Expense not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Expense deleted successfully',
        ]);
    }
}
