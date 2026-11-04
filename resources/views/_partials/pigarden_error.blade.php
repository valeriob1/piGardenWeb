{{-- Friendly piGarden error box: what happened, what to check, retry,
     and the raw technical message collapsed for troubleshooting. --}}
@if(!empty($error->description))
    @php($errId = 'pigarden-error-detail-'.uniqid())
    <div class="alert alert-danger pigarden-error" role="alert">
        <div class="d-flex flex-wrap align-items-start justify-content-between">
            <div class="mr-3 mb-2">
                <h5 class="alert-heading mb-1">
                    <i class="fa {{ ($error->type ?? '') === 'connection' ? 'fa-plug' : 'fa-exclamation-triangle' }}"></i>
                    {{ $error->title ?? trans('pigarden.error_generic_title') }}
                </h5>
                <div>{{ $error->description }}</div>
            </div>
            <a href="{{ url()->current() }}" class="btn btn-light btn-sm mb-2">
                <i class="fa fa-refresh"></i> {{ trans('pigarden.error_retry') }}
            </a>
        </div>

        @if(!empty($error->hints))
            <hr class="my-2">
            <strong class="small">{{ trans('pigarden.error_what_to_check') }}:</strong>
            <ul class="small mb-1 pl-3">
                @foreach($error->hints as $hint)
                    <li>{{ $hint }}</li>
                @endforeach
            </ul>
        @endif

        @if(!empty($error->detail))
            <a class="small alert-link" data-toggle="collapse" href="#{{ $errId }}" role="button" aria-expanded="false" aria-controls="{{ $errId }}">
                <i class="fa fa-caret-right"></i> {{ trans('pigarden.error_details') }}
            </a>
            <div class="collapse" id="{{ $errId }}">
                <code class="d-block small mt-1 text-danger">{{ $error->detail }}</code>
            </div>
        @endif
    </div>
@endif
