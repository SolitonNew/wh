@extends('index')

@section('body')
<form method="POST" action="{{ route('users') }}" class="modal-dialog">
    {{ csrf_field() }}
    <div class="modal-content">
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-label">LOGIN:</div>
                </div>
                <div class="col-sm-7">
                    <input type="text" class="form-control" name="login">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-label">PASSWORD:</div>
                </div>
                <div class="col-sm-7">
                    <input type="text" class="form-control" name="password">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">OK</button>
        </div>
    </div>
</form>
@endsection