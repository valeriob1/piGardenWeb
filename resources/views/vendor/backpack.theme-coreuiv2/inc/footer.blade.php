{{-- The CoreUI footer is a flex container: loose text nodes and the old BS3
     pull-right/hidden-xs classes ran together ("Backpack for LaravelpiGardenWeb",
     "Realizzato dalejubila"). Wrap each part in its own block. --}}
<div class="mr-auto">
    piGardenWeb v. <i>{{implode('.', config('pigarden.version'))}}</i>
    @if (config('backpack.base.developer_link') && config('backpack.base.developer_name'))
        &ndash; {{ trans('backpack::base.handcrafted_by') }}
        <a target="_blank" href="{{ config('backpack.base.developer_link') }}">{{ config('backpack.base.developer_name') }}</a>
    @endif
</div>

@if (config('backpack.base.show_powered_by'))
    <div class="ml-auto d-none d-sm-block">
        {{ trans('backpack::base.powered_by') }} <a target="_blank" href="http://backpackforlaravel.com?ref=panel_footer_link">Backpack for Laravel</a>
    </div>
@endif
