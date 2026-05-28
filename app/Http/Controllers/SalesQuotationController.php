<?php

namespace App\Http\Controllers;

use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesQuotationController extends Controller
{
    public function index()
    {
        return view('sales-quotations.index');
    }

    public function printQuotation(int $id)
    {
        $quotation = SalesQuotation::with(['branch', 'user', 'approver', 'items.product'])
            ->findOrFail($id);

        $this->authorizeQuotationView(request()->user(), $quotation);

        if ($quotation->status === 'rejected') {
            abort(403, 'Rejected quotations cannot be printed.');
        }

        return view('sales-quotations.quotation-print', ['quotation' => $quotation]);
    }

    public function getBranchQuotations(Request $request, int $branchId)
    {
        $user = $request->user();
        $this->assertBranchAccess($user, $branchId);

        $query = SalesQuotation::with(['user', 'branch'])
            ->where('branch_id', $branchId)
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->get());
    }

    public function show(Request $request, int $id)
    {
        $quotation = SalesQuotation::with(['branch', 'user', 'approver', 'items.product', 'sale'])
            ->findOrFail($id);
        $this->authorizeQuotationView($request->user(), $quotation);

        return response()->json($quotation);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_company' => 'nullable|string|max:255',
            'customer_email' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:64',
            'customer_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->assertBranchAccess($user, (int) $validated['branch_id']);

        if ($user->role === 'manager') {
            $request->merge(['branch_id' => $user->branch_id]);
            $validated['branch_id'] = $user->branch_id;
        }
        if ($user->role === 'staff') {
            $validated['branch_id'] = $user->branch_id;
        }

        return DB::transaction(function () use ($validated, $user) {
            $quotation = SalesQuotation::create([
                'branch_id' => $validated['branch_id'],
                'user_id' => $user->id,
                'customer_name' => $validated['customer_name'],
                'customer_company' => $validated['customer_company'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'status' => 'draft',
                'tax_rate' => (float) ($validated['tax_rate'] ?? 0),
                'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
            ]);

            $this->syncItems($quotation, $validated['items']);
            $this->recalculateTotals($quotation);
            $this->assignQuotationNumber($quotation);
            $quotation->refresh()->load(['items.product', 'branch', 'user']);

            return response()->json($quotation, 201);
        });
    }

    public function update(Request $request, int $id)
    {
        $quotation = SalesQuotation::findOrFail($id);
        $user = $request->user();
        $this->authorizeQuotationView($user, $quotation);

        if (! $quotation->isEditable()) {
            return response()->json(['error' => 'Only draft or rejected quotations can be edited.'], 403);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_company' => 'nullable|string|max:255',
            'customer_email' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:64',
            'customer_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($quotation, $validated) {
            $quotation->update([
                'customer_name' => $validated['customer_name'],
                'customer_company' => $validated['customer_company'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'rejection_reason' => null,
            ]);

            if ($quotation->status === 'rejected') {
                $quotation->update(['status' => 'draft']);
            }

            $quotation->items()->delete();
            $this->syncItems($quotation, $validated['items']);
            $this->recalculateTotals($quotation);
            $this->assignQuotationNumber($quotation);
            $quotation->refresh()->load(['items.product', 'branch', 'user']);

            return response()->json($quotation);
        });
    }

    public function destroy(Request $request, int $id)
    {
        $quotation = SalesQuotation::findOrFail($id);
        $this->authorizeQuotationView($request->user(), $quotation);

        if ($quotation->status !== 'draft') {
            return response()->json(['error' => 'Only draft quotations can be deleted.'], 403);
        }

        $quotation->delete();

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, int $id)
    {
        $quotation = SalesQuotation::with('items')->findOrFail($id);
        $this->authorizeQuotationView($request->user(), $quotation);

        if ($quotation->status !== 'draft') {
            return response()->json(['error' => 'Only draft quotations can be submitted for approval.'], 403);
        }

        if ($quotation->items->isEmpty()) {
            return response()->json(['error' => 'Add at least one line item before submitting.'], 422);
        }

        $this->recalculateTotals($quotation);
        $this->assignQuotationNumber($quotation);
        $quotation->update(['status' => 'pending_approval']);

        return response()->json($quotation->fresh()->load(['items.product', 'branch', 'user']));
    }

    public function approve(Request $request, int $id)
    {
        $user = $request->user();
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            return response()->json(['error' => 'Only managers or admins can approve quotations.'], 403);
        }

        $quotation = SalesQuotation::findOrFail($id);
        $this->authorizeQuotationView($user, $quotation);

        if ($quotation->status !== 'pending_approval') {
            return response()->json(['error' => 'Only pending quotations can be approved.'], 403);
        }

        $this->recalculateTotals($quotation);
        $quotation->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return response()->json($quotation->fresh()->load(['items.product', 'branch', 'user', 'approver']));
    }

    public function reject(Request $request, int $id)
    {
        $user = $request->user();
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            return response()->json(['error' => 'Only managers or admins can reject quotations.'], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $quotation = SalesQuotation::findOrFail($id);
        $this->authorizeQuotationView($user, $quotation);

        if ($quotation->status !== 'pending_approval') {
            return response()->json(['error' => 'Only pending quotations can be rejected.'], 403);
        }

        $quotation->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json($quotation->fresh()->load(['items.product', 'branch', 'user']));
    }

    public function linkSale(Request $request, int $id)
    {
        $user = $request->user();
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            return response()->json(['error' => 'Only managers or admins can link a sale.'], 403);
        }

        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
        ]);

        $quotation = SalesQuotation::findOrFail($id);
        $this->authorizeQuotationView($user, $quotation);

        if (! in_array($quotation->status, ['draft', 'approved'], true)) {
            return response()->json(['error' => 'Only saved (draft) or approved quotations can be linked to a sale.'], 403);
        }

        $sale = \App\Models\Sale::findOrFail((int) $validated['sale_id']);
        if ((int) $sale->branch_id !== (int) $quotation->branch_id) {
            return response()->json(['error' => 'Sale must belong to the same branch as the quotation.'], 422);
        }

        $quotation->update(['sale_id' => $sale->id]);

        return response()->json($quotation->fresh()->load(['sale', 'items.product']));
    }

    protected function assertBranchAccess($user, int $branchId): void
    {
        if ($user->role === 'admin') {
            return;
        }
        if ((int) $user->branch_id !== $branchId) {
            abort(403, 'You do not have access to this branch.');
        }
    }

    protected function authorizeQuotationView($user, SalesQuotation $quotation): void
    {
        $this->assertBranchAccess($user, (int) $quotation->branch_id);
    }

    protected function assignQuotationNumber(SalesQuotation $quotation): void
    {
        if ($quotation->quotation_number) {
            return;
        }
        $quotationNumber = sprintf(
            'SQ-%s-%s-%05d',
            str_pad((string) $quotation->branch_id, 2, '0', STR_PAD_LEFT),
            Carbon::now()->format('Y'),
            $quotation->id
        );
        $quotation->update(['quotation_number' => $quotationNumber]);
    }

    protected function syncItems(SalesQuotation $quotation, array $items): void
    {
        foreach ($items as $index => $row) {
            $qty = (float) $row['quantity'];
            $unit = (float) $row['unit_price'];
            $lineTotal = round($qty * $unit, 2);

            SalesQuotationItem::create([
                'sales_quotation_id' => $quotation->id,
                'product_id' => $row['product_id'] ?? null,
                'description' => $row['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $lineTotal,
                'sort_order' => $index,
            ]);
        }
    }

    protected function recalculateTotals(SalesQuotation $quotation): void
    {
        $quotation->load('items');
        $subtotal = (float) $quotation->items->sum('line_total');
        $discount = min((float) ($quotation->discount_amount ?? 0), $subtotal);
        $afterDiscount = max(0, $subtotal - $discount);
        $taxRate = (float) ($quotation->tax_rate ?? 0);
        $taxAmount = round($afterDiscount * ($taxRate / 100), 2);
        $grand = round($afterDiscount + $taxAmount, 2);

        $quotation->update([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => $taxAmount,
            'grand_total' => $grand,
        ]);
    }
}
