<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesQuotationController extends Controller
{
    public function index()
    {
        return view('sales-quotations.index');
    }

    public function printQuotation(int $id)
    {
        $quotation = SalesQuotation::with(['branch', 'user', 'approver', 'items.product.category'])
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

        $dateFrom = null;
        $dateTo = null;
        if ($request->filled('date_from')) {
            $f = $request->string('date_from')->trim();
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
                $dateFrom = $f;
            }
        }
        if ($request->filled('date_to')) {
            $t = $request->string('date_to')->trim();
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {
                $dateTo = $t;
            }
        }
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($request->filled('quotation')) {
            $term = Str::limit(trim($request->string('quotation')), 64, '');
            if ($term !== '') {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where('quotation_number', 'like', $like);
            }
        }

        return response()->json($query->get());
    }

    public function show(Request $request, int $id)
    {
        $quotation = SalesQuotation::with(['branch', 'user', 'approver', 'items.product.category', 'sale'])
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
            'delivery_charge' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.custom_item_name' => 'nullable|string|max:255',
            'items.*.custom_color' => 'nullable|string|max:255',
            'items.*.custom_thickness' => 'nullable|string|max:255',
            'items.*.custom_measurement' => 'nullable|string|max:255',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
            'items.*.cut_measurement_unit' => 'nullable|string|max:32',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.retail_unit_price' => 'nullable|numeric|min:0',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.is_long_span' => 'nullable|boolean',
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
                'delivery_charge' => (float) ($validated['delivery_charge'] ?? 0),
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
            ]);

            $this->syncItems($quotation, $this->normalizeQuotationItems($validated['items']));
            $this->recalculateTotals($quotation);
            $this->assignQuotationNumber($quotation);
            $quotation->refresh()->load(['items.product.category', 'branch', 'user']);

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
            'delivery_charge' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.custom_item_name' => 'nullable|string|max:255',
            'items.*.custom_color' => 'nullable|string|max:255',
            'items.*.custom_thickness' => 'nullable|string|max:255',
            'items.*.custom_measurement' => 'nullable|string|max:255',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
            'items.*.cut_measurement_unit' => 'nullable|string|max:32',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.retail_unit_price' => 'nullable|numeric|min:0',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.is_long_span' => 'nullable|boolean',
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
                'delivery_charge' => $validated['delivery_charge'] ?? 0,
                'rejection_reason' => null,
            ]);

            if ($quotation->status === 'rejected') {
                $quotation->update(['status' => 'draft']);
            }

            $quotation->items()->delete();
            $this->syncItems($quotation, $this->normalizeQuotationItems($validated['items']));
            $this->recalculateTotals($quotation);
            $this->assignQuotationNumber($quotation);
            $quotation->refresh()->load(['items.product.category', 'branch', 'user']);

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

        return response()->json($quotation->fresh()->load(['items.product.category', 'branch', 'user']));
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

        return response()->json($quotation->fresh()->load(['items.product.category', 'branch', 'user', 'approver']));
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

        return response()->json($quotation->fresh()->load(['items.product.category', 'branch', 'user']));
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

        return response()->json($quotation->fresh()->load(['sale', 'items.product.category']));
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

    protected function normalizeQuotationItems(array $items): array
    {
        return array_values(array_map(function (array $row) {
            $productId = isset($row['product_id']) && $row['product_id'] !== '' && $row['product_id'] !== null
                ? (int) $row['product_id']
                : null;
            $customName = $this->nullableString($row['custom_item_name'] ?? null);
            $description = $this->nullableString($row['description'] ?? null);

            if (! $description) {
                $description = $customName;
            }
            if (! $description && $productId) {
                $product = Product::find($productId);
                $description = $product ? ($this->nullableString($product->name) ?? 'Quoted item') : 'Quoted item';
            }
            if (! $description) {
                $description = 'Quoted item';
            }

            $row['product_id'] = $productId;
            $row['description'] = $description;
            $row['custom_item_name'] = $productId ? null : $customName;

            return $row;
        }, $items));
    }

    protected function syncItems(SalesQuotation $quotation, array $items): void
    {
        foreach ($items as $index => $row) {
            $qty = (float) $row['quantity'];
            $unit = (float) $row['unit_price'];
            $retailUnit = isset($row['retail_unit_price']) && $row['retail_unit_price'] !== '' && $row['retail_unit_price'] !== null
                ? (float) $row['retail_unit_price']
                : $unit;
            $isFree = ! empty($row['is_free']);
            $isLongSpan = ! empty($row['is_long_span']);
            $lineTotal = $isFree ? 0 : round($qty * $unit, 2);
            $productId = $row['product_id'] ?? null;

            SalesQuotationItem::create([
                'sales_quotation_id' => $quotation->id,
                'product_id' => $productId,
                'description' => $row['description'],
                'custom_item_name' => $productId ? null : $this->nullableString($row['custom_item_name'] ?? null),
                'custom_color' => $this->nullableString($row['custom_color'] ?? null),
                'custom_thickness' => $this->nullableString($row['custom_thickness'] ?? null),
                'custom_measurement' => $this->nullableString($row['custom_measurement'] ?? null),
                'cut_length' => $this->nullableNumeric($row['cut_length'] ?? null),
                'cut_width' => $this->nullableNumeric($row['cut_width'] ?? null),
                'cut_height' => $this->nullableNumeric($row['cut_height'] ?? null),
                'cut_measurement_unit' => $this->nullableString($row['cut_measurement_unit'] ?? null),
                'quantity' => $qty,
                'unit_price' => $unit,
                'retail_unit_price' => $retailUnit,
                'line_total' => $lineTotal,
                'is_free' => $isFree,
                'is_long_span' => $isLongSpan,
                'sort_order' => $index,
            ]);
        }
    }

    private function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim($value);

        return $t === '' ? null : $t;
    }

    private function nullableNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $n = (float) $value;

        return $n > 0 ? $n : null;
    }

    protected function recalculateTotals(SalesQuotation $quotation): void
    {
        $quotation->load('items');
        $quotedSubtotal = (float) $quotation->items->sum(function ($item) {
            return $item->is_free ? 0 : (float) $item->line_total;
        });
        $discount = max(0, (float) ($quotation->discount_amount ?? 0));
        $delivery = max(0, (float) ($quotation->delivery_charge ?? 0));
        $displaySubtotal = round($quotedSubtotal + $discount, 2);
        $afterDiscount = round($quotedSubtotal, 2);
        $taxRate = (float) ($quotation->tax_rate ?? 0);
        $taxAmount = round($afterDiscount * ($taxRate / 100), 2);
        $grand = round($afterDiscount + $taxAmount + $delivery, 2);

        $quotation->update([
            'subtotal' => $displaySubtotal,
            'discount_amount' => round($discount, 2),
            'tax_amount' => $taxAmount,
            'grand_total' => $grand,
        ]);
    }
}
