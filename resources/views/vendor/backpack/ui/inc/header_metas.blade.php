{{--
    Extra tags injected into Backpack's <head> (official hook: Backpack includes
    this view from inc/head.blade.php when it exists).

    Both stylesheets are pushed to the 'after_styles' stack rather than emitted
    here directly: this partial is included BEFORE the theme's own styles, so a
    plain <link> would be overridden by Bootstrap (e.g. .card beating
    .zone-card). Pushing puts them after the theme, where app rules belong.

    FontAwesome 4.7 is served from the copy bundled in public/ instead of going
    through backpack.ui.styles: that config array is piped through Basset, which
    mirrors assets into storage and breaks the stylesheet's relative "../fonts/"
    URLs, leaving every icon as an empty box. Needed because this app's views use
    FontAwesome 4 class names (fa fa-dashboard, fa-picture-o, ...) while
    Backpack 6 ships Line Awesome.
--}}
@push('after_styles')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pigarden.css') }}?v={{ @filemtime(public_path('css/pigarden.css')) }}">
@endpush

{{-- Zone state labels for public/js/base.js. Declared here so every admin page
     that refreshes zones over AJAX gets the translated text (the dashboard is
     not the only one — the zone page uses the same partial and script). --}}
<script>
    var pigardenZoneStateOpen = @json(trans('pigarden.zone_state_open'));
    var pigardenZoneStateClosed = @json(trans('pigarden.zone_state_closed'));
</script>
