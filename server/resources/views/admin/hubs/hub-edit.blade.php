@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/hubs.hub_add_title')
@else
    @lang('admin/hubs.hub_edit_title')
@endif
@endsection

@section('content')
<form id="hub_edit_form" class="container" method="POST" action="{{ route('admin.hub-edit', $item->id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.hub_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/hubs.hub_TYP')</label>
        </div>
        <div class="col-sm-4">
            @if($item->id > 0)
            <div class="form-control">{{ $item->typ }}</div>
            <input type="hidden" name="typ" value="{{ $item->typ }}">
            @else
            <select class="custom-select" name="typ">
            @foreach(\App\Models\Hub::$typs as $key => $val)
            <option value="{{ $key }}" 
                    data-description="@lang('admin/hubs.hub_types.'.$key)" 
                    {{ App\Models\Hub::isFirstSingleHub($key) ? '' : 'disabled' }}
                    {{ $item->typ == $key ? 'selected' : '' }}>{{ $key }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="offset-sm-3"></div>
        <div class="col-sm-9">
            @if($item->id == -1)
            <div id="hubTypDescription" class="alert alert-warning" style="font-size: 90%"></div>
            @else
            <div class="alert alert-warning" style="font-size: 90%">@lang('admin/hubs.hub_types.'.$item->typ)</div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/hubs.hub_NAME')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @if($item->id < 1 || $item->typ == 'din')
    <div id="rowROM" class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/hubs.hub_ROM')</label>
        </div>
        <div class="col-sm-3">
            <input type="text" class="form-control" name="rom" value="{{ $item->rom }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>    
    @endif
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.hub_COMM')</label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="comm">{{ $item->comm }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="hubDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="hubEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#hub_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
        
        $('#hub_edit_form select[name="typ"]').on('change', function () {
            if ($(this).val() == 'din') {
                $('#rowROM').show(150);
            } else {
                $('#rowROM').hide(150);
            }
            
            let description = $('#hub_edit_form option[value="' + $(this).val() + '"]').data('description');
            $('#hubTypDescription').text(description);
        }).trigger('change');
    });

    function hubEditOK() {
        $('#hub_edit_form').submit();
    }

    function hubDelete() {
        confirmYesNo("@lang('admin/hubs.hub_delete_confirm')", () => {
            $.ajax({
                url: '{{ route("admin.hub-delete", $item->id) }}',
                data: {_token: '{{ csrf_token() }}'},
                type: 'delete',
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            window.location.reload();
                        });
                    } else {

                    }
                }
            });
        });
    }

</script>
@endsection
