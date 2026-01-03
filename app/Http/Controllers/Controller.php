<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="OpenSplit API",
 *     description="Enterprise backend API for OpenSplit - an open-source Splitwise alternative",
 *     @OA\Contact(
 *         email="support@opensplit.io",
 *         name="OpenSplit Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * 
 * @OA\Tag(
 *     name="Expenses",
 *     description="Expense management endpoints"
 * )
 * 
 * @OA\Schema(
 *     schema="ExpenseSplit",
 *     type="object",
 *     required={"user_id", "paid_share", "owed_share"},
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="paid_share", type="string", example="100.00"),
 *     @OA\Property(property="owed_share", type="string", example="33.33")
 * )
 * 
 * @OA\Schema(
 *     schema="Expense",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="description", type="string", example="Dinner at restaurant"),
 *     @OA\Property(property="amount", type="string", example="300.00"),
 *     @OA\Property(property="currency_code", type="string", example="USD"),
 *     @OA\Property(property="paid_by", type="integer", example=1),
 *     @OA\Property(property="group_id", type="integer", example=1),
 *     @OA\Property(property="expense_date", type="string", format="date", example="2026-01-03"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="splits",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ExpenseSplit")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Bearer token from login/register response"
 * )
 */
abstract class Controller
{
    //
}
