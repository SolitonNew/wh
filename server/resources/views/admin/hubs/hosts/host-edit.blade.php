@extends('dialog')

@section('title')
@if($item->id > -1)
    @lang('admin/hubs.host_edit_title')
@else
    @lang('admin/hubs.host_add_title')
@endif
@endsection

@section('content')
<style>
    .ow_rom {
        position: relative;
        display: inline-block;
        margin: 0px 2px;
    }

    .ow_rom .invalid-feedback {
        display: none!important;
    }

    .ow_rom .form-control.is-invalid {
        background-image: none!important;
        padding: .375rem .75rem!important;
    }

</style>
<form id="host_edit_form" method="POST" action="">
    <button type="submit" style="display: none;"></button>
    <input type="hidden" name="group">
    <input type="hidden" name="typ">
    <div class="container">
        @if($item->id > 0)
        <div class="row">
            <div class="col-sm-3">
                <label class="form-label">@lang('admin/hubs.host_ID')</label>
            </div>
            <div class="col-sm-3">
                <div class="form-control">{{ $item->id }}</div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-sm-3">
                <label class="form-label">@lang('admin/hubs.host_CONTROLLER')</label>
            </div>
            <div class="col-sm-9">
                <div class="form-control">{{ $hub->name }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <label class="form-label">@lang('admin/hubs.host_TYP')</label>
            </div>
            <div class="col-sm-9">
                <select id="hostTyp" class="custom-select" {{ $item->id > 0 ? 'disabled' : '' }}>
                @foreach($hostTypList as $typ)
                    <option value="{{ $typ->group }}|{{ $typ->hostTypID }}"
                            data-channels="{{ $typ->channels }}"
                            data-description="{{ $typ->description }}"
                            data-data="{{ $typ->data }}"
                            {{ $typ->group == $group && $typ->hostTypID == $hostTypID ? 'selected' : '' }}>{{ $typ->title }}</option>
                @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="offset-sm-3 col-sm-9">
                <div id="hostTypDescription" class="alert alert alert-warning"></div>
            </div>
        </div>
        <div id="host_edit_form_nameRow" class="row" style="display: none;">
            <div class="col-sm-3">
                <label class="form-label">@lang('admin/hubs.host_NAME')</label>
            </div>
            <div class="col-sm-9">
                <input class="form-control" name="name" value="{{ $item->name ?? '' }}">
            </div>
        </div>
    </div>
    <div class="container host_panel" data-typ="ow" style="display: none;">
        <div class="form-group">
            <label class="strong">@lang('admin/hubs.host_ROM'):</label>
            <div class="d-flex justify-content-between">
                <div class="ow_rom">
                    <div id="ow_rom_1_label" class="ow_rom form-control"></div>
                    <input type="hidden" name="rom_1">
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_2" maxlength="2" value="{{ dechex($item->rom_2 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_3" maxlength="2" value="{{ dechex($item->rom_3 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_4" maxlength="2" value="{{ dechex($item->rom_4 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_5" maxlength="2" value="{{ dechex($item->rom_5 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_6" maxlength="2" value="{{ dechex($item->rom_6 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <input class="form-control" name="rom_7" maxlength="2" value="{{ dechex($item->rom_7 ?? 0) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="ow_rom">
                    <div id="ow_rom_8_label" class="form-control">00</div>
                    <input type="hidden" name="rom_8">
                </div>
            </div>
        </div>
    </div>
    <div class="container host_panel" data-typ="i2c" style="display: none;">
        <div class="row">
            <div class="col-sm-3">
                <label class="form-label strong">@lang('admin/hubs.host_ADDRESS')</label>
            </div>
            <div class="col-sm-3">
                <select name="address" class="custom-select"></select>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
    <div class="container host_panel" data-typ="extapi" style="display: none;">
    </div>
    <div class="container host_panel" data-typ="camcorder" style="display: none;">
    </div>
    <div class="container">
        <div class="row form-group">
            <div class="col-sm-12">
                <label>@lang('admin/hubs.host_COMM'):</label>
                <textarea class="form-control" name="comm" rows="2">{!! $item->comm !!}</textarea>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-12">
                <label class="">@lang('admin/hubs.host_CHANNELS'):</label>
                <div id="hostChannels" class="form-control"></div>
            </div>
        </div>
        @if($item->id > 0)
        <div class="row form-group">
            <div class="col-sm-12">
                <label class="">@lang('admin/hubs.host_DEVICES'):</label>
                <div id="host_edit_form_devices" class="form-control" style="height: auto;">
                @forelse($item->devices as $v)
                    <div>[{{ $v->channel }}] {{ $v->name }}</div>
                @empty
                    -//-
                @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
        <button type="button" class="btn btn-danger" onclick="hostDelete()">@lang('dialogs.btn_delete')</button>
    @endif
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-primary" onclick="hostEditOK();">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection
@section('script')
    <script>
        $(document).ready(() => {
            $('#host_edit_form').ajaxForm((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        reloadWithWaiter();
                    });
                } else {
                    console.log(data);
                    dialogShowErrors(data);
                }
            });

            $('#hostTyp').on('change', function () {
                let val = $(this).val();
                let a = val.split('|');
                $('#host_edit_form [name="group"]').val(a[0]);
                $('#host_edit_form [name="typ"]').val(a[1]);

                let url = '{{ route('admin.hub-host-edit', ['hubID' => $item->hub_id, 'group' => '', 'id' => '']) }}/' + a[0] + '/{{ $item->id }}';
                $('#host_edit_form').attr('action', url);

                $('#host_edit_form .host_panel').hide();
                $('#host_edit_form_nameRow').hide();

                let option = $('option[value="' + val + '"]');
                $('#hostTypDescription').text(option.data('description'));
                $('#hostChannels').text(option.data('channels'));

                switch (a[0]) {
                    case 'ow':
                        hostEditFormPageOw(a[1], option.data('data'));
                        break;
                    case 'i2c':
                        hostEditFormPageI2c(a[1], option.data('data'));
                        break;
                    case 'extapi':
                        hostEditFormPageExtApi(a[1], option.data('data'));
                        break;
                    case 'camcorder':
                        hostEditFormPageCamcorder(a[1], option.data('data'));
                        break;
                }
                $('#host_edit_form .host_panel[data-typ="' + a[0] + '"]').show();
            }).trigger('change');

            // OW Inputs ----------------------
            $('#host_edit_form .ow_rom input').on('input', function (e) {
                let a = $(this).val().toUpperCase().match(/[A-F0-9]/g);
                let val = a ? a.join('') : '';
                $(this).val(val);
                hostMakeOwCRC();
            }).trigger('input');

            $('#host_edit_form .ow_rom input').on('keyup', function (e) {
                let val = $(this).val();
                if (e.key.length == 1) {
                    if (val.length == 2) {
                        let next = $(this).parent().next();
                        $('input', next).focus().select();
                    }
                } else
                if (e.key == 'Backspace') {
                    if (val.length == 0) {
                        let prev = $(this).parent().prev();
                        $('input', prev).focus().select();
                    }
                }
            });
            // --------------------------------
        });

        function hostEditFormPageOw(key, data) {
            let h = parseInt(key).toString(16).toUpperCase();
            $('#host_edit_form [name="rom_1"]').val(h);
            $('#ow_rom_1_label').text(h);
            $('#host_edit_form .ow_rom input').trigger('input');
        }

        function hostEditFormPageI2c(key, data) {
            let select = $('#host_edit_form .host_panel[data-typ="i2c"] [name="address"]');
            select.html('');
            data.forEach(function (item) {
                let option = $('<option value="' + item + '">x' + item.toString(16) + '</option>');
                select.append(option);
            });
            @if($group == 'i2c')
            select.val('{{ $item->address }}');
            @endif
        }

        function hostEditFormPageExtApi(key, data) {
            $('#host_edit_form [data-typ="extapi"]').html('');
            let i = 0;
            for (field in data) {
                let input = '';
                switch (data[field].size) {
                    case 'small':
                        input = '<input class="form-control" name="extapi_' + data[field].key + '" value="">';
                        break;
                    case 'large':
                        input = '<textarea class="form-control" name="extapi_' + data[field].key + '" rows="3"></textarea>';
                        break;
                }

                let html = '<div class="row property">' +
                    '    <div class="col-sm-3">' +
                    '        <label class="form-label">' + data[field].title + '</label>' +
                    '    </div>' +
                    '    <div class="col-sm-9">' +
                    input +
                    '    </div>' +
                    '</div>';

                $('#host_edit_form [data-typ="extapi"]').append(html);
                i++;
            }
            @if($group == 'extapi')
            @foreach(json_decode($item->data) as $field => $value)
            $('#host_edit_form [name="extapi_{{ $field }}"]').val('{!! str_replace("\n", '\n', addslashes($value)) !!}');
            @endforeach
            @endif
        }

        function hostEditFormPageCamcorder(key, data) {
            $('#host_edit_form_nameRow').show();
            $('#host_edit_form [data-typ="camcorder"]').html('');
            let i = 0;
            for (field in data) {
                let input = '';
                switch (data[field].size) {
                    case 'small':
                        input = '<input class="form-control" name="camcorder_' + data[field].key + '" value="">';
                        break;
                    case 'large':
                        input = '<textarea class="form-control" name="camcorder_' + data[field].key + '" rows="3"></textarea>';
                        break;
                }

                let html = '<div class="row property">' +
                    '    <div class="col-sm-3">' +
                    '        <label class="form-label">' + data[field].title + '</label>' +
                    '    </div>' +
                    '    <div class="col-sm-9">' +
                    input +
                    '    </div>' +
                    '</div>';

                $('#host_edit_form [data-typ="camcorder"]').append(html);
                i++;
            }
            @if($group == 'camcorder')
            @foreach(json_decode($item->data) as $field => $value)
            $('#host_edit_form [name="camcorder_{{ $field }}"]').val('{!! str_replace("\n", '\n', addslashes($value)) !!}');
            @endforeach
            @endif
        }

        function hostEditOK() {
            $('#host_edit_form').submit();
        }

        function hostDelete() {
            confirmYesNo("@lang('admin/hubs.host_delete_confirm')", () => {
                $.ajax({
                    type: 'delete',
                    url: '{{ route("admin.hub-host-delete", ["hubID" => $item->hub_id, "group" => $group, "id" => $item->id]) }}',
                    data: {

                    },
                    success: function (data) {
                        if (data == 'OK') {
                            dialogHide(() => {
                                reloadWithWaiter();
                            });
                        } else {
                            if (data.errors) {
                                alert(data.errors.join(', '));
                            }
                        }
                    },
                });
            });
        }

        function hostMakeOwCRC() {
            function table(data) {
                let crc = 0x0;
                let fb_bit = 0;
                for (let b = 0; b < 8; b++) {
                    fb_bit = (crc ^ data) & 0x01;
                    if (fb_bit == 0x01) {
                        crc = crc ^ 0x18;
                    }
                    crc = (crc >> 1) & 0x7F;
                    if (fb_bit == 0x01) {
                        crc = crc | 0x80;
                    }
                    data >>= 1;
                }
                return crc;
            }

            let crc = 0;
            let ls = $('#host_edit_form .ow_rom input');
            for (let i = 0; i < 7; i++) {
                let v = parseInt($(ls[i]).val(), 16);
                crc = table(crc ^ v);
            }
            let h = parseInt(crc).toString(16).toUpperCase();
            $('#host_edit_form [name="rom_8"]').val(h);
            $('#ow_rom_8_label').text(h);
        }
    </script>
@endsection
