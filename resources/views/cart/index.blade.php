@extends('layouts.app')

@section('title', $page->title)
@section('description', $page->description)
@push('og')
    <meta property="og:title" content="{{ $page->title }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->getUri() }}">
    <meta property="og:image" content="{{ asset($page->image ? $page->image->path : 'images/logo.png') }}">
    <meta property="og:description" content="{{ $page->description }}">
    <meta property="og:site_name" content="Всё для бани">
    <meta property="og:locale" content="ru_RU">
@endpush

@section('content')
    @includeWhen($page->slider, 'layouts.sections.slider', ['slider' => $page->slider])

    <section class="breadcrumbs-custom">
        <div class="breadcrumbs-custom-body parallax-content context-dark">
            <div class="container">
                <h2 class="breadcrumbs-custom-title">{{ $page->name }}</h2>
            </div>
        </div>
        <div class="breadcrumbs-custom-footer">
            <div class="container">
                <ul class="breadcrumbs-custom-path">
                    <li><a href="{{ route('page.show') }}">Главная</a></li>
                    <li class="active">{{ $page->name }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section section-content bg-default text-justify">
        <div class="container">
            <div class="row row-xl row-30 justify-content-center">
                <div class="col-md-12">
                    {!! $page->text !!}
                    @if(count($items))
                    <div class="table-custom-responsive cart_table">
                        <table class="table-custom table-cart">
                            <thead>
                            <tr>
                                <th>Наименование товара</th>
                                <th>Цена</th>
                                <th>Количество</th>
                                <th>Сумма</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr data-product="{{ $item->id }}">
                                    <td>
                                        @if($item->attributes->image)
                                        <a class="table-cart-figure" href="{{ $item->attributes->url }}">
                                            <img src="{{ $item->attributes->image }}" alt="" width="146" height="132"/>
                                        </a>
                                        @endif
                                        <a class="table-cart-link" href="{{ $item->attributes->url }}">{{ $item->name }}</a>
                                    </td>
                                    <td>{{ number_format($item->price, 0, '.', ' ') }} &#8381;@if($item->measure)/{!! $item->measure !!} @endif</td>
                                    <td>
                                        <div class="table-cart-stepper">
                                            <input class="form-input" type="number" data-zeros="false" value="{{ $item->quantity }}" min="1" max="20">
                                        </div>
                                    </td>
                                    <td class="product_price"><span>{{ number_format($item->quantity * $item->price, 0, '.', ' ') }}</span> &#8381;</td>
                                    <td><span class="fa-trash-o remove_item" title="Удалить товар" data-link="{{ route('cart.remove',['product' => $item->id]) }}"></span></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="5">
                                    Общая цена: <div class="total_price">{{ $total }}</div> &#8381;
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            <div class="row row-30">
                <div class="col-md-12 col-lg-12">
                    <h3 class="font-weight-medium">Форма оформления заказа</h3>
                    <form id="form__order-cart" class="rd-form rd-mailform form-checkout" action="{{ route('order.cart') }}" method="post" onsubmit="yaCounter54461437.reachGoal('ZAKAZ_TOVARA'); return true">
                        @csrf
                        <div class="row row-30">
                            <div class="col-sm-12">
                                <div class="form-wrap">
                                    <input class="form-input" placeholder="ФИО*" type="text" name="fio" autocomplete="off" required/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-wrap">
                                    <input class="form-input" placeholder="Телефон*" type="text" name="phone" autocomplete="off" required/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-wrap">
                                    <input class="form-input" placeholder="Email" type="email" autocomplete="off" name="email"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-wrap">
                                    <input class="form-input" placeholder="Адрес" type="text" autocomplete="off" name="address"/>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="button button-lg button-primary button-zakaria" type="submit">Отправить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @includeWhen($page->gallery, 'layouts.sections.gallery', ['gallery' => $page->gallery])

@endsection
