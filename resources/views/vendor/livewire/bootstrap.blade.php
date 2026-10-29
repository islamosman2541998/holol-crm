@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
        : '';

    $pageName = $paginator->getPageName();
@endphp

<div>
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
                    <li>
                        <button type="button" class="list-page" wire:click="previousPage('{{ $pageName }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="السابق">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="list-page is-gap">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li wire:key="paginator-{{ $pageName }}-page-{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span class="list-page is-active" aria-current="page">{{ $page }}</span>
                                @else
                                    <button type="button" class="list-page" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $page }}</button>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li>
                        <button type="button" class="list-page" wire:click="nextPage('{{ $pageName }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="التالي">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    </li>
                @else
                    <li><span class="list-page is-disabled" aria-disabled="true" aria-label="التالي"><i class="bi bi-chevron-left"></i></span></li>
                @endif
            </ul>
        </nav>
    @endif
</div>
