@extends('dialog')

@section('title')
@lang('admin/scripts.script_add_title')
@endsection

@section('content')
<form id="script_add_form" class="container" method="POST" action="{{ route('admin.script-edit', ['id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/scripts.table_TEMPLATE')</label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select" name="template">
                @foreach($templates as $key => $val)
                <option value="{{ $key }}">{{ $key }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="scriptBeforeParams">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/scripts.table_COMM')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="comm" value="{{ $item->comm }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="scriptTemplateEditorRow" style="display: none;">
        <div class="col-sm-12">
            <div id="scriptTemplateEditor" class="border" style="height: 11rem;"></div>
        </div>
    </div>
    <input name="attachDevices" vlaue="" type="hidden">
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-primary" onclick="scriptEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    var scriptTemplateEditor = false;
    
    $(document).ready(() => {
        let ctx = document.getElementById('scriptTemplateEditor');
        let options = {
        @foreach([\App\Library\Script\ScriptEditor::makeKeywords()] as $row)
            keywords: [
            @foreach($row->keywords as $key => $descr)
                '{{ $key }}',
            @endforeach
            ],
            functions: [
            @foreach($row->functions as $key => $descr)
                {name: '{{ $key }}', description: '{{ $descr }}'},
            @endforeach
            ],
            strings: [
            @foreach($row->strings as $key => $descr)
                {name: '{{ $key }}', description: '{{ $descr }}'},
            @endforeach
            ],
        @endforeach
            readOnly: false,
            name: 'templateSource',
        };
        scriptTemplateEditor = new ScriptEditor(ctx, options);
        
        
        $('#script_add_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
        
        $('#script_add_form [name="template"]').on('change', function () {
            scriptEditTemplate($(this).val());
        }).trigger('change');
    });

    function scriptEditOK() {
        $('#script_add_form').submit();
    }
    
    function scriptEditTemplate(key) {
        $('#script_add_form [data-param]').css('opacity', 0);
        $('#scriptTemplateEditorRow').css('opacity', 0);
        
        $.ajax({
            url: '{{ route("admin.script-template") }}',
            data: {
                template: key,
            },
            success: function (data) {
                $('#script_add_form [data-param]').remove();
                $('#scriptBeforeParams').after(data);
                
                $('#script_add_form [data-param] select').on('change', function () {
                    scriptEditorSetSource();
                });
                
                $('#script_add_form [data-param] input').on('input', function () {
                    scriptEditorSetSource();
                });
                
                scriptEditorSetSource();
            },
            error: function (err) {
                $('#script_add_form [data-param]').remove();
            }
        });
    }
    
    function scriptEditorSetSource() {
        let name = $('#scriptTemplateName').val();
        
        let room = 'ROOM';
        let p = $('#script_add_form [data-param]');
        if (p.length) {
            let sel = $('select', p[0]);
            if (sel.length) {
                room = $('option[value="' + sel.val() + '"]', sel).data('room');
                if (room == undefined) {
                    room = 'ROOM';
                }
            }
        }
        $('#script_add_form input[name="comm"]').val(name.replaceAll('@ROOM@', room));
        
        // --------------------------------------------
        let events = new Array();
        $('#script_add_form [data-event] select').each(function () {
            let id = $('option[value="' + $(this).val() + '"]', this).data('id');
            events.push(id);
        });
        $('#script_add_form input[name="attachDevices"]').val(events.join(','));
        // --------------------------------------------
        
        let source = $('#scriptTemplateSource').val();
        
        if (!source) {
            $('#scriptTemplateEditorRow').hide();
            return ;
        }
        
        $('#script_add_form [data-param]').each(function () {
            let param = $('select, input', this);
            let key = $(this).data('param');
            let value = param.val();
            
            source = source.replaceAll('@' + key + '@', value ? value : key);
        });
        
        scriptTemplateEditor.setData(source);
        $('#scriptTemplateEditorRow').css('opacity', 1);
        $('#scriptTemplateEditorRow').show();
    }
</script>
@endsection
