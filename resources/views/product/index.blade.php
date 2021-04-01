@extends('layouts.app')

@section('title', $product->title)
@section('description', $product->description)
@push('og')
    <meta property="og:title" content="{{ $product->title }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->getUri() }}">
    <meta property="og:image" content="{{ asset($product->image ? $product->image->path : 'images/logo.png') }}">
    <meta property="og:description" content="{{ $product->description }}">
    <meta property="og:site_name" content="Всё для бани">
    <meta property="og:locale" content="ru_RU">
@endpush

@section('content')
    @includeWhen($product->slider, 'layouts.sections.slider', ['slider' => $product->slider])

    <section class="breadcrumbs-custom">
        <div class="breadcrumbs-custom-body parallax-content context-dark">
            <div class="container">
                <h2 class="breadcrumbs-custom-title">{{ $product->name }}</h2>
            </div>
        </div>
        <div class="breadcrumbs-custom-footer">
            <div class="container">
                <ul class="breadcrumbs-custom-path">
                    <li><a href="{{ route('page.show') }}">Главная</a></li>
                    <li><a href="{{ route('page.show', ['alias' => 'catalog']) }}">Каталог</a></li>
                    @if($product->catalog->parent && $product->catalog->parent->parent)
                        <li><a href="{{ route('catalog.show', ['alias' => $product->catalog->parent->parent->alias]) }}">{{ $product->catalog->parent->parent->name }}</a></li>
                    @endif
                    @if($product->catalog->parent)
                        <li><a href="{{ route('catalog.show', ['alias' => $product->catalog->parent->alias]) }}">{{ $product->catalog->parent->name }}</a></li>
                    @endif
                    <li><a href="{{ route('catalog.show', ['alias' => $product->catalog->alias]) }}">{{ $product->catalog->name }}</a></li>
                    <li class="active">{{ $product->name }}</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Single Product-->
    <section class="section section-sm section-first bg-default">
        <div class="container">
            <div class="row row-30">
                <div class="col-lg-5">
                    @if($product->image)
                    <div class="single-product-image">
                        <img src="{{ $product->image->path }}" alt="{{ $product->image->alt }}" title="{{ $product->image->title }}">
                    </div>
                    @endif
                </div>
                <div class="col-lg-7">
                    <div class="single-product">
                        <h3 class="text-transform-none font-weight-medium">{{ $product->name }}</h3>
                        <div class="group-md group-middle">
                            <div class="single-product-price">
                                @if($product->on_request)
                                    <a href="#form__subscribe" class="to-form__subscribe">Запросить стоимость</a>
                                @else
                                    Цена: {!! $product->getPrice() !!}@if($product->measure)/{!! $product->measure !!} @endif
                                @endif

                                @if($product->priceNotIncludedDelivery() || $product->on_request || $product->not_include_delivery || $product->id === 16766 || $product->category_id === 298)
                                    *
                                @endif
                            </div>
                        </div>
                        <div class="product_text">
                            {!! $product->text !!}
                        </div>
                        <hr class="hr-gray-100">
                        @if($product->priceNotIncludedDelivery() || $product->not_include_delivery)
                            <div class="p_info">*Стоимость указана без учета доставки</div>
                        @endif
                        @if($product->on_request)
                            <div class="p_info">*Стоимость указана без учета доставки в г. Севастополь</div>
                        @endif
                        @if($product->id === 16766 || $product->catalog_id === 298)
                            <div class="p_info">* Цена указана с учётом доставки в г.Севастополь</div>
                        @endif
                        @if(! $product->on_request)
                        <div class="group-xs group-middle">
                            <div class="button button-lg button-secondary button-zakaria add_to-cart" data-cart="{{ route('cart.add', ['product' => $product->id]) }}">Купить</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-sm section-first bg-default">
        <div class="container">
            <div class="row">
                {!! $product->props !!}
            </div>
        </div>
    </section>

    @includeWhen(count($product->images), 'layouts.sections.product_gallery', ['$product' => $product])

    @if($product->on_request)
        <section class="with_bg">
            <div class="container">
                <div class="row row-30 align-content-end">
                    <div class="col-md-12 group-middle wow fadeInRight" data-wow-delay=".3s">
                        <div class="form_question in_content">
                            <div class="form_info"><p>Узнать стоимость - «{{ $product->name }}»</p></div>
                            <div>
                                <form action="{{ route('send.order_product') }}" onsubmit="yaCounter54461437.reachGoal('ZAKAZ_HOBBIT'); return true" class="rd-form rd-mailform rd-form-inline rd-form-inline-2" method="post" id="form__subscribe">
                                    @csrf
                                    <input type="hidden" name="product" value="{{ $product->name }}">
                                    <div class="form-wrap">
                                        <input class="form-input" id="subscribe-form-2-email" type="text" name="name" autocomplete="off" placeholder="Имя" required="" />
                                    </div>
                                    <div class="form-wrap">
                                        <input class="form-input" id="subscribe-form-2-email" type="text" name="phone" autocomplete="off" placeholder="Телефон" required="" />
                                    </div>
                                    <div class="form-button submit">
                                        <button class="button button-sm button-secondary button-zakaria" type="submit">
                                            Заказать
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

@endsection
