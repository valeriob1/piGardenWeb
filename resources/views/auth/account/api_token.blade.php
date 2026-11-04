@extends(backpack_view('blank'))

{{-- Same layout as Backpack 6's "My account" page (card + breadcrumbs).
     The previous AdminLTE markup (box, nav-pills nav-stacked, sidemenu with a
     gravatar) rendered as loose links run together and a broken image. --}}

@php
  $breadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      trans('backpack::base.my_account') => route('backpack.account.info'),
      trans('backpack::base.api_token') => false,
  ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('backpack::base.my_account') }}</h1>
        </div>
    </section>
@endsection

@section('content')
    <div class="row">

        @if (session('success'))
        <div class="col-lg-8">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
        @endif

        @if ($errors->count())
        <div class="col-lg-8">
            <div class="alert alert-danger">
                <ul class="mb-1">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="col-lg-8">
            <form class="form" action="{{ route('backpack.account.api_token') }}" method="post">

                {!! csrf_field() !!}

                <div class="card padding-10">

                    <div class="card-header">
                        {{ trans('backpack::base.api_token') }}
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="form-group">
                            <label>{{ trans('backpack::base.api_token') }}</label>
                            <input class="form-control" type="text" name="api_token" value="{{ $user->api_token }}" readonly="readonly">
                        </div>

                        <div class="form-group mb-0">
                            <label>{{ __('Action') }}</label>
                            <select name="action_api_token" class="form-control">
                                <option value=""></option>
                                <option value="remove_token">{{ __('Remove token') }}</option>
                                <option value="regenerate_token">{{ __('Generate new token') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> {{ trans('backpack::base.save') }}</button>
                        <a href="{{ route('backpack.account.info') }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
