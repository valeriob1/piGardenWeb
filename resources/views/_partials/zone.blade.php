<?php
$arr_cron_in_start = config('pigarden.cron_in.start');
$arr_cron_in_length = config('pigarden.cron_in.length');
$isOpen = $zone->state > 0;
$canControl = backpack_user()
    && backpack_user()->hasPermissionTo('start stop zones', backpack_guard_name())
    && (!isset($showButton) || $showButton);
?>
{{-- Bootstrap 4 / CoreUI card. The AdminLTE "box" markup this replaced had no
     styling left in Backpack 6, which is why zones rendered as loose elements.
     IDs and the open_in_start / open_in_set / button-zone-text hooks are kept
     because public/js/base.js drives them over AJAX. --}}
<div class="card zone-card h-100 {{ $isOpen ? 'zone-open' : '' }}" id="box-zone-{{ $zone->name }}">

    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <span class="zone-title text-truncate" title="{{ $zone->name_stripped }}">{{ $zone->name_stripped }}</span>
        <span class="badge badge-pill zone-badge {{ $isOpen ? 'badge-success' : 'badge-secondary' }}"
              id="badge-zone-{{ $zone->name }}">
            {{ $isOpen ? trans('pigarden.zone_state_open') : trans('pigarden.zone_state_closed') }}
        </span>
    </div>

    <div class="card-body text-center d-flex flex-column justify-content-between">

        <img id="btn-zone-image-{{ $zone->name }}" class="sprinkler mx-auto d-block" src="{{ $zone->imageSrc }}" alt="" />

        {{-- Scheduled start, shown when a delayed start is pending --}}
        <div class="zone-open-in small text-danger {{ empty($cron_open_in[$zone->name]) ? 'd-none' : '' }}"
             id="wrp-open-in-{{ $zone->name }}">
            <i class="fa fa-clock-o"></i>
            <span id="text-btn-zone-open-in-cancel-{{ $zone->name }}">{!! isset($cron_open_in[$zone->name]) ? $cron_open_in[$zone->name] : '' !!}</span>
        </div>

        @if ($canControl)
        <div class="btn-group btn-group-zone mt-3 w-100">
            <a class="btn btn-lg btn-zone {{ $isOpen ? 'btn-warning' : 'btn-success' }}"
               id="btn-zone-{{ $zone->name }}" href="{{ $zone->actionHref }}">
                <i class="fa {{ $zone->actionButtonClass }}"></i>
                <span class="button-zone-text">{{ $zone->actionButtonText }}</span>
            </a>
            <button type="button"
                    class="btn btn-lg dropdown-toggle dropdown-toggle-split {{ $isOpen ? 'btn-warning' : 'btn-success' }}"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    {{ $isOpen ? 'disabled' : '' }}>
                <i class="fa fa-clock-o {{ !empty($cron_open_in[$zone->name]) ? 'text-danger' : '' }}"></i>
                <span class="sr-only">Toggle Dropdown</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-right zone-dropdown">
                @foreach( $arr_cron_in_start as $cron_in_start )
                <li class="open_in_start dropdown-submenu {{ !empty($cron_open_in[$zone->name]) ? 'd-none' : '' }}">
                    <a href="#" class="dropdown-item dropdown-toggle-zone" data-toggle="dropdown">{{ trans("pigarden.cron_in.start.$cron_in_start") }}</a>
                    <ul class="dropdown-menu">
                        @foreach( $arr_cron_in_length as $cron_in_length )
                        <li>
                            <a class="dropdown-item btn-zone-open-in btn-zone-open-in-{{ $zone->name }}"
                               href="{{ route( 'zone.play_in', ['zone' => $zone->name, 'start' => $cron_in_start, 'length' => $cron_in_length ]) }}">
                                {{ trans("pigarden.cron_in.length.$cron_in_length") }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endforeach
                <li class="open_in_set {{ empty($cron_open_in[$zone->name]) ? 'd-none' : '' }}">
                    <a class="dropdown-item text-danger btn-zone-open-in-cancel btn-zone-open-in-cancel-{{ $zone->name }}"
                       href="{{ route( 'zone.play_in_cancel', ['zone' => $zone->name] ) }}">
                        <i class="fa fa-times-circle"></i> {{ trans('pigarden.cancel') }}
                    </a>
                </li>
            </ul>
        </div>
        @endif

        @if(isset($force) && $force)
        <div class="custom-control custom-checkbox mt-3 text-left d-inline-block">
            <input class="custom-control-input force_open" name="force_open_{{$zone->name}}" id="force_open_{{$zone->name}}" type="checkbox" value="1">
            <label class="custom-control-label" for="force_open_{{$zone->name}}">{{ trans('pigarden.force_open_with_rain') }}</label>
        </div>
        @endif

    </div>
</div>
