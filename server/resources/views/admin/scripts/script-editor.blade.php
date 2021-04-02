<div class="script-editor-background">
    <div id="scriptEditorWindow" class="script-editor-window">
        <div id="scriptEditor" style="flex-grow: 1; overflow: hidden;"></div>
        <div class="script-editor-toolbar">
            <button class="btn btn-warning" onclick="editorTest()">@lang('admin/scripts.btn_test')</button>
            <div style="flex-grow: 1"></div>
            <button class="btn btn-primary" onclick="editorSave()">@lang('dialogs.btn_save')</button>
            <button class="btn btn-secondary" onclick="editorHide()" >@lang('dialogs.btn_cancel')</button>
        </div>
    </div>
</div>
<script>         
    var scriptEditor = false;
    
    $(document).ready(function () {
        $(window).on('resize', () => {
            let scriptViewer = $('#scriptViewer')[0];
            let bounds = scriptViewer.getBoundingClientRect();
            $('#scriptEditorWindow').css({
                left: bounds.left + 'px',
                top: bounds.top + 'px',
                width: scriptViewer.offsetWidth,
                height: scriptViewer.offsetHeight,
            });
        });
        
        let ctx = document.getElementById('scriptEditor');
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
            data: '',
            readOnly: false,
        };
        scriptEditor = new ScriptEditor(ctx, options);
    });
    
    function editorShow(selStart, selEnd, data) {
        $('.script-editor-background').fadeIn(250);
        scriptEditor.setData(data);
        scriptEditor.setSelection(selStart, selEnd);
        scriptEditor.focus();
    }

    function editorHide(handler) {
        $('.script-editor-background').fadeOut(250, function () {
            if (handler) handler();
        });
    }

    function editorSave() {
        $.post({
            url: '{{ route("script-save", $scriptID) }}',
            data: {
                '_token': '{{ Session::token() }}',
                data: scriptEditor.getData(),
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
        runScriptTest(scriptEditor.getData());
    }
</script>