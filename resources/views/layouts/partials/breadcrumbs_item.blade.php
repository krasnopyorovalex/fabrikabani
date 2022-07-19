@if($catalog->parent)
    @include('layouts.partials.breadcrumbs_item', ['catalog' => $catalog->parent])
    <li><a href="{{ route('catalog.show', ['alias' => $catalog->parent->alias]) }}">{{ $catalog->parent->name }}</a></li>
@endif