@extends(backpack_view('blank'))

@section('body_class', 'dashboard')

{{-- Bootstrap 4 / CoreUI markup. The previous version used AdminLTE 2 + BS3
     classes (box, col-xs-*, pull-*, hide) which no longer exist in Backpack 6,
     so every panel rendered as unstyled loose content. --}}

@section('content')

    @if(!empty($error->description))
        <div class="alert alert-danger">
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> piGarden</h5>
            <div class="small mb-0">{{ $error->description }}</div>
        </div>
    @endif

    {{-- Toolbar: clock on the left, global actions on the right --}}
    @if(!empty($zones) && $zones->count() > 0)
    <div class="card mb-3">
        <div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between">

            <div class="wrp-pigarden-date-time mr-2 my-1">
                <i class="fa fa-clock-o"></i> <span id="pigarden-date-time">{{$date_time}}</span>
            </div>

            <div class="dashboard-actions d-flex flex-wrap my-1">
                @if(backpack_user()->hasPermissionTo('manage cron zones', backpack_guard_name()))
                <a class="btn btn-success mr-2 mb-1" href="{{ route('zone.all_enable_cron') }}"
                   onclick="return confirm('{{ trans('pigarden.confirm') }}')"
                   title="{{ trans('pigarden.irrigation_enable_all_schduling') }}">
                    <i class="fa fa-clock-o"></i>
                    <span class="d-none d-lg-inline">&nbsp;{{ trans('pigarden.irrigation_enable_all_schduling') }}</span>
                </a>
                @endif

                @if(backpack_user()->hasPermissionTo('start stop zones', backpack_guard_name()))
                <div class="btn-group mr-2 mb-1">
                    <a class="btn btn-warning" href="{{ route('zone.all_stop') }}"
                       onclick="return confirm('{{ trans('pigarden.confirm') }}')"
                       title="{{ trans('pigarden.irrigation_stop_all') }}">
                        <i class="fa fa-stop"></i>
                        <span class="d-none d-lg-inline">&nbsp;{{ trans('pigarden.irrigation_stop_all') }}</span>
                    </a>
                    <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-close-all">
                        <a class="dropdown-item" href="{{ route('zone.all_stop') }}" onclick="return confirm('{{ trans('pigarden.confirm') }}')">
                            <i class="fa fa-stop"></i> {{ trans('pigarden.irrigation_stop_all') }}
                        </a>
                        @if(backpack_user()->hasPermissionTo('manage cron zones', backpack_guard_name()))
                        <a class="dropdown-item" href="{{ route('zone.all_stop', ['disable_scheduling' => 'disable_scheduling']) }}" onclick="return confirm('{{ trans('pigarden.confirm') }}')">
                            <i class="fa fa-clock-o"></i> {{ trans('pigarden.irrigation_stop_all_and_disable_scheduled') }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if(backpack_user()->hasPermissionTo('shutdown restart', backpack_guard_name()))
                <div class="btn-group mb-1">
                    <a class="btn btn-danger" href="{{ route('reboot') }}"
                       onclick="return confirm('{{ trans('pigarden.confirm') }}')"
                       title="{{ trans('pigarden.system_reboot') }}">
                        <i class="fa fa-power-off"></i>
                        <span class="d-none d-lg-inline">&nbsp;{{ trans('pigarden.system_reboot') }}</span>
                    </a>
                    <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-reboot">
                        <a class="dropdown-item" href="{{ route('reboot') }}" onclick="return confirm('{{ trans('pigarden.confirm') }}')">
                            <i class="fa fa-refresh"></i> {{ trans('pigarden.system_reboot') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('poweroff') }}" onclick="return confirm('{{ trans('pigarden.confirm') }}')">
                            <i class="fa fa-power-off"></i> {{ trans('pigarden.system_shutdown') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
    @endif

    {{-- Zones --}}
    @if(!empty($zones) && $zones->count() > 0)
        <div class="row">
            @foreach($zones as $id => $zone)
                <div class="col-12 col-sm-6 col-lg-4 mb-3">
                    @include('_partials.zone', ['zone' => $zone])
                </div>
            @endforeach
        </div>
    @endif

    {{-- Rain + weather --}}
    <div class="row">
        <div class="col-12 {{ !empty($weather) ? 'col-lg-4' : 'col-lg-12' }} mb-3">
            <div class="card h-100">
                <div class="card-header py-2"><i class="fa fa-tint text-info"></i> {{ trans('pigarden.last_rain') }}</div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">{{ trans('pigarden.last_rain_sensor') }}</span>
                        <strong id="last_rain_sensor">{{$last_rain_sensor}}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">{{ trans('pigarden.last_rain_online') }}</span>
                        <strong id="last_rain_online">{{$last_rain_online}}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($weather))
        <div class="col-12 col-lg-8 mb-3">
            <div class="card h-100">
                <div class="card-header py-2">
                    <i class="fa fa-cloud text-info"></i>
                    {{trans('pigarden.weather_conditions')}}
                    <span class="text-muted small">(<span id="observation_time">{{$weather->observation_time}}</span>)</span>
                </div>
                <div class="card-body py-3">
                    <div class="row align-items-center text-center">

                        {{-- Current conditions --}}
                        <div class="col-12 col-md-5 mb-3 mb-md-0 d-flex align-items-center justify-content-center">
                            <img id="icon_url" src="{{ $weather->icon_url }}" alt="{{ $weather->weather }}" class="mr-2" />
                            <div class="text-left weather-text">
                                @if(!empty($weather->weather))
                                    <div><strong><span id="weather">{{ $weather->weather }}</span></strong></div>
                                @endif
                                <div class="h4 mb-0"><span id="temp_c">{{ $weather->temp_c }}</span> C°</div>
                                <div class="small text-muted">
                                    {{ trans('pigarden.feelslike_c') }} <span id="feelslike_c">{{ $weather->feelslike_c }}</span> C°
                                </div>
                            </div>
                        </div>

                        {{-- Wind --}}
                        <div class="col-6 col-md-3 d-flex align-items-center justify-content-center">
                            <div id="curWind" class="mr-2">
                                <div id="windCompassContainer">
                                    <div id="windCompass" class="wx-data" style="{!! $weather->wind_degress_style !!}">
                                        <div class="dial"><div class="arrow-direction"></div></div>
                                    </div>
                                    <div id="windN">N</div>
                                    <div id="windCompassSpeed" class="wx-data">
                                        <span class="wx-value" id="wind_kph">{{$weather->wind_kph}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left small weather-text">
                                <div><strong><span id='wind_dir'>{{ $weather->wind_dir }}</span></strong></div>
                                <div class="text-muted">{{ trans('pigarden.wind_gust_kph') }} <span id='wind_gust_kph'>{{$weather->wind_gust_kph}}</span> km/h</div>
                            </div>
                        </div>

                        {{-- Pressure / humidity / dewpoint --}}
                        <div class="col-6 col-md-4 text-left small weather-text">
                            <div>{{ trans('pigarden.pressure_mb') }} <strong><span id='pressure_mb'>{{ $weather->pressure_mb }}</span></strong> hPa</div>
                            <div>{{ trans('pigarden.relative_humidity') }} <strong><span id='relative_humidity'>{{ $weather->relative_humidity }}</span></strong></div>
                            <div>{{ trans('pigarden.dewpoint_c') }} <strong><span id='dewpoint_c'>{{ $weather->dewpoint_c }}</span></strong> C°</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sensors --}}
    <div class="row" id="vue-sensor" v-if="sensor">
        <div class="col-12 mb-3" v-for="(sensor_item, sensor_name) in sensor">
            <div class="card">
                <div class="card-header py-2">
                    <i class="fa fa-leaf text-success"></i>
                    {{trans('pigarden.sensor')}}: <strong>@{{ sensor_name.replace('_', ' ') }}</strong>
                </div>
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 sensor-item">
                            <i class="fa fa-tint sensor-icon text-primary"></i>
                            <div class="small text-muted">{{ trans('pigarden.moisture') }}</div>
                            <div class="h5 mb-0">@{{ sensor_item.moisture }} %</div>
                        </div>
                        <div class="col-6 col-md-3 sensor-item">
                            <i class="fa fa-thermometer-half sensor-icon text-danger"></i>
                            <div class="small text-muted">{{ trans('pigarden.temp_c') }}</div>
                            <div class="h5 mb-0">@{{ sensor_item.temperature }} C°</div>
                        </div>
                        <div class="col-6 col-md-3 sensor-item">
                            <i class="fa fa-pagelines sensor-icon text-success"></i>
                            <div class="small text-muted">{{ trans('pigarden.fertility') }}</div>
                            <div class="h5 mb-0">@{{ sensor_item.fertility }} us/cm</div>
                        </div>
                        <div class="col-6 col-md-3 sensor-item">
                            <i class="fa fa-sun-o sensor-icon text-warning"></i>
                            <div class="small text-muted">{{ trans('pigarden.illuminance') }}</div>
                            <div class="h5 mb-0">@{{ sensor_item.illuminance }} lx</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('after_scripts')
    <script src="{{ asset('js/vue2.js') }}"></script>
    {{-- pigardenZoneStateOpen/Closed come from the header (see header_metas) --}}
    <script src="{{ asset('js/base.js') }}"></script>
    <script>
        var urlJsonDashboardStatus = "{{ route('get.json.dashboard.status') }}";
        var timeoutJsonDashboardStatus = {{ config('pigarden.timeout_json_dashboard_status') }};
    </script>
    <script src="{{ asset('js/backend.js') }}"></script>
    <script>

        $(document).ready(function(){
            $('.btn-zone, .btn-zone-open-in, .btn-zone-open-in-cancel').click(function(e){
                var btn = $(this);
                var id;

                if(btn.hasClass('btn-zone-open-in') || btn.hasClass('btn-zone-open-in-cancel')){
                    id = $(btn.parents('.btn-group-zone').find('.btn-zone')).prop('id').replace('btn-zone-', '');
                } else {
                    id = $(btn).prop('id').replace('btn-zone-', '');
                }

                $.ajax({
                    type : "GET",
                    url : btn.attr('href'),
                    dataType: 'json',
                    beforeSend: function(jqXHR) {
                        $('#box-zone-'+id).append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                    },
                    success: function (data, textStatus, jqXHR) {
                        updateZones(data);
                        updateNotify(data);
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        callBackAjaxError(jqXHR, textStatus, errorThrown);
                    },
                    complete: function(jqXHR, textStatus){
                        $('#box-zone-'+id+' .overlay').remove();
                    }
                });

                e.preventDefault();
            });
        });

    </script>
@endsection
