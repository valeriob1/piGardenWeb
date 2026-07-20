<?php

/*
|--------------------------------------------------------------------------
| piGardenWeb — CoreUI v2 theme overrides
|--------------------------------------------------------------------------
|
| The theme's own config wins over config/backpack/ui.php, so the layout
| classes have to be overridden here. The theme ships a light sidebar/header
| (bg-light), which is why the panel looked washed out after the upgrade;
| these restore the dark sidebar and coloured header of the original design.
|
| Background options: bg-dark, bg-primary, bg-secondary, bg-danger, bg-warning,
| bg-success, bg-info, bg-blue, bg-light-blue, bg-indigo, bg-purple, bg-pink,
| bg-red, bg-orange, bg-yellow, bg-green, bg-teal, bg-cyan, bg-white.
| Pair a dark background with "navbar-dark" / "sidebar-dark" to keep the links
| readable — tweak the two lines below to taste.
|
*/

return [

    'layout' => 'top_left',

    'classes' => [

        'header' => 'app-header navbar navbar-dark bg-purple border-0',

        'body' => 'app aside-menu-fixed sidebar-lg-show',

        'sidebar' => 'sidebar sidebar-dark bg-dark',

        'footer' => 'app-footer d-print-none',

    ],

];
