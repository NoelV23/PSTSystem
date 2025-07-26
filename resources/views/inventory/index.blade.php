@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold text-gray-900">Inventory Management</h2>
                <p class="text-gray-600 mt-1">Select a branch to manage its inventory</p>
            </div>
        </div>

        <!-- Branch Selection Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($branches as $branch)
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $branch->name }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $branch->location }}
                        </div>
                        
                        @if($branch->phone)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            {{ $branch->phone }}
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button onclick="manageInventory({{ $branch->id }})" class="flex items-center bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Manage Inventory
                        </button>
                        
                        <div class="text-sm text-gray-500">
                            <span id="stock-count-{{ $branch->id }}">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Empty State -->
        @if($branches->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No branches available</h3>
            <p class="text-gray-600">There are no active branches to manage inventory for.</p>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load stock counts for each branch
    const branches = @json($branches);
    branches.forEach(branch => {
        loadBranchSummary(branch.id);
    });
});

function loadBranchSummary(branchId) {
    fetch(`/api/inventory/branch/${branchId}/summary`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const countElement = document.getElementById(`stock-count-${branchId}`);
        countElement.textContent = `${data.total_products} products`;
    })
    .catch(error => {
        console.error('Error loading branch summary:', error);
        const countElement = document.getElementById(`stock-count-${branchId}`);
        countElement.textContent = 'Error loading';
    });
}

function manageInventory(branchId) {
    // Store selected branch in localStorage for future visits
    localStorage.setItem('lastSelectedBranch', branchId);
    
    // Redirect to branch-specific inventory page
    window.location.href = `/inventory/${branchId}`;
}
</script>
@endsection