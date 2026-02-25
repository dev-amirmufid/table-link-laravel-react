<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Transaction Dashboard API",
 *         description="API Documentation for Transaction Dashboard Application\n\n## Authentication\n\nThis API uses JWT (JSON Web Token) for authentication.\n\nTo authenticate, include the JWT token in the Authorization header:\n```\nAuthorization: Bearer <your_token>\n```\n\n### Login Flow\n1. POST /auth/login with email and password\n2. Receive access_token in response\n3. Use access_token for all protected endpoints",
 *         @OA\Contact(
 *             email="support@tablelink.com"
 *         ),
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8000/api",
 *         description="Local API Server"
 *     ),
 *     @OA\Server(
 *         url="https://api.tablelink.com",
 *         description="Production API Server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="JWT Authorization header using the Bearer scheme. Enter 'Bearer' followed by your JWT token."
 *     ),
 *     @OA\Tag(
 *         name="Authentication",
 *         description="Authentication Endpoints (Login, Register, Logout)"
 *     ),
 *     @OA\Tag(
 *         name="Dashboard",
 *         description="Dashboard Analytics Endpoints"
 *     ),
 *     @OA\Tag(
 *         name="Transactions",
 *         description="Transaction Endpoints"
 *     )
 * )
 *
 * @OA\Components(
 *     @OA\Schema(
 *         schema="User",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="type", type="string", enum={"domestic", "foreign"}),
 *         @OA\Property(property="created_at", type="string", format="datetime"),
 *         @OA\Property(property="updated_at", type="string", format="datetime")
 *     ),
 *     @OA\Schema(
 *         schema="Transaction",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="buyer_id", type="string", format="uuid"),
 *         @OA\Property(property="seller_id", type="string", format="uuid"),
 *         @OA\Property(property="item_id", type="string", format="uuid"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="price", type="number"),
 *         @OA\Property(property="created_at", type="string", format="datetime"),
 *         @OA\Property(property="updated_at", type="string", format="datetime")
 *     ),
 *     @OA\Schema(
 *         schema="Error",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string"),
 *         @OA\Property(property="errors", type="object")
 *     ),
 *     @OA\Schema(
 *         schema="AuthResponse",
 *         type="object",
 *         @OA\Property(property="success", type="boolean"),
 *         @OA\Property(property="message", type="string"),
 *         @OA\Property(
 *             property="data",
 *             type="object",
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string"),
 *             @OA\Property(property="expires_in", type="integer")
 *         )
 *     )
 * )
 */
class OpenApiAnnotations
{
}

/**
 * @OA\PathItem(
 *     path="/"
 * )
 */
class PathItem
{
}
