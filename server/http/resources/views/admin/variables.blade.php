@extends('admin.admin')

@section('content')
<nav class="navbar navbar-light bg-light">
    <button class="btn btn-primary" type="button" onclick="userAdd();">@lang('admin\users.user_add')</button>
</nav>
<div class="container-fluid" style="overflow: auto;">
    <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_id')</th>
                    <th scope="col" style="width: 130px;">@lang('admin\variables.table_controller')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_typ')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_readonly')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_name')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_comm')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_app_control')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_value')</th>
                    <th scope="col" style="width: 50px;">@lang('admin\variables.table_channel')</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr id="row_{{ $row->ID }}">
                    <th scope="row">{{ $row->ID }}</th>
                    <td>{{ $row->CONTROLLER_NAME }}</td>
                    <td>{{ $row->TYP_NAME }}</td>
                    <td>{{ $row->DIRECTION }}</td>
                    <td>{{ $row->NAME }}</td>
                    <td>{{ $row->COMM }}</td>
                    <td>{{ $row->APP_CONTROL }}</td>
                    <td>{{ $row->VALUE }}</td>
                    <td>{{ $row->CHANNEL }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary" onclick="userEdit({{ $row->ID }}); return false;">@lang('admin\variables.variable_edit')</a>
                        <a href="#" class="btn btn-sm btn-danger" onclick="userDelete({{ $row->ID }}); return false;">@lang('admin\variables.variable_delete')</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection