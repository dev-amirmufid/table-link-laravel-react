<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    #[OA\Post(
        path: "/auth/register",
        tags: ["Authentication"],
        summary: "Register a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "type", type: "string", enum: ["domestic", "foreign"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User registered successfully"),
            new OA\Response(response: 400, description: "Validation error")
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'type' => 'sometimes|in:domestic,foreign',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'domestic',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ], 201);
    }

    /**
     * Login user
     */
    #[OA\Post(
        path: "/auth/login",
        tags: ["Authentication"],
        summary: "Login user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login successful"),
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => auth()->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }

    /**
     * Logout user
     */
    #[OA\Post(
        path: "/auth/logout",
        tags: ["Authentication"],
        summary: "Logout user",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout successful"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    #[OA\Get(
        path: "/auth/me",
        tags: ["Authentication"],
        summary: "Get authenticated user",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "User data"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
        ]);
    }

    /**
     * Refresh token
     */
    #[OA\Post(
        path: "/auth/refresh",
        tags: ["Authentication"],
        summary: "Refresh JWT token",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Token refreshed"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
            ], 401);
        }
    }
}
