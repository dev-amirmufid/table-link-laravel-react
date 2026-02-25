<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * GET /api/v1/transactions
     */
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
     * GET /api/v1/transactions/{id}
     */
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
