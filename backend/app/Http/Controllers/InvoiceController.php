<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * GET /api/invoices
     * Підтримує query-параметри:
     * - search: пошук за number або supplier_name
     * - sort_by: поле сортування (created_at, due_date, gross_amount тощо). За замовчуванням: created_at
     * - sort_order: asc або desc. За замовчуванням: desc
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query();

        // Пошук
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        // Сортування
        $allowedSorts = ['created_at', 'issue_date', 'due_date', 'gross_amount', 'number', 'status'];
        $sortBy = in_array($request->query('sort_by'), $allowedSorts, true)
            ? $request->query('sort_by')
            : 'created_at';

        $sortOrder = strtolower($request->query('sort_order')) === 'asc' ? 'asc' : 'desc';

        $invoices = $query->orderBy($sortBy, $sortOrder)->get();

        return response()->json($invoices);
    }

    /**
     * POST /api/invoices
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['currency'])) {
            $data['currency'] = 'UAH';
        }

        $invoice = Invoice::create($data);

        return response()->json($invoice, 201);
    }

    /**
     * GET /api/invoices/{invoice}
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice);
    }

    /**
     * PUT /api/invoices/{invoice}
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validated();

        // Автоматичний перерахунок gross_amount
        $validated['gross_amount'] = (float)$validated['net_amount'] + (float)$validated['vat_amount'];

        $invoice->update($validated);

        return response()->json($invoice);
    }
}
