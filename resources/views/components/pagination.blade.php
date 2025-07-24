@if ($paginator->hasPages())
    <nav class="flex justify-center mt-6">
        <ul class="inline-flex items-center -space-x-px">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li><span class="px-3 py-2 rounded-l bg-gray-200 text-gray-400 cursor-not-allowed">Prev</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 rounded-l bg-white border text-gray-700 hover:bg-red-50">Prev</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-3 py-2 bg-white border text-gray-400">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="px-3 py-2 bg-red-500 text-white border">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="px-3 py-2 bg-white border text-gray-700 hover:bg-red-50">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 rounded-r bg-white border text-gray-700 hover:bg-red-50">Next</a></li>
            @else
                <li><span class="px-3 py-2 rounded-r bg-gray-200 text-gray-400 cursor-not-allowed">Next</span></li>
            @endif
        </ul>
    </nav>
@endif 