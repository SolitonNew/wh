@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scriptAdd(); return false;">@lang('admin/scripts.script_add')</a>
@if($data)
<a href="#" class="dropdown-item" onclick="scriptEdit(); return false;">@lang('admin/scripts.script_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="editorShow(); return false;">@lang('admin/scripts.script_show_editor')</a>
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
<div class="script-editor-background">
    <div class="script-editor-container">
        <div class="script-editor-body">
            <div class="script-editor-rownums" data-count="0"></div>
            <textarea class="script-editor-code">{{ $data->data }}</textarea>
        </div>
        <div class="script-editor-toolbar">
            <button class="btn btn-warning" onclick="editorTest()">@lang('admin/scripts.btn_test')</button>
            <div style="flex-grow: 1"></div>
            <button class="btn btn-primary" onclick="editorSave()">@lang('dialogs.btn_save')</button>
            <button class="btn btn-secondary" onclick="editorHide()" >@lang('dialogs.btn_cancel')</button>
        </div>
    </div>
</div>
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

        $(window).on('resize', () => {
            let codeedit = $('.codeedit');
            let codeedit_pos = codeedit.position();
            let editor = $('.script-editor-container');
            editor.css({
                left: codeedit_pos.left + 'px',
                top: codeedit_pos.top + 'px',
                width: codeedit.width() + 'px',
                height: codeedit.height() + 'px',
            });
        });

        $('.script-editor-code').on('input', function () {
            let s = $(this).val();
            let n = 0;
            if (s) {
                n = s.split('\n').length;
            }
            let nums = $('.script-editor-rownums');
            if (nums.data('count') != n) {
                let a = new Array(n);
                for (let i = 0; i < n; i++) {
                    a[i] = (i + 1);
                }
                nums.data('count', n);
                nums.html(a.join('<br>'));
            }
        }).trigger('input');
    });

    function scriptAdd() {
        dialog('{{ route("script-edit", -1) }}');
    }

    @if($scriptID)
    function scriptEdit() {
        dialog('{{ route("script-edit", $scriptID) }}');
    }

    function scriptAttachEvent() {
        dialog('{{ route("script-events", $scriptID) }}');
    }

    function editorShow() {
        $('.script-editor-background').fadeIn(250);
    }

    function editorHide(handler) {
        $('.script-editor-background').fadeOut(250, handler);
    }

    function editorSave() {
        $.post({
            url: '{{ route("script-save", $scriptID) }}',
            data: {
                '_token': '{{ Session::token() }}',
                data: $('.script-editor-code').val(),
            },
            success: function (data) {
                if (data == 'OK') {
                    editorHide(() => {
                        window.location.reload();
                    });
                } else {
                    console.log(data);
                }
            }
        });
    }
    
    function editorTest() {
        $.post({
            url: '{{ route("script-test") }}',
            data: {
                '_token': '{{ Session::token() }}',
                'command': $('.script-editor-code').val(),
            },
            success: function(data) {
                alert(data);
            },
            error: function () {
                alert('ERROR');
            }
        });
    }
    @endif    
</script>

@endsection
