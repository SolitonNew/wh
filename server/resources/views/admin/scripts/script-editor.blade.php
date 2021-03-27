<div class="script-editor-background">
    <textarea id="editor_original_data" style="visibility: hidden;">{!! $data->data !!}</textarea>
    <div class="script-editor-container">
        <div class="script-editor-body">
            <div class="script-editor-rownums" data-count="0"></div>
            <div class="script-editor-content">
                <textarea id="script_editor_code" class="script-editor-code"></textarea>
                <div class="script-editor-code-view"></div>
            </div>
        </div>
        <div class="script-editor-toolbar">
            <button class="btn btn-warning" onclick="editorTest()">@lang('admin/scripts.btn_test')</button>
            <div style="flex-grow: 1"></div>
            <button class="btn btn-primary" onclick="editorSave()">@lang('dialogs.btn_save')</button>
            <button class="btn btn-secondary" onclick="editorHide()" >@lang('dialogs.btn_cancel')</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
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
        
        $('#script_editor_code').on('input', function () {
            editorUpdateView($(this).val());
            
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
        
        $('.script-editor-code-view').on('click', () => {
            $('#script_editor_code').focus();
        });
    });
    
    function editorShow() {
        $('#script_editor_code').val($('#editor_original_data').val()).trigger('input');
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
        runScriptTest($('.script-editor-code').val());
    }
    
    function editorUpdateView(code) {
        let separators = [
            ' ', '.', ',', "'", '"', '+', '-', '*', '/', '=', '(', ')', '{', '}', 
            '[', ']', ':', ';', '?', '&', '|', '!', '$', 
            String.fromCharCode(10), 
            String.fromCharCode(13), 
            String.fromCharCode(9)
        ];
        
        let keywords = [
            'if', 
            'else', 
            'for',
            'switch',
            'case',
            'default',
            'break',
            'get',
            'set',
            'on',
            'off',
            'toggle',
            'speech',
            'play',
            'info',
        ];
        
        
        let parts = new Array();
        let s = '';
        for (let i = 0; i < code.length; i++) {
            if (separators.indexOf(code[i]) >= 0) {
                if (s != '') {
                    parts.push(s);
                    s = '';
                }
                parts.push(code[i]);
            } else {
                s += code[i];
            }
        }
        
        for (let i = 0; i < parts.length; i++) {
            if (keywords.indexOf(parts[i]) >= 0) {
                parts[i] = '<span class="keyword">' + parts[i] + '</span>'
            }
        }
        
        $('.script-editor-code-view').html(parts.join(''));
        
        
    }
</script>