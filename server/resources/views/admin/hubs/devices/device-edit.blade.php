@extends('dialog')

@section('title')
@if ($item->id == -1)
    @lang('admin/hubs.device_add_title')
@else
    @lang('admin/hubs.device_edit_title')
@endif
@endsection

@section('content')
<form id="device_edit_form" class="container" method="POST" action="{{ route('admin.hub-device-edit', [$item->controller_id, $item->id]) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_APP_CONTROL')</label>
        </div>
        <div class="col-sm-8">
            <select class="custom-select" name="app_control">
                @foreach(Lang::get('admin/hubs.app_control') as $key => $val)
                <option value="{{ $key }}" {{ $key == $item->app_control ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_CONTROLLER')</label>
        </div>
        <div class="col-sm-6">
            <select class="custom-select" name="controller_id">
            @foreach(\App\Http\Models\ControllersModel::orderBy('name', 'asc')->get() as $row)
            <option value="{{ $row->id }}" 
                    {{ $row->id == $item->controller_id ? 'selected' : '' }}
                    data-typs="{{ implode('|', \App\Http\Models\ControllersModel::$typs[$row->typ]) }}">{{ $row->name }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_TYP')</label>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="typ" data-val="{{ $item->typ }}"></select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="ow_id">
        <div class="col-sm-4">
            <label class="form-label strong">@lang('admin/hubs.device_OW')</label>
        </div>
        <div class="col-sm-8">
            <select class="custom-select" name="ow_id" data-value="{{ $item->ow_id }}"></select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label strong">@lang('admin/hubs.device_NAME')</label>
        </div>
        <div class="col-sm-8">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_COMM')</label>
        </div>
        <div class="col-sm-8">
            <input class="form-control" type="text" name="comm" value="{{ $item->comm }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="channel">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_CHANNEL')</label>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="channel" data-value="{{ $item->channel }}"></select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_GROUP')</label>
        </div>
        <div class="col-sm-8">
            <div class="form-control">{{ $groupPath }}</div>
        </div>
    </div>
    <div class="row" id="value">
        <div class="col-sm-4">
            <label class="form-label">@lang('admin/hubs.device_VALUE')</label>
        </div>
        <div class="col-sm-4">
            <input class="form-control" type="number" step="0.01" name="value" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="deviceDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="deviceEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        @if($item->id == -1)
        //$('#device_edit_form select[name="group_id"] option').removeAttr('selected');
        //$('#device_edit_form select[name="group_id"] option[value="' + currentPartID + '"]').attr('selected', 'true');
        @endif

        $('#device_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });

        $('#device_edit_form select[name="controller_id"]').on('change', () => {
            reloadTyps();
            reloadOwList();
            reloadChannels();
        });

        $('#device_edit_form select[name="typ"]').on('change', () => {
            reloadOwList();
            reloadChannels();
        });

        $('#device_edit_form select[name="ow_id"]').on('change', (e) => {
            let l = $(e.currentTarget);
            l.attr('data-value', l.val());
            reloadChannels();
        });
        
        reloadTyps();

        reloadOwList(() => {
            reloadChannels(() => {
                //
            });
        });
    });
    
    function reloadTyps() {
        let controller = $('#device_edit_form select[name="controller_id"]').val();
        let typs = $('#device_edit_form select[name="controller_id"] option[value="' + controller + '"]').data('typs').split('|');
        
        let typSelect = $('#device_edit_form select[name="typ"]');
        let currTyp = typSelect.data('val') ? typSelect.data('val') : typSelect.val();
        typSelect.data('val', null);
        typSelect.html('');
        
        let a = new Array();
        for (let i = 0; i < typs.length; i++) {
            a.push('<option val="' + typs[i] + '">' + typs[i] + '</option>');
        }
        typSelect.html(a.join(''));
        
        if (typs.indexOf(currTyp) > -1) {
            typSelect.val(currTyp);
        } else {
            typSelect.val(typs[0]);
        }
        
        typSelect.trigger('change');
    }

    function reloadOwList(afterHandle = null) {
        let controller = $('#device_edit_form select[name="controller_id"]').val();
        controller = controller ? controller : -1;
        $.ajax('{{ route("admin.hub-device-host-list", "") }}/' + controller).done((data) => {
            let rom = $('#device_edit_form select[name="typ"]').val();
            if (rom == 'ow') {
                let owList = $('#device_edit_form select[name="ow_id"]');
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
        let typ = $('#device_edit_form select[name="typ"]').val();
        let ow_id = $('#device_edit_form select[name="ow_id"]').val();
        if (ow_id == null) ow_id = '';

        $.ajax('{{ route("admin.hub-device-host-channel-list", ["", ""]) }}/' + typ + '/' + ow_id).done((data) => {
            let rom = $('#device_edit_form select[name="typ"]').val();
            if (((rom == 'ow') && (ow_id > 0)) || (rom == 'din')) {
                let chanList = $('#device_edit_form select[name="channel"]');
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

    function deviceEditOK() {
        $('#device_edit_form').submit();
    }

    function deviceDelete() {
        confirmYesNo("@lang('admin/hubs.device_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.hub-device-delete", $item->id) }}',
                data: {_token: '{{ csrf_token() }}'},
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            window.location.reload();
                        });
                    } else {

                    }
                },
            });
        });
    }

</script>
@endsection
