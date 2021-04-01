@extends('layouts.admin')

@section('breadcrumb')
    <li><a href="{{ route('admin.catalogs.index') }}">Категории каталога</a></li>
    <li><a href="{{ route('admin.catalog_products.index', $catalogProduct->catalog) }}">Список товаров</a></li>
    <li class="active">Форма редактирования товара</li>
@endsection

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">Форма редактирования товара</div>

        <div class="panel-body">
            @include('layouts.partials.errors')
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#main" data-toggle="tab">Основное</a></li>
                    <li><a href="#images" data-toggle="tab">Галерея</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="main">
                        <form action="{{ route('admin.catalog_products.update', ['id' => $catalogProduct->id]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label for="label">Метка:</label>
                                            <select class="form-control border-blue border-xs select-search" id="slider_id" name="label" data-width="100%">
                                                @foreach ($catalogProduct->getLabels() as $key => $value)
                                                    <option value="{{ $key }}" {{ $catalogProduct->isSelectedLabel($key) ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @input(['name' => 'name', 'label' => 'Название', 'entity' => $catalogProduct])
                                        @input(['name' => 'title', 'label' => 'Title', 'entity' => $catalogProduct])
                                        @input(['name' => 'description', 'label' => 'Description', 'entity' => $catalogProduct])

                                        @input(['name' => 'price', 'label' => 'Цена', 'entity' => $catalogProduct])

                                        @input(['name' => 'alias', 'label' => 'Alias', 'entity' => $catalogProduct])

                                        @input(['name' => 'measure', 'label' => 'Мера возле цены', 'entity' => $catalogProduct])

                                        @textarea(['name' => 'text', 'label' => 'Текст', 'entity' => $catalogProduct])
                                        @textarea(['name' => 'props', 'label' => 'Текст', 'id' => 'full-props', 'entity' => $catalogProduct])

                                        @checkbox(['name' => 'on_request', 'label' => 'Стоимость под запрос?', 'entity' => $catalogProduct])
                                    </div>
                                    <div class="col-md-3">
                                        @if ($catalogProduct->image)
                                            <div class="panel panel-flat border-blue border-xs" id="image__box">
                                                <div class="panel-body">
                                                    <img src="{{ asset($catalogProduct->image->path) }}" alt="" class="upload__image">

                                                    <div class="btn-group btn__actions">
                                                        <button data-toggle="modal" data-target="#modal_info" type="button" class="btn btn-primary btn-labeled btn-sm"><b><i class="icon-pencil4"></i></b> Атрибуты</button>

                                                        <button type="button" data-href="{{ route('admin.images.destroy', ['id' => $catalogProduct->image->id]) }}" class="btn delete__img btn-danger btn-labeled btn-labeled-right btn-sm">Удалить <b><i class="icon-trash"></i></b></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @imageInput(['name' => 'image', 'type' => 'file', 'entity' => $catalogProduct, 'label' => 'Выберите изображение на компьютере'])
                                    </div>
                                </div>
                                @submit_btn()
                            </form>
                        </div>
                    <div class="tab-pane" id="images">
                        <form action="#" enctype="multipart/form-data" method="post">
                            <div class="form-group">
                                <div class="col-lg-12">
                                    <input type="hidden" name="catalogProductId" value="{{ $catalogProduct->id }}">
                                    <input type="hidden" name="uploadUrl" value="{{ route('admin.catalog_product_images.store', $catalogProduct) }}">
                                    <input type="hidden" name="updatePositionUrl" value="{{ route('admin.catalog_product_images.update_positions') }}">
                                    <input type="file" class="file-input-ajax" multiple="multiple" name="upload" accept="image/*">
                                </div>
                            </div>
                        </form>
                        <div class="clearfix"></div>
                        @if ($catalogProduct->images)
                            <div id="_images_box">
                                @include('admin.catalog_products._images_box')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($catalogProduct->image)
        @include('layouts.partials._image_attributes_popup', ['image' => $catalogProduct->image])
    @endif
    <div id="edit-image" class="modal fade"></div>
    @push('scripts')
        <script src="{{ asset('dashboard/assets/js/plugins/ui/dragula.min.js') }}"></script>
        <script src="{{ asset('dashboard/assets/js/pages/extension_dnd.js') }}"></script>
        <script src="{{ asset('dashboard/assets/js/plugins/uploaders/fileinput.min.js') }}"></script>
        <script src="{{ asset('dashboard/assets/js/pages/uploader_bootstrap.js') }}"></script>
        <script src="{{ asset('dashboard/ckeditor/ckeditor.js') }}"></script>
    @endpush
@endsection
