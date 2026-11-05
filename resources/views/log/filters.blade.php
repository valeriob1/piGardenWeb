{{-- Log filters (replacement for Backpack Pro's list filters).
     A plain GET form: the values end up in the page query string, which the
     datatable forwards to its search request (see LogCrudController). --}}
@php
    $f = $widget['filters'];
    $active = collect($f)->filter(fn ($v) => $v !== '')->count();
@endphp
<div class="card mb-3 log-filters">
    <div class="card-body py-2">
        {{-- empty fields are disabled on submit so they don't clutter the URL --}}
        <form method="GET" action="{{ url()->current() }}" class="form-row align-items-end"
              onsubmit="Array.prototype.forEach.call(this.elements, function (el) { if (el.name && el.value === '') { el.disabled = true; } });">
            <div class="col-6 col-md-3 col-lg-2 form-group mb-2">
                <label class="small mb-1" for="filter_type">{{ __('Type') }}</label>
                <select class="form-control form-control-sm" id="filter_type" name="filter_type">
                    <option value="">{{ trans('pigarden.log.filter_all') }}</option>
                    @foreach($widget['types'] as $value)
                        <option value="{{ $value }}" @selected($f['type'] === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 form-group mb-2">
                <label class="small mb-1" for="filter_level">{{ __('Level') }}</label>
                <select class="form-control form-control-sm" id="filter_level" name="filter_level">
                    <option value="">{{ trans('pigarden.log.filter_all') }}</option>
                    @foreach($widget['levels'] as $value)
                        <option value="{{ $value }}" @selected($f['level'] === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 form-group mb-2">
                <label class="small mb-1" for="filter_from">{{ trans('pigarden.log.filter_from') }}</label>
                <input type="date" class="form-control form-control-sm" id="filter_from" name="filter_from" value="{{ $f['from'] }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2 form-group mb-2">
                <label class="small mb-1" for="filter_to">{{ trans('pigarden.log.filter_to') }}</label>
                <input type="date" class="form-control form-control-sm" id="filter_to" name="filter_to" value="{{ $f['to'] }}">
            </div>
            <div class="col-12 col-lg-4 form-group mb-2 d-flex flex-wrap align-items-center">
                <button type="submit" class="btn btn-sm btn-primary mr-2">
                    <i class="fa fa-filter"></i> {{ trans('pigarden.log.filter_apply') }}
                </button>
                @if($active > 0)
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-link px-1">
                        <i class="fa fa-times"></i> {{ trans('pigarden.log.filter_reset') }}
                    </a>
                    <span class="badge badge-info ml-1">{{ trans_choice('pigarden.log.filter_active', $active, ['count' => $active]) }}</span>
                @endif
            </div>
        </form>
    </div>
</div>
