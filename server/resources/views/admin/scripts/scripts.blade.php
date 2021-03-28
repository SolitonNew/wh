@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scriptAdd(); return false;">@lang('admin/scripts.script_add')</a>
@if($data)
<a href="#" class="dropdown-item" onclick="scriptEdit(); return false;">@lang('admin/scripts.script_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="editorShow(); return false;">@lang('admin/scripts.script_show_editor')</a>
<a href="#" class="dropdown-item" onclick="scriptTest(); return false;">@lang('admin/scripts.script_test')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="scriptAttachEvent(); return false;">@lang('admin/scripts.script_attach_event')</a>
@endif
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 320px;min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="scriptsList">
        @foreach($list as $row)
        <a href="{{ route('scripts', $row->id) }}"
            class="tree-item {{ $row->id == $scriptID ? 'active' : '' }}">
            {{ $row->comm }}
            @if($row->var_count > 0)
            <div class="badge badge-pill badge-warning">{{ $row->var_count }}</div>
            @endif
        </a>
        @endforeach
    </div>
    <div class="content-body codeedit">
        <div id="code_rownums" class="script-editor-rownums" data-count="0"></div>
        <div class="script-editor-content">
            <div id="code_preview" class="script-editor-code-view"></div>
            <div id="code_preview_sel" class="script-editor-code-view-sel"></div>
        </div>
    </div>
</div>

@include('admin.scripts.script-editor')

<script>
    $(document).ready(() => {        
        @if($data)
        $('#code_preview_sel').on('click', () => {
            let {anchorOffset, focusOffset} = document.getSelection();
            editorShow(anchorOffset, focusOffset);
        });
    
        $('#code_preview_sel').text($('#editor_original_data').val());
        editorUpdateView($('#code_preview'), $('#editor_original_data').val());
        @endif
        
        let s = $('#editor_original_data').val();
        let a = s.split('\n');
        let aa = new Array();
        for (let i = 1;  i <= a.length; i++) {
            aa.push(i);
        }
        $('#code_rownums').html(aa.join('<br>'));
    });

    function scriptAdd() {
        dialog('{{ route("script-edit", -1) }}');
    }

    @if($scriptID)
    function scriptEdit() {
        dialog('{{ route("script-edit", $scriptID) }}');
    }

    function runScriptTest(source) {
        $.post({
            url: '{{ route("script-test") }}',
            data: {
                '_token': '{{ Session::token() }}',
                'command': source,
            },
            success: function(data) {
                alert(data);
            },
            error: function () {
                alert('ERROR');
            }
        });
    }

    function scriptTest() {
        console.log($('.codetext').text());
        runScriptTest($('.codetext').text());
    }

    function scriptAttachEvent() {
        dialog('{{ route("script-events", $scriptID) }}');
    }
    @endif    
</script>

@endsection
