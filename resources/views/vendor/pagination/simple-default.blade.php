@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true"><span>{!! '&laquo; Previous' !!}</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">{!! '&laquo; Previous' !!}</a></li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">{!! 'Next &laquo;' !!}</a></li>
            @else
                <li class="disabled" aria-disabled="true"><span>{!! 'Next &laquo;' !!}</span></li>
            @endif
        </ul>
    </nav>
@endif
