@extends('dialog')

@section('title')
    @lang('admin/settings.check_update_title')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-control" style="height: auto; white-space: pre; overflow-x: auto;">
                    {!! $response !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('buttons')
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection
