{{-- piGardenWeb sidebar.
     Markup uses the CoreUI v2 classes the Backpack 6 theme expects
     (nav-item / nav-link / nav-icon / nav-title / nav-dropdown); the old
     AdminLTE markup (<li> + <li class="header"> + treeview) rendered as
     unstyled links. Icons are FontAwesome 4 — see config/backpack/ui.php. --}}

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="nav-icon fa fa-dashboard"></i> {{ trans('backpack::base.dashboard') }}
    </a>
</li>

<li class="nav-title">{{ strtoupper(trans('pigarden.zones')) }}</li>
@forelse( \App\Zones::get() as $zone )
    <li class="nav-item">
        <a class="nav-link link-zone-{{$zone->name}}" href="{{route('zone.edit', ['zone' => $zone->name])}}">
            <i class="nav-icon fa {{$zone->state == 0 ? 'fa-toggle-off' : 'fa-toggle-on'}}"></i> {{$zone->name_stripped}}
        </a>
    </li>
@empty
    <li class="nav-item">
        @if(\App\Zones::$unavailable)
            <a class="nav-link text-warning" href="{{ backpack_url('dashboard') }}" title="{{ trans('pigarden.error_connection_title') }}">
                <i class="nav-icon fa fa-plug"></i> <em>{{ trans('pigarden.zones_unavailable') }}</em>
            </a>
        @else
            <span class="nav-link disabled text-muted"><em>{{ trans('pigarden.zones_empty') }}</em></span>
        @endif
    </li>
@endforelse

<li class="nav-title">LOG</li>
<li class="nav-item">
    <a class="nav-link" href="{{backpack_url('log')}}">
        <i class="nav-icon fa fa-list"></i> {{ trans('pigarden.log.title') }}
    </a>
</li>

<li class="nav-title">{{ strtoupper(trans('pigarden.setup')) }}</li>
@if(backpack_user()->hasPermissionTo('manage setup', backpack_guard_name()))
    <li class="nav-item">
        <a class="nav-link" href="{{route('initial_setup.get')}}">
            <i class="nav-icon fa fa-cogs"></i> {{ trans('pigarden.initial_setup.title') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{backpack_url('icon')}}">
            <i class="nav-icon fa fa-picture-o"></i> {{ trans('pigarden.setup_icons.title') }}
        </a>
    </li>
@endif

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('elfinder') }}">
        <i class="nav-icon fa fa-files-o"></i> {{ trans('backpack::crud.file_manager') }}
    </a>
</li>

{{-- Users, Roles, Permissions --}}
@if(
    config('backpack.permissionmanager.allow_manage_user') ||
    backpack_user()->hasPermissionTo('manage users', backpack_guard_name())
)
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon fa fa-group"></i> {{ trans('pigarden.users_roles_permissions') }}
    </a>
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('user') }}">
                <i class="nav-icon fa fa-user"></i> {{ trans('backpack::permissionmanager.users') }}
            </a>
        </li>
        @if(
            config('backpack.permissionmanager.allow_role_create') ||
            config('backpack.permissionmanager.allow_role_update') ||
            config('backpack.permissionmanager.allow_role_delete')
        )
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('role') }}">
                <i class="nav-icon fa fa-group"></i> {{ trans('backpack::permissionmanager.roles') }}
            </a>
        </li>
        @endif
        @if(
            config('backpack.permissionmanager.allow_permission_create') ||
            config('backpack.permissionmanager.allow_permission_update') ||
            config('backpack.permissionmanager.allow_permission_delete')
        )
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('permission') }}">
                <i class="nav-icon fa fa-key"></i> {{ trans('backpack::permissionmanager.permission_plural') }}
            </a>
        </li>
        @endif
    </ul>
</li>
@endif
