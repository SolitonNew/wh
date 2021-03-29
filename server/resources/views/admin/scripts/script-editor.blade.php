<div class="script-editor-background">
    <textarea id="editor_original_data" style="visibility: hidden;">{!! $data->data !!}</textarea>
    <div class="script-editor-container">
        <div class="script-editor-body">
            <div id="script_editor_rownums" class="script-editor-rownums" data-count="0"></div>
            <div class="script-editor-content">
                <textarea id="script_editor_code" class="script-editor-code"></textarea>
                <div id="script_editor_code_view" class="script-editor-code-view"></div>
                <div id="script_editor_code_view_sel" class="script-editor-code-view-sel"></div>
                <div id="script_editor_code_helper" class="script-editor-code-helper">
                @foreach($helper as $row)
                <div class="script-editor-code-helper-item" data-word="{{ $row->keyword }}">
                    <span class="strong {{ $row->type }}">{{ $row->keyword }}</span>
                    <span class="italic text-muted" >{{ $row->description }}</span>
                </div>
                @endforeach
                </div>
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
    var script_editor_tab_chars = 4;
    
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
            $('#script_editor_code_view_sel').text($(this).val())
            editorUpdateView($('#script_editor_code_view'), $(this).val());
            
            let s = $(this).val();
            let n = 0;
            if (s) {
                n = s.split('\n').length;
            }
            let nums = $('#script_editor_rownums');
            if (nums.data('count') != n) {
                let a = new Array(n);
                for (let i = 0; i < n; i++) {
                    a[i] = (i + 1);
                }
                nums.data('count', n);
                nums.html(a.join('<br>'));
            }
        }).trigger('input');
        
        $('.script-editor-code-helper-item').on('click', function (e) {
            $('.script-editor-code-helper-item.active').removeClass('active');
            $(this).addClass('active');
            let ev = $.Event('keydown');
            ev.code = 'Enter';
            $('#script_editor_code')
                    .trigger(ev)
                    .focus();
        });
        
        $('#script_editor_code').on('keydown', function (e) {
            if (e.code == 'Tab') {
                e.preventDefault();
                
                let selStart = $('#script_editor_code').prop('selectionStart');
                let selEnd = $('#script_editor_code').prop('selectionEnd');
                let text = $(this).val();
                
                let text_before = text.substr(0, selStart);
                let text_after = text.substr(selStart);
                
                let a_before = text_before.split(/\r?\n/);
                
                let chars_before = 0;
                if (a_before.length > 0) {
                    chars_before = a_before[a_before.length - 1].length;
                }
                
                let insert_chars = 0;
                let insert_text = '';

                if (e.shiftKey) {
                    let n = 0;
                    let s = a_before[a_before.length - 1];
                    for (let i = 0; i < s.length; i++) {
                        if (s[i] != ' ') break;
                        n++;
                    }
                    
                    if (n % script_editor_tab_chars == 0) {
                        insert_chars -= script_editor_tab_chars;
                    } else {
                        if (n > script_editor_tab_chars) {
                            insert_chars = Math.floor(n / script_editor_tab_chars) * script_editor_tab_chars - n;
                        } else {
                            insert_chars = -n;
                        }
                    }
                    
                    if (insert_chars < 0) {
                        text_before = text_before.substr(0, text_before.length + insert_chars);
                    }
                } else {
                    if (chars_before % script_editor_tab_chars == 0) {
                        insert_chars = script_editor_tab_chars;
                    } else {
                        insert_chars = Math.ceil(chars_before / script_editor_tab_chars) * script_editor_tab_chars - chars_before;
                    }

                    insert_text = ' '.repeat(insert_chars);
                }
                
                $(this).val(text_before + insert_text + text_after);

                $('#script_editor_code').prop('selectionStart', selStart + insert_chars);
                $('#script_editor_code').prop('selectionEnd', selStart + insert_chars);

                $(this).trigger('input');
            } else
            if (e.code == 'ArrowLeft') {
                if (editorHelperKeyLeft()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'ArrowRight') {
                if (editorHelperKeyRight()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'ArrowUp') {
                if (editorHelperKeyTop()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'ArrowDown') {
                if (editorHelperKeyBottom()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'Escape') {
                if (editorHelperKeyEsc()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'Enter') {
                if (editorHelperKeyEnter()) {
                    e.preventDefault();
                }
            }
        });
        
        $('#script_editor_code').on('keyup', function (e) {
            if (e.code == 'Enter') {
                if (editorHelperVisible()) return ;
                
                let selStart = $('#script_editor_code').prop('selectionStart');
                let text = $(this).val();
                
                let text_before = text.substr(0, selStart);
                let text_after = text.substr(selStart);
                
                let a_before = text_before.split(/\r?\n/);
                
                if (a_before.length > 0) {
                    let insert_chars = 0;
                    let s = a_before[a_before.length - 2];
                    for (let i = 0; i < s.length; i++) {
                        if (s[i] != ' ') break;
                        insert_chars++;
                    }
                    
                    for (let i = s.length - 1; i > -1; i--) {
                        if (s[i] != ' ') {
                            if (s[i] == '{') {
                                insert_chars += script_editor_tab_chars;
                            }
                            break;
                        }
                    }
                    
                    if (insert_chars > 0) {
                        let insert_text = ' '.repeat(insert_chars);
                        
                        $(this).val(text_before + insert_text + text_after);

                        $('#script_editor_code').prop('selectionStart', selStart + insert_chars);
                        $('#script_editor_code').prop('selectionEnd', selStart + insert_chars);

                        $(this).trigger('input');
                    }   
                }
            } else
            if (e.ctrlKey && e.code == 'Space') {
                editorHelperShow();
            }
        });
        
        $('#script_editor_code').on('scroll', function (e) {
            editorScrollSync($(this).scrollLeft(), $(this).scrollTop());
        });
        
        $('#script_editor_code_view_sel').on('scroll', function (e) {            
            editorScrollSync($(this).scrollLeft(), $(this).scrollTop());
        });
        
        $('#script_editor_code_view_sel').on('click', function (e) {
            let {anchorOffset, focusOffset} = document.getSelection();
            let start = Math.min(anchorOffset, focusOffset);
            let end = Math.max(anchorOffset, focusOffset);
            $('#script_editor_code').focus();
            $('#script_editor_code').prop('selectionStart', start);
            $('#script_editor_code').prop('selectionEnd', end);
            
            editorHelperHide();
        });
    });
    
    var editorScrollX;
    var editorScrollY;
    
    function editorScrollSync(scrollX, scrollY) {
        if (scrollX == editorScrollX && scrollY == editorScrollY) return ;
        
        editorScrollX = scrollX;
        editorScrollY = scrollY;
        
        $('#script_editor_code').scrollLeft(scrollX);
        $('#script_editor_code').scrollTop(scrollY);
        $('#script_editor_code_view').scrollLeft(scrollX);
        $('#script_editor_code_view').scrollTop(scrollY);
        $('#script_editor_code_view_sel').scrollLeft(scrollX);
        $('#script_editor_code_view_sel').scrollTop(scrollY);
        $('#script_editor_rownums').scrollTop(scrollY);
    }
    
    function editorShow(selStart, selEnd) {
        $('#script_editor_code').val($('#editor_original_data').val()).trigger('input');
        $('.script-editor-background').fadeIn(250, () => {
            if (selStart != undefined && selEnd != undefined) {
                let start = Math.min(selStart, selEnd);
                let end = Math.max(selStart, selEnd);
                $('#script_editor_code').focus();
                $('#script_editor_code').prop('selectionStart', start);
                $('#script_editor_code').prop('selectionEnd', end);        
            }
        });
    }

    function editorHide(handler) {
        $('.script-editor-background').fadeOut(250, handler);
    }

    function editorSave() {
        $.post({
            url: '{{ route("script-save", $scriptID) }}',
            data: {
                '_token': '{{ Session::token() }}',
                data: $('#script_editor_code').val(),
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
        runScriptTest($('#script_editor_code').val());
    }
    
    function editorUpdateView(viewer, code) {
        let separators = [
            ' ', '.', ',', "'", '"', '+', '-', '*', '/', '=', '(', ')', '{', '}', 
            '[', ']', ':', ';', '?', '&', '|', '!', '$', 
            String.fromCharCode(10), 
            String.fromCharCode(13), 
            String.fromCharCode(9)
        ];
        
        let keywords = [
            @foreach($keywords as $key => $descr)
            '{{ $key }}',
            @endforeach
        ];
        
        let parts = new Array();
        let s = '';
        for (let i = 0; i < code.length; i++) {
            if (separators.indexOf(code[i]) >= 0) {
                if (s != '') {
                    parts.push(s);
                    s = '';
                }
                
                if (code[i] == '/' && code[i + 1] == '*') {
                    s = '/*';
                    let find = false;
                    for (let k = i + 2; k < code.length - 1; k++) {
                        if (code[k] == '*' && code[k + 1] == '/') {
                            s += '*/';
                            i = k + 1;
                            find = true;
                            break;
                        }
                        s += code[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = code.length;
                } else
                if (code[i] == '/' && code[i + 1] == '/') {
                    s = '//';
                    let find = false;
                    for (let k = i + 2; k < code.length; k++) {
                        if (code[k] == '\r' || code[k] == '\n') {
                            i = k - 1;
                            find = true;
                            break;
                        }
                        s += code[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = code.length;
                } else
                if (code[i] == "'") {
                    s = "'";
                    let find = false;
                    for (let k = i + 1; k < code.length; k++) {
                        if (code[k] == "'") {
                            s += "'";
                            i = k;
                            find = true;
                            break;
                        }
                        s += code[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = code.length;
                } else {
                    parts.push(code[i]);
                }
            } else {
                s += code[i];
            }
        }
        if (s != '') {
            parts.push(s);
        }
        
        for (let i = 0; i < parts.length; i++) {
            if (keywords.indexOf(parts[i]) >= 0) {
                parts[i] = '<span class="keyword">' + parts[i] + '</span>';
            } else
            if (parts[i].substr(0, 2) == '/*' || parts[i].substr(0, 2) == '//') {
                parts[i] = '<span class="comment">' + parts[i] + '</span>';
            } else
            if (parts[i].substr(0, 1) == "'") {
                parts[i] = '<span class="text">' + parts[i] + '</span>';
            }
        }
        
        $(viewer).html(parts.join(''));
    }
    
    function editorHelperShow() {
        if (!editorHelperVisible()) {
            $('#script_editor_code_helper').fadeIn(150);
        }
        
        let selStart = $('#script_editor_code').prop('selectionStart');
        let text = $('#script_editor_code').val();
        let text_before = text.substr(0, selStart);
        let a_before = text_before.split(/\r?\n/);
        
        let top = 1 + (a_before.length * 1.5);
        let left = 1;
        if (a_before.length > 0) {
            left = 1 + (a_before[a_before.length - 1].length * 0.6);
        }
        
        $('#script_editor_code_helper').css({
            top: top + 'rem',
            left: left + 'rem',
        });
    }
    
    function editorHelperHide() {
        $('#script_editor_code_helper').fadeOut(150);
    }
    
    function editorHelperVisible() {
        return $('#script_editor_code_helper').css('display') != 'none';
    }
    
    function editorHelperKeyLeft() {
        if (editorHelperVisible()) {
            $('#script_editor_code_helper .active').removeClass('active');
            let elem = $('#script_editor_code_helper div')
                            .first()
                            .addClass('active');
            editorHelperScrollToVisible();
            return true;
        }
        return false;
    }
    
    function editorHelperKeyRight() {
        if (editorHelperVisible()) {
            $('#script_editor_code_helper .active').removeClass('active');
            let elem = $('#script_editor_code_helper div')
                            .last()
                            .addClass('active');
            editorHelperScrollToVisible();
            return true;
        }
        return false;
    }
    
    function editorHelperKeyTop() {
        if (editorHelperVisible()) {
            let active = $('#script_editor_code_helper .active');
            if (active.length) {
                active = active
                            .removeClass('active')
                            .prev()
                            .addClass('active');
                if (active.length == 0) {
                    $('#script_editor_code_helper div')
                        .last()
                        .addClass('active');
                }
            } else {
                $('#script_editor_code_helper div')
                    .last()
                    .addClass('active');
            }
            editorHelperScrollToVisible();
            return true;
        }
        return false;
    }
    
    function editorHelperKeyBottom() {
        if (editorHelperVisible()) {
            let active = $('#script_editor_code_helper .active');
            if (active.length) {
                active = active
                            .removeClass('active')
                            .next()
                            .addClass('active');
                if (active.length == 0) {
                    $('#script_editor_code_helper div')
                        .first()
                        .addClass('active');
                }
            } else {
                let elem = $('#script_editor_code_helper div')
                            .first()
                            .addClass('active');
            }
            editorHelperScrollToVisible();
            return true;
        }
        return false;
    }
    
    function editorHelperScrollToVisible() {
        let helper = $('#script_editor_code_helper');
        let padding = parseInt(helper.css('padding-top'));
        let active = $('#script_editor_code_helper .active');
        if (active.length) {
            active = active[0];
            if (active.offsetTop - padding < helper.scrollTop()) {
                helper.scrollTop(active.offsetTop - padding);
            } else {
                let item_bottom = active.offsetTop + active.offsetHeight;
                if (item_bottom > helper.scrollTop() + helper[0].clientHeight - padding) {
                    helper.scrollTop(item_bottom - helper[0].clientHeight + padding);
                }
            }
        }
    }
    
    function editorHelperKeyEsc() {
        if (editorHelperVisible()) {
            editorHelperHide();
            return true;
        }
        return false;
    }
    
    function editorHelperKeyEnter() {
        if (editorHelperVisible()) {
            
            let selStart = $('#script_editor_code').prop('selectionStart');
            let text = $('#script_editor_code').val();
            let text_before = text.substr(0, selStart);
            let text_after = text.substr(selStart);
            //let a_before = text_before.split(/\r?\n/);
            
            let word = $('#script_editor_code_helper div.active').data('word');
            if (word) {
                let insert_chars = word.length;

                $('#script_editor_code').val(text_before + word + text_after).trigger('input');

                $('#script_editor_code').prop('selectionStart', selStart + insert_chars);
                $('#script_editor_code').prop('selectionEnd', selStart + insert_chars);   
            }
            
            editorHelperHide();
            return true;
        }
        return false;
    }
</script>