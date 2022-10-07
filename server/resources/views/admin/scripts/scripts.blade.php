@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scriptAdd(); return false;">@lang('admin/scripts.script_add')</a>
@if($data)
<a href="#" class="dropdown-item" onclick="scriptEdit(); return false;">@lang('admin/scripts.script_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="scriptEditSource(); return false;">@lang('admin/scripts.script_show_editor')</a>
<a href="#" class="dropdown-item" onclick="scriptTest(); return false;">@lang('admin/scripts.script_test')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="scriptAttachEvent(); return false;">@lang('admin/scripts.script_attach_event')</a>
@endif
@endsection

@section('top-menu')
@endsection

@section('content')
@if($scriptID)
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div id="scriptList" class="tree" style="width: 320px;min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="scriptsList">
        @foreach($list as $row)
        <a href="{{ route('admin.scripts', ['id' => $row->id]) }}"
           class="tree-item {{ $row->id == $scriptID ? 'active' : '' }}" style="white-space: normal; justify-content: space-between;">
            {{ $row->comm }}
            <div class="d-flex align-items-center">
                @if($row->var_count > 0)
                <div class="badge badge-pill badge-warning">{{ $row->var_count }}</div>
                @endif
                <button class="only-small btn btn-primary btn-sm ml-2 script_edit_btn" data-id="{{ $row->id }}">Edit</button>
            </div>
        </a>
        @endforeach
    </div>
    <div id="scriptViewerPanel" class="content-body">
        <div id="scriptViewer" style="height: 100%;"></div>
    </div>
</div>
@else
<div style="display: flex; flex-direction: column; flex-grow: 1;height: 100%; align-items: center;">
    <div class="page-jumbotron">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/scripts.main_prompt')</h5>
            <a href="javascript:scriptAdd()" class="btn btn-primary">@lang('admin/scripts.script_add')</a>
        </div>
    </div>
</div>
@endif
@if($data)
@include('admin.scripts.script-editor')
@endif

<script>
    var scriptViewer = false;

    $(document).ready(() => {
        @if($data)
        let ctx = document.getElementById('scriptViewer');
        let options = {
            readOnly: true,
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
            data: `{!! addslashes($data->data) !!}`,
        };
        scriptViewer = new ScriptEditor(ctx, options);

        $('#scriptViewer').on('click', function (e) {
            const sel = scriptViewer.getSelection();
            editorShow(sel.start, sel.end, scriptViewer.getData());
        });
        @endif

        let a = window.location.href.split('?');
        if (a.length > 1 && a[a.length - 1] == 'editor=show') {
            history.pushState({}, '', a[0]);
            scriptEditSource();
        }

        $('#scriptList button.script_edit_btn').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            scriptEditSource($(this).data('id'));
        });
    });

    function scriptAdd() {
        dialog('{{ route("admin.script-edit", ["id" => -1]) }}');
    }

    @if($scriptID)
    function scriptEdit() {
        dialog('{{ route("admin.script-edit", ["id" => $scriptID]) }}');
    }

    function scriptEditSource(id) {
        if (id && id != {{ $scriptID }}) {
            window.location.href = "{{ route('admin.scripts', ['id' => '']) }}/" + id + '?editor=show';
            return ;
        }
        editorShow(0, 0, scriptViewer.getData());
    }

    function runScriptTest(source) {
        $.post({
            url: '{{ route("admin.script-test") }}',
            data: {
                command: source,
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
        runScriptTest(scriptViewer.getData());
    }

    function scriptAttachEvent() {
        dialog('{{ route("admin.script-events", ["id" => $scriptID]) }}');
    }
    @endif
</script>

@endsection
