@extends('admin.admin')

@section('content')
<div class="main-content-with-bar">
    <nav class="navbar">
        <button class="btn btn-primary" type="button" onclick="userAdd();">@lang('admin\users.user_add')</button>
    </nav>
    <div class="main-content-with-bar-container">
        <div style="display: flex; flex-direction: row; flex-grow: 1;max-height: 100%;">
            <div style="width: 200px;min-width:200px; overflow: auto;">
                @for($i = 0; $i < 100; $i++)
                <div>ZZZZZZ</div>
                @endfor
            </div>
            <div class="content-body">
                <table id="variable_table" class="table table-sm table-hover table-bordered table-fixed-header">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 60px;"><span>@lang('admin\variables.table_id')</span></th>
                            <th scope="col" style="width: 150px;"><span>@lang('admin\variables.table_controller')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_typ')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_readonly')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_name')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_comm')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_app_control')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_value')</span></th>
                            <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_channel')</span></th>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
</script>
@endsection