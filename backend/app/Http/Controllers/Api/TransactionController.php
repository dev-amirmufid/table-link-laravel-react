<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    protected TransactionRepository $transactionRepository;
    protected FilterService $filterService;

    public function __construct(
        TransactionRepository $transactionRepository,
        FilterService $filterService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->filterService = $filterService;
    }

    /**
     * Get all transactions with pagination and search
     */
    #[OA\Get(
        path: "/transactions",
        tags: ["Transactions"],
        summary: "Get all transactions",
        description: "Returns paginated transactions",
        parameters: [
            new OA\Parameter(name: "page", in: "query", description: "Page number", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page (max 100)", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Search by buyer name, seller name, or item name", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d H:i:s)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d H:i:s)", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $perPage = $request->input('per_page', 15);
        
        // Ensure perPage is reasonable for performance
        $perPage = min($perPage, 100);

        $data = $this->transactionRepository->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get single transaction
     */
    #[OA\Get(
        path: "/transactions/{id}",
        tags: ["Transactions"],
        summary: "Get single transaction",
        description: "Returns a single transaction by ID",
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "Transaction UUID", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Transaction not found")
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $transaction = $this->transactionRepository->getQuery()->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }
}
