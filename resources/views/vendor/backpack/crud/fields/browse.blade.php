{{--
    Campo "browse": input di testo con il file manager elFinder in popup.

    In Backpack 3 questo campo arrivava dal core; dalla 6 e' stato spostato nel
    pacchetto a pagamento backpack/pro, per cui /admin/icon/create andava in 500
    con BackpackProRequiredException. elFinder pero' e' gia' installato e
    instradato (barryvdh/laravel-elfinder, rotta elfinder.popup), quindi qui lo
    ricostruiamo con gli asset che il progetto ha gia' in public/vendor/backpack.

    Il popup di elFinder (resources/views/vendor/elfinder/standalonepopup.php)
    chiama window.parent.processSelectedFile(path, input_id) e poi
    parent.jQuery.colorbox.close(): per questo il modale e' un iframe colorbox.
--}}

@php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitBrowseElement';

    // l'id deve essere univoco: il popup lo riceve come parametro di rotta e lo
    // usa per riconsegnare il path al campo giusto
    $field['attributes'] = $field['attributes'] ?? [];
    $field['attributes']['id'] = $field['attributes']['id'] ?? 'browse_'.Str::slug($field['name'], '_').'_'.Str::random(6);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="input-group">
        <input
            type="text"
            name="{{ $field['name'] }}"
            value="{{ old_empty_or_null($field['name'], '') ?? $field['value'] ?? $field['default'] ?? '' }}"
            @include('crud::fields.inc.attributes')
        >
        <div class="input-group-append">
            <button type="button" class="btn btn-secondary browse-button"
                    data-browse-url="{{ url(config('backpack.base.route_prefix').'/elfinder/popup/'.$field['attributes']['id']) }}">
                <i class="fa fa-folder-open"></i> {{ trans('pigarden.icon.browse') }}
            </button>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}

@push('crud_fields_styles')
    @once
    <link rel="stylesheet" href="{{ asset('vendor/backpack/colorbox/example1/colorbox.css') }}">
    @endonce
@endpush

@push('crud_fields_scripts')
    @once
    <script src="{{ asset('vendor/backpack/colorbox/jquery.colorbox-min.js') }}"></script>
    <script>
        // chiamata dall'iframe di elFinder quando si conferma un file
        function processSelectedFile(filePath, inputId) {
            $('#' + inputId).val(filePath).trigger('change');
        }

        function bpFieldInitBrowseElement(element) {
            var button = element.find('.browse-button');
            var input = element.find('input[type=text]');

            button.on('click', function (e) {
                e.preventDefault();
                $.colorbox({
                    href: $(this).data('browse-url'),
                    fastIframe: false,
                    iframe: true,
                    width: '80%',
                    height: '80%'
                });
            });

            element.on('CrudField:disable', function (e) {
                input.prop('disabled', true);
                button.prop('disabled', true);
            });
            element.on('CrudField:enable', function (e) {
                input.removeAttr('disabled');
                button.removeAttr('disabled');
            });
        }
    </script>
    @endonce
@endpush
