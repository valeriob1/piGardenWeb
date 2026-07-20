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

    // NOTE: FontAwesome 4.7 (which this app's views rely on) is NOT loaded from
    // here. Anything in 'styles' goes through Basset, which mirrors assets into
    // storage and breaks the stylesheet's relative "../fonts/" URLs, leaving
    // every icon as an empty box. It is loaded instead as a plain <link> from
    // resources/views/vendor/backpack/ui/inc/header_metas.blade.php, pointing at
    // the copy already bundled in public/.

    // NOTE: the layout classes (sidebar/header colours) are NOT set here —
    // the active theme's config takes precedence over this file, so they live
    // in config/backpack/theme-coreuiv2.php instead.

];
