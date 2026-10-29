@if ($paginator->hasPages())
    <nav class="list-pagination" aria-label="التنقل بين الصفحات">
        <div class="list-pagination-summary">
            عرض
            <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
            من أصل
            <strong>{{ $paginator->total() }}</strong>
        </div>

        <ul class="list-pagination-pages">
            @if ($paginator->onFirstPage())
                <li><span class="list-page is-disabled" aria-disabled="true" aria-label="السابق"><i class="bi bi-chevron-right"></i></span></li>
            @else
                <li><a class="list-page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="السابق"><i class="bi bi-chevron-right"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="list-page is-gap">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="list-page is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="list-page" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="list-page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="التالي"><i class="bi bi-chevron-left"></i></a></li>
            @else
                <li><span class="list-page is-disabled" aria-disabled="true" aria-label="التالي"><i class="bi bi-chevron-left"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
