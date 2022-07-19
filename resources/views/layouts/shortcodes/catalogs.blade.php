<div class="row row-30 row-lg-50">
    @foreach ($catalogs as $item)
        <div class="col-sm-4 col-md-3 col-lg-3 col-6">
        <article class="product">
            <div class="product-body">
                @if($item->image)
                    <div class="product-figure">
                        <a href="{{ $item->url }}">
                            <img src="" data-src="{{ asset($item->image->path) }}" alt="" width="220" height="160">
                        </a>
                    </div>
                @endif
                <h5 class="product-title">
                    <a href="{{ $item->url }}">{{ $item->name }}</a>
                </h5>
            </div>
            <div class="product-button-wrap">
                <div class="product-button">
                    <a class="button button-secondary button-zakaria fl-bigmug-line-search74" href="{{ $item->url }}"></a>
                </div>
            </div>
        </article>
        </div>
    @endforeach
</div>
