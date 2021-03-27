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
        <div class="numbers">000</div>
        <div class="codetext">{!! $sourceCode !!}</div>
    </div>
</div>

@if($data)
@include('admin.scripts.script-editor')
@endif

<script>
    $(document).ready(() => {
        let s = $('.content-body .codetext').text();
        let a = s.split('\n');
        let aa = new Array();
        for (let i = 1;  i <= a.length; i++) {
            aa.push(i);
        }
        $('.content-body .numbers').html(aa.join('<br>'));
        
        @if($data)
        $('.codetext').on('click', () => {
            editorShow();
        });
        @endif
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
