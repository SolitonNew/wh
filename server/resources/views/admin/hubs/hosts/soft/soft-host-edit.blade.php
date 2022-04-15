@extends('dialog')

@section('title')
@if($item->id > -1)
@lang('admin/hubs.host_edit_title')
@else
@lang('admin/hubs.host_add_title')
@endif
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
    <div id="lastFormRow" class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_TYP')</label>
        </div>
        <div class="col-sm-9">
            @if($item->id == -1)
            <select id="hostTyp" name="typ" class="custom-select">
                @foreach($item->typeList() as $type)
                <option value="{{ $type->name }}" 
                        data-description="{{ $type->description }}"
                        data-properties="{{ json_encode($type->properties) }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
            <small id="hostTypDescription" class="text-muted"></small>
            @else
            <div class="form-control">{{ $item->typ }}</div>
            <small class="text-muted">{{ $item->type()->description }}</small>
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
        
        @if($item->id > -1)
        let properties = '{{ json_encode($item->type()->properties) }}';
        buildProperties(JSON.parse(properties.replace(/&quot;/g,'"')));
        @foreach(json_decode($item->data) as $key => $val)
        $('#host_edit_form [name="{{ $key }}"]').val('{{ str_replace("\n", '\n', $val) }}');
        @endforeach
        @else
        $('#hostTyp').on('change', function () {
            let description = $('#hostTyp option[value="' + $(this).val() + '"]').data('description');
            $('#hostTypDescription').text(description);
            
            buildProperties($('#hostTyp option[value="' + $(this).val() + '"]').data('properties'));
        }).trigger('change');
        @endif
        
        function buildProperties(properties) {
            console.log(properties);
            
            $('#host_edit_form .property').remove();
            let lastFormRow = $('#lastFormRow');
            let i = 0;
            for (key in properties) {
                let input = '';
                switch (properties[key]) {
                    case 'small':
                        input = '<input class="form-control" name="property_' + i + '" value="">';
                        break;
                    case 'large':
                        input = '<textarea class="form-control" name="property_' + i + '" rows="3"></textarea>';
                        break;
                }
                
                let html = '<div class="row property">' +
                           '    <div class="col-sm-3">' +
                           '        <label class="form-label">' + key + '</label>' +
                           '    </div>' +
                           '    <div class="col-sm-9">' + 
                           input +
                           '    </div>' + 
                           '</div>';
                   
                lastFormRow = $(html).insertAfter(lastFormRow);
                
                i++;
            }
        }
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
