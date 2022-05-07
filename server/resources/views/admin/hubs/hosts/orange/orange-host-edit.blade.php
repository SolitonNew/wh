@extends('dialog')

@section('title')
@if($item->id > -1)
@lang('admin/hubs.host_edit_title')
@else
@lang('admin/hubs.host_add_title')
@endif
@endsection

@section('content')
<form id="host_edit_form" class="container" method="POST" action="{{ route('admin.hub-orangehost-edit', ['hubID' => $item->hub_id, 'id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
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
        <div class="col-sm-6">
            @if($item->id > 0)
            <div class="form-control">{{ $item->hub->name }}</div>
            @else
            <div class="form-control">{{ App\Models\Hub::find($item->hub_id)->name }}</div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_TYP')</label>
        </div>
        <div class="col-sm-9">
            <select id="hostTyp" name="typ" class="custom-select">
                @foreach($item->typeList() as $type)
                <option value="{{ $type->name }}" 
                        data-description="{{ $type->description }}"
                        data-channels="{{ $type->channels }}"
                        data-address="{{ $type->address }}"
                        {{ $item->typ == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div id="lastFormRow" class="row">
        <div class="offset-sm-3 col-sm-9">
            <div id="hostTypDescription" class="alert alert-warning" style="font-size: 90%"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_ADDRESS')</label>
        </div>
        <div class="col-sm-3">
            <select id="hostAddress" name="address" class="custom-select">
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @if($item->id > -1)
    <div class="form-group">
        <label class="">@lang('admin/hubs.host_DEVICES') ({{ count($item->devices) }}):</label>
        <div class="form-control" style="height: auto;">
        @forelse($item->devices as $v)
        <div>[{{ $v->channel }}] {{ $v->name }}</div>
        @empty
        -//-
        @endforelse
        </div>
    </div>
    @endif
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
                    window.location.reload();
                });
            } else {
                console.log(data);
                dialogShowErrors(data);
            }
        });
        
        $('#hostTyp').on('change', function () {
            let option = $('#hostTyp option[value="' + $(this).val() +'"]');
            
            $('#hostTypDescription').html(option.data('description'));
            
            $('#hostAddress').html('');
            let ls = option.data('address').split(';');
            let a = new Array();
            ls.forEach(function (item) {
                a.push('<option value="' + item + '">0x' + parseInt(item).toString(16) + '</option>');
            });
            $('#hostAddress').append(a.join(''));
            @if($item->address)
            $('#hostAddress').val('{{ $item->address }}');
            @endif
        }).trigger('change');
    });
    
    function hostEditOK() {
        $('#host_edit_form').submit();
    }

    function hostDelete() {
        confirmYesNo("@lang('admin/hubs.host_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.hub-orangehost-delete", ["hubID" => $item->hub_id, "id" => $item->id]) }}',
                data: {
                    
                },
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            window.location.reload();
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

</script>
@endsection
