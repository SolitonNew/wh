@extends('index')

@section('body')
<div class="content">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('loginPost') }}">
            {{ csrf_field() }}
            <div class="modal-header">
                <h5 class="modal-title">@Lang('auth.login_title')</h5>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-label">@lang('auth.login_login')</div>
                        </div>
                        <div class="col-sm-7">
                            <input class="form-control" type="text" name="login" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-label">@lang('auth.login_password')</div>
                        </div>
                        <div class="col-sm-7">
                            <input class="form-control" type="password" name="password" value="">
                        </div>
                    </div>
                    @if($errors->has('login'))
                    <div class="row">
                        <div class="offset-sm-3 col-sm-7">
                            <div class="error">{{ $errors->first('login') }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if(request()->method() == 'POST')
                    <div>{{ dd($errors) }}</div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('dialogs.btn_ok')</button>
            </div>
        </form>
    </div>
</div>
@endsection()