<?php

namespace App\Annotations;

use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Server;
use OpenApi\Annotations\SecurityScheme;
use OpenApi\Annotations\Tag;

/**
 * @OA\OpenAPI(
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
 *         description="JWT Authorization header using the Bearer scheme."
 *     ),
 *     @OA\Tag(
 *         name="Authentication",
 *         description="Authentication Endpoints"
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
 */
class Swagger
{
}
