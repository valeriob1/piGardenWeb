<?php

/*
|--------------------------------------------------------------------------
| piGardenWeb admin UI
|--------------------------------------------------------------------------
|
| Backpack 6 reads branding and layout classes from THIS file (plus the active
| theme's own config). The project's old config/backpack/base.php still holds
| these keys but Backpack no longer looks there, which is why the panel showed
| the default "Backpack" name and an all-white sidebar after the upgrade.
|
| Only the keys that differ from the package defaults are set here; everything
| else falls back to vendor/backpack/crud/src/config/backpack/ui.php.
|
*/

return [

    // ------
    // BRANDING
    // ------

    'project_name' => 'piGardenWeb',
    'project_logo' => '<b>piGarden</b>Web',

    'developer_name' => 'lejubila',
    'developer_link' => 'http://lejubila.net',
    'show_powered_by' => true,

    // -------------
    // GLOBAL STYLES
    // -------------

    // The app's views (sidebar, dashboard, zone pages, custom buttons…) use
    // FontAwesome 4 class names such as "fa fa-dashboard", "fa-picture-o",
    // "fa-files-o". Backpack 6 ships Line Awesome instead, so without this the
    // icons silently render as nothing. Loading FA 4.7 keeps every existing
    // icon working without rewriting the views.
    'styles' => [
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
    ],

    // NOTE: the layout classes (sidebar/header colours) are NOT set here —
    // the active theme's config takes precedence over this file, so they live
    // in config/backpack/theme-coreuiv2.php instead.

];
