@extends('dialog')

@section('title')
@if ($item->id == -1)
    @lang('admin/variables.variable_add_title')
@else
    @lang('admin/variables.variable_edit_title')
@endif
@endsection

@section('content')
<form id="variable_edit_form" class="container" method="POST" action="{{ route('variable-edit', $item->id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_id')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_controller')</div>
        </div>
        <div class="col-sm-6">
            <select class="custom-select" name="controller_id">
            @foreach(\App\Http\Models\ControllersModel::orderBy('name', 'asc')->get() as $row)
            <option value="{{ $row->id }}" {{ $row->id == $item->controller_id ? 'selected' : '' }}>{{ $row->name }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_typ')</div>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="typ">
                @foreach($typs as $key => $val)
                <option value="{{ $key }}" {{ $key == $item->typ ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="ow_id">
        <div class="col-sm-4">
            <div class="form-label strong">@lang('admin/variables.table_ow')</div>
        </div>
        <div class="col-sm-8">
            <select class="custom-select" name="ow_id" data-value="{{ $item->ow_id }}"></select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_readonly')</div>
        </div>
        <div class="col-sm-3">
            <select class="custom-select" name="direction">
                @foreach(Lang::get('admin/variables.table_readonly_list') as $key => $val)
                <option value="{{ $key }}" {{ $key == $item->direction ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label strong">@lang('admin/variables.table_name')</div>
        </div>
        <div class="col-sm-8">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label strong">@lang('admin/variables.table_comm')</div>
        </div>
        <div class="col-sm-8">
            <input class="form-control" type="text" name="comm" value="{{ $item->comm }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="channel">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_channel')</div>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="channel" data-value="{{ $item->channel }}"></select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_group')</div>
        </div>
        <div class="col-sm-8">
            <select class="custom-select" name="group_id">
                @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
                <option value="{{ $row->id }}" {{ $row->id == $item->group_id ? 'selected' : '' }}>{!! str_repeat('&nbsp;-&nbsp;', $row->level) !!} {{ $row->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/variables.table_app_control')</div>
        </div>
        <div class="col-sm-8">
            <select class="custom-select" name="app_control">
                @foreach(Lang::get('admin/variables.app_control') as $key => $val)
                <option value="{{ $key }}" {{ $key == $item->app_control ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="value">
        <div class="col-sm-4">
            <div class="form-label strong">@lang('admin/variables.table_value')</div>
        </div>
        <div class="col-sm-4">
            <input class="form-control" type="text" name="value" value="{{ $item->value }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="variableDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="variableEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        @if($item->id == -1)
        $('#variable_edit_form select[name="group_id"] option').removeAttr('selected');
        $('#variable_edit_form select[name="group_id"] option[value="' + currentPartID + '"]').attr('selected', 'true');
        @endif

        $('#variable_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });

        $('#variable_edit_form select[name="controller_id"]').on('change', () => {
            reloadOwList();
            reloadChannels();
        });

        $('#variable_edit_form select[name="typ"]').on('change', () => {
            reloadOwList();
            reloadChannels();
            reloadDirection();
        });

        $('#variable_edit_form select[name="ow_id"]').on('change', (e) => {
            let l = $(e.currentTarget);
            l.attr('data-value', l.val());
            reloadChannels();
        });

        $('#variable_edit_form select[name="direction"]').on('change', (e) => {
            reloadDirection();
        });

        reloadOwList(() => {
            reloadChannels(() => {
                //
            });
        });

        reloadDirection();
    });

    function reloadOwList(afterHandle = null) {
        let controller = $('#variable_edit_form select[name="controller_id"]').val();
        controller = controller ? controller : -1;
        $.ajax('{{ route("variables-ow-list", "") }}/' + controller).done((data) => {
            let rom = $('#variable_edit_form select[name="typ"]').val();
            if (rom == 'ow') {
                let owList = $('#variable_edit_form select[name="ow_id"]');
                let selValue = owList.attr('data-value');
                owList.html('');
                owList.append('<option value="">-//-</option>');
                for (let i = 0; i < data.length; i++) {
                    let sel = '';
                    let s = '[' + data[i]['num'] + '] ';
                    for (k = 1; k <= 7; k++) {
                        let h = data[i]['rom_' + k].toString(16).toUpperCase();
                        if (h.length < 2) {
                            h = '0' + h;
                        }
                        s += 'x' + h + ' ';
                    }

                    if (data[i]['id'] == selValue) {
                        sel = 'selected';
                    }

                    owList.append('<option value="' + data[i]['id'] + '" ' + sel + '>' + s + '</option>');
                }
                $('#ow_id').show(250);
            } else {
                $('#ow_id').hide(250);
            }

            if (afterHandle) {
                afterHandle();
            }
        });
    }

    function reloadChannels(afterHandle = null) {
        let rom = $('#variable_edit_form select[name="typ"]').val();
        let ow_id = $('#variable_edit_form select[name="ow_id"]').val();
        if (ow_id == null) ow_id = '';

        $.ajax('{{ route("variables-channel-list", ["", ""]) }}/' + rom + '/' + ow_id).done((data) => {
            let rom = $('#variable_edit_form select[name="typ"]').val();
            if (((rom == 'ow') && (ow_id > 0)) || (rom == 'din')) {
                let chanList = $('#variable_edit_form select[name="channel"]');
                let selValue = chanList.attr('data-value');
                chanList.html('');
                for (let i = 0; i < data.length; i++) {
                    let sel = '';
                    let s = data[i];
                    if (data[i] == selValue) {
                        sel = 'selected';
                    }
                    chanList.append('<option value="' + s + '" ' + sel + '>' + s + '</option>');
                }
                $('#channel').show(250);
            } else {
                $('#channel').hide(250);
            }

            if (afterHandle) {
                afterHandle();
            }
        });
    }

    function reloadDirection() {
        let l = $('#variable_edit_form select[name="direction"]').val();
        let rom = $('#variable_edit_form select[name="typ"]').val();

        if ((rom == 'variable') || (l == '1')) {
            $('#value').show(250);
        } else {
            $('#value').hide(250);
        }
    }

    function variableEditOK() {
        $('#variable_edit_form').submit();
    }

    function variableDelete() {
        confirmYesNo("@lang('admin/variables.variable_delete_confirm')", () => {
            $.ajax('{{ route("variable-delete", $item->id) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {

                }
            });
        });
    }

</script>
@endsection
