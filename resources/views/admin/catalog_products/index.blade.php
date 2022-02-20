@extends('layouts.admin')

@section('breadcrumb')
    <li><a href="{{ route('admin.catalogs.index') }}">Категории каталога</a></li>
    <li class="active">Список товаров</li>
@endsection

@section('content')

    <a href="{{ route('admin.catalog_products.create', ['catalog' => $catalog]) }}" type="button" class="btn bg-primary">
        Добавить
        <i class="icon-stack-plus position-right"></i>
    </a>

    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr class="border-solid">
                <th>#</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Обновлена</th>
                <th></th>
            </tr>
            </thead>
            <tbody id="table__dnd">
            @foreach($catalogProducts as $catalogProduct)
                <tr data-id="{{ $catalogProduct->id }}">
                    <td><span class="label label-primary">{{ request('page') > 1 ? request('page') * 50 - 50 + $loop->iteration : $loop->iteration }}</span></td>
                    <td>{{ $catalogProduct->name }}</td>
                    <td><span class="label label-primary bg-teal-400">{{ $catalogProduct->catalog->name }}</span></td>
                    <td><span class="label label-primary bg-brown-400"><b>{!! $catalogProduct->getPrice() !!}</b></span></td>
                    <td><span class="label label-primary">{{ $catalogProduct->updated_at->diffForHumans() }}</span></td>
                    <td>
                        <div>
                            <a href="{{ route('admin.catalog_products.edit', $catalogProduct) }}"><i class="icon-pencil7"></i></a>
                            <form method="POST" action="{{ route('admin.catalog_products.destroy', $catalogProduct) }}" class="form__delete">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <input type="hidden" value="{{ $catalogProduct->catalog_id }}" name="catalog_id">
                                <button type="submit" class="last__btn">
                                    <i class="icon-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $catalogProducts->links('vendor.pagination.in_admin') }}
    </div>
    @push('scripts')
        <script src="{{ asset('dashboard/assets/js/plugins/ui/dragula.min.js') }}"></script>
        <script src="{{ asset('dashboard/assets/js/pages/extension_dnd.js') }}"></script>
    @endpush
@endsection
