{{--
    Extra tags injected into Backpack's <head> (official hook: Backpack includes
    this view from inc/head.blade.php when it exists).

    FontAwesome 4.7 is loaded here rather than through backpack.ui.styles on
    purpose. That config array is piped through Basset, which mirrors assets
    into storage — and a stylesheet whose glyphs live in a sibling "../fonts/"
    folder ends up with dangling font URLs, so every icon renders as an empty
    box. Serving the copy already bundled in public/ keeps the relative paths
    intact and works with no internet access at all.

    Needed because this app's views use FontAwesome 4 class names
    (fa fa-dashboard, fa-picture-o, fa-files-o, ...) while Backpack 6 ships
    Line Awesome.
--}}
<link rel="stylesheet" href="{{ asset('vendor/adminlte/bower_components/font-awesome/css/font-awesome.min.css') }}">
