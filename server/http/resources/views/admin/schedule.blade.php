@extends('admin.admin')

@section('content')
<div class="main-content-with-bar">
    <nav class="navbar">
        <button class="btn btn-primary" type="button" onclick="userAdd();">@lang('admin\users.user_add')</button>
    </nav>
</div>
@endsection