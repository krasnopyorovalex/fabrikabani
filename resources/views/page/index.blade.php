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
    @includeWhen($ourServices, 'layouts.sections.our_services')
    @includeWhen($page->gallery, 'layouts.sections.gallery', ['gallery' => $page->gallery])

    <!-- About Us-->
    <section class="section section-inset-2 bg-default text-md-left">
        <div class="container">
            <div class="row row-30 align-items-start justify-content-center">
                <div class="col-sm-4 col-md-4 col-lg-4 wow fadeInLeft">
                    <img src="" data-src="{{ asset('images/director.jpg') }}" alt="Прокопенко Сергей" />
                </div>
                <div class="col-md-8 col-lg-8">
                    <h4 class="wow fadeInRight decorate title">Про нас</h4>
                    <p class="offset-top-md-20 wow fadeInRight decorate" data-wow-delay=".2s">Немного расскажу о нашей компании. Компания на рынке Крыма с 2014 года. За время работы компании построено множество объектов от маленьких парных в квартире до полноценных СПА зон с несколькими видами бань, мокрыми зонами, бассейнами и купелями. <br /><br />Мы используем в строительстве передовые технологии и оборудование только надежных производителей. Заказывая строительство или оборудование в нашей компании Вы приобретаете не только товар или услугу, Вы приобретаете бесценный опыт, добытый нами. Если Вы планируете построить баню, сауну, хамам или СПА комплекс Вы получите в нашей компании Весь спектр услуг от разработки концепции и дизайн проекта, до реализации проекта под ключ.</p>
                    <p class="offset-top-md-20 wow fadeInRight decorate" data-wow-delay=".2s"><b>С Уважением Прокопенко Сергей</b></p>
                    <div class="group-middle d-md-flex justify-content-md-start wow fadeInRight" data-wow-delay=".3s">
                        <div class="form_question">
                            <div>
                                <div class="form_info"><p>Задать вопрос директору</p></div>
                                <form action="{{ route('send.question') }}" onsubmit="yaCounter54461437.reachGoal('VOPROS'); return true" class="rd-form rd-mailform rd-form-inline rd-form-inline-2" method="post" id="form__subscribe">
                                    @csrf
                                    <div class="form-wrap">
                                        <input class="form-input" id="subscribe-form-2-email" type="text" name="name" autocomplete="off" placeholder="Имя" required="" />
                                    </div>
                                    <div class="form-wrap">
                                        <input class="form-input" id="subscribe-form-2-email" type="text" name="phone" autocomplete="off" placeholder="Телефон" required="" />
                                    </div>
                                    <div class="form-button submit">
                                        <button class="button button-sm button-secondary button-zakaria" type="submit">
                                            Задать вопрос Директору
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @includeWhen($advantages, 'layouts.sections.advantages')
    @includeWhen($partners, 'layouts.sections.partners')

    <section class="section section-inset-2 bg-default text-md-left">
        <div class="container">
            <h5 class="text-center video_title">
                Посмотрите видео и Вы убедитесь в отличном качестве наших товаров и услуг.
            </h5>
        </div>
        <div class="container">
            <div class="row row-30 align-items-center justify-content-center justify-content-xl-between">
                <div class="col-md-4 col-lg-4 col-xs-12">
                    <div class="youtube-box" data-embed="bklKNNONAu0">
                        <div class="btn-play"></div>
                    </div>
                    <div class="block-center text-center">
                        <a class="button button-sm button-secondary button-zakaria" href="{{ route('page.show', ['alias' => 'services']) }}">Строительство бани и сауны</a>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4 col-xs-12">
                    <div class="youtube-box" data-embed="ChS8GTMbr_c">
                        <div class="btn-play"></div>
                    </div>
                    <div class="block-center text-center">
                        <a class="button button-sm button-secondary button-zakaria" href="{{ route('catalog.show', ['alias' => 'grilld']) }}">Печи GriilD</a>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4 col-xs-12">
                    <div class="youtube-box" data-embed="W_elJGTKwlQ">
                        <div class="btn-play"></div>
                    </div>
                    <div class="block-center text-center">
                        <a class="button button-sm button-secondary button-zakaria" href="{{ route('page.show', ['alias' => 'services']) }}#service_2">Строительство хамама</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="yandex-map-container" id="map-yandex"></div>
    </section>
@endsection
