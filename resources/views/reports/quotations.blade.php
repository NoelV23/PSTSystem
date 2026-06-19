@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Sales Quotations Report</h1>
                <p class="text-gray-600 mt-1">Track quotation pipeline and conversions</p>
            </div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('reports.quotations') }}" class="grid grid-cols-1 md:grid-cols-{{ auth()->user()->role === 'admin' ? '5' : '4' }} gap-4">
            @if(auth()->user()->role === 'admin')
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
            @endif
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Statuses</option>
                    @foreach(['draft', 'pending_approval', 'approved', 'rejected'] as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-red-600 text-white font-medium rounded-lg transition duration-200">Apply Filters</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Total Quotations</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalQuotations) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Quoted Value</p>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalQuotedValue, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Approved</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($approvedCount) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Converted to Sale</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($convertedCount) }}</p>
        </div>
    </div>

    @if($statusStats->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($statusStats as $statusKey => $stats)
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $statusKey)) }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ $stats['count'] }} quotes</p>
                <p class="text-sm text-gray-500">₱{{ number_format($stats['total'], 2) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Quotation Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quote #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prepared By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($quotations as $quotation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $quotation->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <a href="{{ url('/sales-quotations/'.$quotation->id.'/print') }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $quotation->quotation_number ?: '#'.$quotation->id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $quotation->customer_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $quotation->branch->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $quotation->user->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badge = match($quotation->status) {
                                    'approved' => 'bg-green-100 text-green-800',
                                    'pending_approval' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($quotation->grand_total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($quotation->sale_id)
                                <a href="{{ url('/sales?highlight='.$quotation->sale_id) }}" class="text-blue-600 hover:underline">#{{ $quotation->sale_id }}</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No quotations found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
