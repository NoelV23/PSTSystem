<form method="GET" action="{{ $action ?? '' }}" class="flex items-center gap-2 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-4 py-2 border rounded w-64 focus:outline-none focus:ring-2 focus:ring-[#0a2d9a]">
    {{ $filters ?? '' }}
    <button type="submit" class="bg-[#0a2d9a] hover:bg-[#08247b] text-white px-4 py-2 rounded">Search</button>
</form> 