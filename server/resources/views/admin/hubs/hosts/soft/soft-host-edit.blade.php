@extends('dialog')

@section('title')
@lang('admin/hubs.host_edit_title')
@endsection

@section('content')
<form id="host_edit_form" class="container" method="POST" action="{{ route('admin.hub-softhost-edit', [$item->hub_id, $item->id]) }}">
    {{ csrf_field() }}
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
            <div class="form-control">{{ $item->hub->name }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_TYP')</label>
        </div>
        <div class="col-sm-9">
            @if($item->id == -1)
            <select id="hostTyp" name="typ" class="custom-select">
                @foreach(Lang::get('admin/softhosts') as $name => $description)
                <option value="{{ $name }}" data-description="{{ $description }}">{{ $name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
            <small id="hostTypDescription" class="text-muted"></small>
            @else
            <div class="form-control">{{ $item->typ }}</div>
            <small class="text-muted">@lang('admin/softhosts.'.$item->typ)</small>
            @endif
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
    @if($item->id == -1)
    <button type="button" class="btn btn-primary" onclick="hostEditOK();">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
    @else
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
    @endif
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
            let description = $('#hostTyp option[value="' + $(this).val() + '"]').data('description');
            $('#hostTypDescription').text(description);
        }).trigger('change');
    });
    
    function hostEditOK() {
        $('#host_edit_form').submit();
    }

    function hostDelete() {
        confirmYesNo("@lang('admin/hubs.host_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.hub-softhost-delete", [$item->hub_id, $item->id]) }}',
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
