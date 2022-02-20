@extends('layouts.app')

@section('title', $catalog->title)
@section('description', $catalog->description)
@push('og')
    <meta property="og:title" content="{{ $catalog->title }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->getUri() }}">
    <meta property="og:image" content="{{ asset($catalog->image ? $catalog->image->path : 'images/logo.png') }}">
    <meta property="og:description" content="{{ $catalog->description }}">
    <meta property="og:site_name" content="Всё для бани">
    <meta property="og:locale" content="ru_RU">
@endpush

@section('content')
    @includeWhen($catalog->slider, 'layouts.sections.slider', ['slider' => $catalog->slider])

    <section class="breadcrumbs-custom">
        <div class="breadcrumbs-custom-body parallax-content context-dark">
            <div class="container">
                <h2 class="breadcrumbs-custom-title">{{ $catalog->name }}</h2>
            </div>
        </div>
        <div class="breadcrumbs-custom-footer">
            <div class="container">
                <ul class="breadcrumbs-custom-path">
                    <li><a href="{{ route('page.show') }}">Главная</a></li>
                    <li><a href="{{ route('page.show', ['alias' => 'catalog']) }}">Каталог</a></li>
                    @if($catalog->parent && $catalog->parent->parent)
                        <li><a href="{{ route('catalog.show', ['alias' => $catalog->parent->parent->alias]) }}">{{ $catalog->parent->parent->name }}</a></li>
                    @endif
                    @if($catalog->parent)
                        <li><a href="{{ route('catalog.show', ['alias' => $catalog->parent->alias]) }}">{{ $catalog->parent->name }}</a></li>
                    @endif
                    <li class="active">{{ $catalog->name }}</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Section Shop-->
    <section class="section section-xxl bg-default text-md-left">
        <div class="container">
            <div class="row row-50">
                <div class="col-lg-4 col-xl-3">
                    <div class="aside row row-30 row-md-50 justify-content-md-between">
                        <div class="aside-item col-sm-6 col-lg-12">
                            <h6 class="aside-title">Категории каталога</h6>
                            <div class="row row-10 row-lg-20 gutters-10">
                                @if($catalogs)
                                <ul class="list-shop-filter">
                                    @foreach($catalogs as $cat)
                                    <li class="{{ $cat->alias === request('alias') ? 'active' : '' }}"><a href="{{ $cat->url }}">{{ $cat->name }}</a></li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-9">
                    <div class="row row-30 row-lg-50">
                        @if($products)
                            @foreach($products as $product)
                                <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4">
                                    <!-- Product-->
                                    <article class="product">
                                        <div class="product-body">
                                            @if($product->image)
                                            <div class="product-figure">
                                                <a href="{{ $product->url }}">
                                                    <img src="" data-src="{{ $product->image->path }}" alt="{{ $product->image->alt }}" title="{{ $product->image->title }}" width="220" height="160"/>
                                                </a>
                                            </div>
                                            @endif
                                            <h5 class="product-title"><a href="{{ $product->url }}">{{ $product->name }}</a></h5>
                                            @if(!$product->on_request)
                                                <div class="product-price-wrap">
                                                    <div class="product-price">{!! $product->getPrice() !!}</div>
                                                </div>
                                            @endif
                                        </div>
                                        @if($product->label)
                                        <span class="product-badge product-badge-sale">{{ $product->getLabelName($product->label) }}</span>
                                        @endif
                                        <div class="product-button-wrap">
                                            <div class="product-button">
                                                <a class="button button-secondary button-zakaria fl-bigmug-line-search74" href="{{ $product->url }}"></a>
                                            </div>
                                            @if(!$product->on_request)
                                            <div class="product-button">
                                                <div class="button button-primary button-zakaria fl-bigmug-line-shopping202 add_to-cart" data-cart="{{ route('cart.add', ['product' => $product->id]) }}"></div>
                                            </div>
                                            @endif
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @if($products)
                    <div class="pagination-wrap">
                        <nav>
                            {{ $products->links() }}
                        </nav>
                    </div>
                    @endif

                    @includeWhen($catalog->catalogs, 'layouts.shortcodes.catalogs', ['catalogs' => $catalog->catalogs])

                </div>
            </div>
        </div>
    </section>
    @include('layouts.forms.order')
@endsection
