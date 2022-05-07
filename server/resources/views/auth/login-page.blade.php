@extends('index')

@section('body')
<div class="content">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('loginPost') }}">
            <button type="submit" style="display: none;"></button>
            <div class="modal-header">
                <h5 class="modal-title">@Lang('auth.login_title')</h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    @if(Auth::user()->access >= 1)
                    <div class="row">
                        <div class="offset-sm-2 col-sm-8">
                            <a href="{{ route('home') }}" class="btn btn-primary" style="width: 100%;">@lang('auth.pages.1')</a>
                        </div>
                    </div>
                    @endif
                    @if(Auth::user()->access >= 2)
                    <div class="row">
                        <div class="offset-sm-2 col-sm-8">
                            <a href="{{ route('admin') }}" class="btn btn-primary" style="width: 100%;">@lang('auth.pages.2')</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection()