@extends(backpack_view('blank'))

@section('after_scripts')
    {{-- jQuery UI (REQUIRED by elFinder), served locally: it was loaded from
         ajax.googleapis.com, so the file manager broke whenever the box running
         piGardenWeb had no internet access. jQuery itself comes from Backpack. --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/bower_components/jquery-ui/themes/smoothness/jquery-ui.min.css') }}" />
    <script src="{{ asset('vendor/adminlte/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="<?= asset($dir.'/css/elfinder.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('vendor/backpack/elfinder/elfinder.backpack.theme.css') ?>">

    <!-- elFinder JS (REQUIRED) -->
    <script src="<?= asset($dir.'/js/elfinder.min.js') ?>"></script>

    <?php if ($locale) { ?>
    <!-- elFinder translation (OPTIONAL) -->
    <script src="<?= asset($dir."/js/i18n/elfinder.$locale.js") ?>"></script>
    <?php } ?>

    <!-- elFinder initialization (REQUIRED) -->
    <script type="text/javascript" charset="utf-8">
        // Documentation for client options:
        // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
        $().ready(function() {
            $('#elfinder').elfinder({
                // set your elFinder options here
                <?php if ($locale) { ?>
                    lang: '<?= $locale ?>', // locale
                <?php } ?>
                customData: {
                    _token: '<?= csrf_token() ?>'
                },
                // the Backpack theme is loaded above; without this elFinder also
                // requests css/theme.css relative to the page (/admin/css/theme.css: 404)
                cssAutoLoad: false,
                // use the screen height instead of a fixed 400px box
                height: Math.max(400, $(window).height() - 260),
                uiOptions: {
                    // on phones the folder tree took ~2/3 of the width: start
                    // with it hidden (the toolbar button shows it again).
                    // elFinder deep-merges this into its defaults.
                    navbar: { autoHideUA: ['Mobile'] }
                },
                url : '<?= route("elfinder.connector") ?>'  // connector URL
            });
        });
    </script>
@endsection

@section('header')
    <section class="container-fluid d-flex flex-wrap align-items-baseline justify-content-between mb-2 d-print-none">
      <h1 class="mb-0 mr-3">
        {{ trans('backpack::crud.file_manager') }}
      </h1>
      <ol class="breadcrumb bg-transparent p-0 mb-0 d-none d-lg-flex">
        <li class="breadcrumb-item"><a href="{{ url(config('backpack.base.route_prefix')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ trans('backpack::crud.file_manager') }}</li>
      </ol>
    </section>
@endsection

@section('content')

    <!-- Element where elFinder will be created (REQUIRED) -->
    <div id="elfinder"></div>

@endsection
