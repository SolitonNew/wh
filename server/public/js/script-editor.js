/* 
 * Author: Moklyak Alexandr
 */

function ScriptEditor(ctx, options) {
    this.init(ctx, options);
}

ScriptEditor.prototype = {
    _ctx: null,
    _options: {},
    _functionsList: [],
    _stringList: [],
    _nums: false,
    _viewer: false,
    _editor: false,
    _caret: false,
    _isFocused: false,
    _helper: false,
    _helper_text: '',
    _separators: [
        ' ', '.', ',', "'", '"', '+', '-', '*', '/', '=', '(', ')', '{', '}', 
        '[', ']', ':', ';', '?', '&', '|', '!', 
        String.fromCharCode(10), 
        String.fromCharCode(13), 
        String.fromCharCode(9)
    ],
    _caretPos: {
        x: 0,
        y: 0,
    },
    setOptions: function (options) {
        let owner = this;
        
        if (typeof(options.tabSize) != 'undefined') {
            this._options.tabSize = options.tabSize;
        }
        
        if (typeof(options.keywords) != 'undefined') {
            this._options.keywords = options.keywords;
        }
        
        if (typeof(options.functions) != 'undefined') {
            this._options.functions = options.functions;
        }
        
        if (typeof(options.strings) != 'undefined') {
            this._options.strings = options.strings;
        }
        
        if (typeof(options.readOnly) != 'undefined') {
            this._options.readOnly = options.readOnly;
        }
        
        if (typeof(options.data) != 'undefined') {
            this._editor.value = options.data;
        }
        
        /* ---------------- */
        
        this._functionsList = new Array();
        this._options.functions.forEach(function (item) {
            owner._functionsList.push(item.name);
        });
        
        this._stringsList = new Array();
        this._options.strings.forEach(function (item) {
            owner._stringsList.push(item.name);
        });
                
        if (this._options.readOnly) {
            this._editor.setAttribute('readonly', 'true');
        } else {
            this._editor.removeAttribute('readonly');
        }
    },
    init: function (ctx, options) {
        /* Устанавливаем значения по умолчанию */
        this._options = {
            tabSize: 4,
            keywords: [
                'if', 
                'else', 
                'for',
                'switch',
                'case',
                'default',
                'break',
            ],
            functions: [],
            strings: [],
            readOnly: false,
        };
        
        /* Выполняем преднастройку */
        this._ctx = ctx;
        this._makeLayouts();
        this.setOptions(options);
        this._calcCharSize();
        this._caret.style.height = this._charSize.h + 'px';
        this.update();
        this._editor.selectionStart = 0;
        this._editor.selectionEnd = 0;
        this._caretBlink();
    },
    _calcCharSize: function () {
        this._editorStyle = getComputedStyle(this._editor);
        let div = document.createElement('div');
        
        let s = '';
        for (let i = 0; i < 20; i++) {
            s += 'W'.repeat(20) + '<br>';
        }
        div.innerHTML = s;
        div.style.display = 'inline-block';
        div.style.fontFamily = this._editorStyle.fontFamily;
        div.style.fontSize = this._editorStyle.fontSize;
        div.style.lineHeight = this._editorStyle.lineHeight;
        document.body.appendChild(div);
        this._charSize = {
            w: div.offsetWidth / 20,
            h: div.offsetHeight / 20,
        }
        document.body.removeChild(div);
    },
    focus: function () {
        this._editor.focus();
    },
    setSelection: function (start, end) {
        this._editor.selectionStart = start;
        this._editor.selectionEnd = end;
    },
    getSelection: function () {
        return {
            start: this._editor.selectionStart,
            end: this._editor.selectionEnd,
        }
    },
    setData: function (data) {
        this._editor.value = data;
        this.update();
    },
    getData: function () {
        return this._editor.value;
    },
    _makeLayouts: function () {
        /* Чистим старое если вызываем повторно */
        if (this._nums) {
            this._nums = false;
        }
        if (this._viewer) {
            this._viewer = false;
        }
        if (this._editor) {
            this._editor = false;
        }
        if (this._caret) {
            this._caret = false;
        }
        
        /* Создаем составные элементы */
        
        this._nums = document.createElement('div');
        this._nums.classList.add('script-editor-nums');
        
        this._viewer = document.createElement('div');
        this._viewer.classList.add('script-editor-viewer');
        
        this._editor = document.createElement('textarea');
        this._editor.classList.add('script-editor-editor');
        
        this._caret = document.createElement('div');
        this._caret.classList.add('script-editor-caret');
        
        /* Формируем макет редактора */
        
        let content = document.createElement('div');
        content.classList.add('script-editor-content');
        content.appendChild(this._viewer);
        content.appendChild(this._editor);
        content.appendChild(this._caret);
        
        let container = document.createElement('div');
        container.classList.add('script-editor-container');
        container.appendChild(this._nums);
        container.appendChild(content);
        
        this._ctx.appendChild(container);
        
        /* Назначаем слушателей на события */
        
        let owner = this;
        
        window.addEventListener('resize', function (e) {
            owner._helperUpdate();
        });
        
        window.addEventListener('click', function (e) {
            if (owner._helperVisible()) {
                owner._helperHide();
            }
        });
        
        this._editor.addEventListener('input', function (e) {
            owner.update();
        });
        
        this._editor.addEventListener('click', function (e) {
            owner.update();
        });
        
        this._editor.addEventListener('keydown', function (e) {
            if (owner._options.readOnly) return ;
            
            if (e.code == 'Tab') {
                e.preventDefault();
                
                let selStart = owner._editor.selectionStart;
                let selEnd = owner._editor.selectionEnd;
                let text = owner._editor.value;
                let text_before = text.substr(0, selStart);
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
                    
                    if (n % owner._options.tabSize == 0) {
                        insert_chars -= owner._options.tabSize;
                    } else {
                        if (n > owner._options.tabSize) {
                            insert_chars = Math.floor(n / owner._options.tabSize) * owner._options.tabSize - n;
                        } else {
                            insert_chars = -n;
                        }
                    }
                    
                    if (insert_chars < 0) {
                        text_before = text_before.substr(0, text_before.length + insert_chars);
                    }
                } else {
                    if (chars_before % owner._options.tabSize == 0) {
                        insert_chars = owner._options.tabSize;
                    } else {
                        insert_chars = Math.ceil(chars_before / owner._options.tabSize) * owner._options.tabSize - chars_before;
                    }
                    insert_text = ' '.repeat(insert_chars);
                }
                
                owner.insertText(selStart, selEnd, insert_text);
            } else
            if (e.code == 'ArrowUp') {
                if (owner._helperKeyUp()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'ArrowDown') {
                if (owner._helperKeyDown()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'Escape') {
                if (owner._helperKeyEsc()) {
                    e.preventDefault();
                }
            } else
            if (e.code == 'Enter') {
                if (owner._helperKeyEnter()) {
                    e.preventDefault();
                }
            }
        
            owner._updateScrollers();
            owner._helperUpdate();
            owner._updateCaret();
        });
        
        this._editor.addEventListener('keyup', function (e) {
            if (owner._options.readOnly) return ;
            
            if (e.code == 'Enter') {
                if (owner._helperVisible()) {
                    // 
                } else {
                    let selStart = owner._editor.selectionStart;
                    let selEnd = owner._editor.selectionEnd;
                    let text = owner._editor.value;
                    let text_before = text.substr(0, selStart);
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
                                    insert_chars += owner._options.tabSize;
                                }
                                break;
                            }
                        }

                        if (insert_chars > 0) {
                            let insert_text = ' '.repeat(insert_chars);                        
                            owner.insertText(selStart, selEnd, insert_text);
                        }   
                    }
                }
            } else
            if (e.ctrlKey && e.code == 'Space') {
                owner._helperShow();
            }
        
            owner._updateScrollers();
            owner._helperUpdate();
            owner._updateCaret();
        });
        
        this._editor.addEventListener('scroll', function (e) {
            owner._updateScrollers();
        });
        
        this._editor.addEventListener('focus', function (e) {
            owner._isFocused = true;
            owner._updateCaret();
        });
        
        this._editor.addEventListener('blur', function (e) {
            owner._isFocused = false;
            owner._caret.style.display = 'none';
        });
    },
    update: function () {
        let source = this._editor.value;
        let parts = this._splitSource(source);
        
        let keywords = this._options.keywords ? this._options.keywords : new Array();        
        
        for (let i = 0; i < parts.length; i++) {
            if (keywords.indexOf(parts[i]) >= 0) {
                parts[i] = '<span class="keyword">' + parts[i] + '</span>';
            } else
            if (this._functionsList.indexOf(parts[i]) >= 0) {
                parts[i] = '<span class="keyword">' + parts[i] + '</span>';
            } else
            if (parts[i].substr(0, 2) == '/*' || parts[i].substr(0, 2) == '//') {
                parts[i] = '<span class="comment">' + parts[i] + '</span>';
            } else
            if (parts[i].substr(0, 1) == "'") {
                parts[i] = '<span class="string">' + parts[i] + '</span>';
            }
        }
        
        this._viewer.innerHTML = parts.join('') + '<br>';
        this._updateCaret();
        this._updateNums();
    },
    _updateNums: function () {
        let source = this._editor.value;
        let n = 1;
        if (source) {
            n = source.split('\n').length;
        }
        if (this._nums.getAttribute('data-count') != n) {
            let a = new Array(n);
            for (let i = 0; i < n; i++) {
                a[i] = (i + 1);
            }
            this._nums.setAttribute('data-count', n);
            this._nums.innerHTML = a.join('<br>');
        }
    },
    _updateCaret: function () {
        let selStart = this._editor.selectionEnd;
        let text = this._editor.value;
        let text_before = text.substr(0, selStart);
        let a_before = text_before.split(/\r?\n/);
        this._caretPos.x = 0;
        this._caretPos.y = a_before.length;
        if (a_before.length) { 
            this._caretPos.x = a_before[a_before.length - 1].length;
        }
        
        let left = (this._caretPos.x * this._charSize.w) - 1 - this._editor.scrollLeft + parseInt(this._editorStyle.paddingLeft);
        let top = ((this._caretPos.y - 1) * this._charSize.h) - this._editor.scrollTop + parseInt(this._editorStyle.paddingTop);
        
        if (left != this._caretPos.x || top != this._caretPos.y) {
            this._caret.style.left = left + 'px';
            this._caret.style.top = top + 'px';
            this._caretPos.x = left;
            this._caretPos.y = top;
        }
        
        if (!this._options.readOnly) {
            this._caret.style.display = 'inline-block';
        }
    },
    _splitSource: function (source) {
        let parts = new Array();
        let s = '';
        for (let i = 0; i < source.length; i++) {
            if (this._separators.indexOf(source[i]) >= 0) {
                if (s != '') {
                    parts.push(s);
                    s = '';
                }
                
                if (source[i] == '/' && source[i + 1] == '*') {
                    s = '/*';
                    let find = false;
                    for (let k = i + 2; k < source.length; k++) {
                        if (source[k] == '*' && source[k + 1] == '/') {
                            s += '*/';
                            i = k + 1;
                            find = true;
                            break;
                        }
                        s += source[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = source.length;
                } else
                if (source[i] == '/' && source[i + 1] == '/') {
                    s = '//';
                    let find = false;
                    for (let k = i + 2; k < source.length; k++) {
                        if (source[k] == '\r' || source[k] == '\n') {
                            i = k - 1;
                            find = true;
                            break;
                        }
                        s += source[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = source.length;
                } else
                if (source[i] == "'") {
                    s = "'";
                    let find = false;
                    for (let k = i + 1; k < source.length; k++) {
                        if (source[k] == "'") {
                            s += "'";
                            i = k;
                            find = true;
                            break;
                        }
                        s += source[k];
                    }
                    parts.push(s);
                    s = '';
                    if (!find) i = source.length;
                } else {
                    parts.push(source[i]);
                }
            } else {
                s += source[i];
            }
        }
        if (s != '') {
            parts.push(s);
        }
        
        return parts;
    },
    _updateScrollers: function () {
        this._viewer.scrollTop = this._editor.scrollTop;
        this._viewer.scrollLeft = this._editor.scrollLeft;
        this._nums.scrollTop = this._editor.scrollTop;
        this._updateCaret();
    },
    _caretBlink: function () {
        if (this._caret) {
            if (this._caret.style.display == 'inline-block') {
                this._caret.style.display = 'none';
            } else {
                if (this._isFocused && !this._options.readOnly) {
                    this._caret.style.display = 'inline-block';
                }
            }
            let owner = this;
            setTimeout(function () {
                owner._caretBlink();
            }, 500);
        }
    },
    insertText: function (selStart, selEnd, text) {
        this._editor.focus();
        this._editor.selectronStart = selStart;
        this._editor.selectionEnd = selEnd;
        document.execCommand('insertText', false, text);
    },
    
    _helperKeyUp: function () {
        if (this._helperVisible()) {
            let ls = new Array();
            this._helper.querySelectorAll('div').forEach(function (item) {
                if (item.style.display != 'none') {
                    ls.push(item);
                }
            });
            
            let index = -1;
            ls.forEach(function (item, i) {
                if (item.classList.contains('active')) {
                    index = i;
                }
            });
            
            if (ls.length) {
                if (index == -1) {
                    ls[ls.length - 1].classList.add('active');
                } else {
                    this._helper.querySelectorAll('div.active').forEach(function (item) {
                        item.classList.remove('active');
                    });
                    index--;
                    if (index < 0) {
                        ls[ls.length - 1].classList.add('active');
                    } else {
                        ls[index].classList.add('active');
                    }
                }
            }
            
            return true;
        }
        return false;
    },
    _helperKeyDown: function () {
        if (this._helperVisible()) {
            let ls = new Array();
            this._helper.querySelectorAll('div').forEach(function (item) {
                if (item.style.display != 'none') {
                    ls.push(item);
                }
            });
            
            let index = -1;
            ls.forEach(function (item, i) {
                if (item.classList.contains('active')) {
                    index = i;
                }
            });
            
            if (ls.length) {
                if (index == -1) {
                    ls[0].classList.add('active');
                } else {
                    this._helper.querySelectorAll('div.active').forEach(function (item) {
                        item.classList.remove('active');
                    });
                    index++;
                    if (index >= ls.length) {
                        ls[0].classList.add('active');
                    } else {
                        ls[index].classList.add('active');
                    }
                }
            }
            
            return true;
        }
        return false;
    },
    _helperKeyEsc: function () {
        if (this._helperVisible()) {
            this._helperHide();
            return true;
        }
        return false;
    }, 
    _helperKeyEnter: function () {
        if (this._helperVisible()) {
            let selWord = null;
            this._helper.querySelectorAll('div.active').forEach(function (item) {
                if (item.style.display != 'none') {
                    selWord = item;
                }
            });
            
            if (selWord) {
                let selStart = this._editor.selectionStart;
                let selEnd = this._editor.selectionEnd;
                let text = this._editor.value;
                let text_after = text.substr(selStart);

                let helper_text_len = this._helper_text.length;
                if (this._helper_text[0] == "'") {
                    helper_text_len--;
                } else 
                if (this._separators.indexOf(this._helper_text) > -1) {
                    helper_text_len = 0;
                }
                
                let word = selWord.getAttribute('data-word').substr(helper_text_len);
                
                let parts_after = this._splitSource(text_after);
                if (parts_after.length) {
                    if (parts_after[0][0] != "'" &&  this._separators.indexOf(parts_after[0]) == -1) {
                        selEnd = selEnd + parts_after[0].length;
                    }
                }
                
                this.insertText(selStart, selEnd, word);
            } 
            
            let owner = this;
            setTimeout(function () {
                owner._helperHide();
            }, 200);
            
            return true;
        }
        return false;
    },
    _helperVisible: function () {
        return (this._helper != false);
    },
    _helperShow: function () {
        if (this._helper == false) {
            this._helper = document.createElement('div');
            this._helper.classList.add('script-editor-helper');
            
            let helper = this._helper;
            
            /* Добавляем списко ключевых фраз */
            this._options.keywords.forEach(function (item) {
                let div = document.createElement('div');
                div.classList.add('script-editor-helper-item');
                div.classList.add('keyword');
                div.setAttribute('data-type', 'keyword');
                div.setAttribute('data-word', item);
                div.innerHTML = '<span class="strong keyword">' + item + '<span>';
                helper.appendChild(div);
            });
            
            /* Добавляем список функций */
            this._options.functions.forEach(function (item) {
                let div = document.createElement('div');
                div.classList.add('script-editor-helper-item');
                div.classList.add('function');
                div.setAttribute('data-type', 'function');
                div.setAttribute('data-word', item.name);
                div.innerHTML = '<span class="strong function">' + item.name + '</span> <span class="description">' + item.description + '</span>';
                helper.appendChild(div);
            });
            
            /* Добавляем список строк */
            this._options.strings.forEach(function (item) {
                let div = document.createElement('div');
                div.classList.add('script-editor-helper-item');
                div.classList.add('string');
                div.setAttribute('data-type', 'string');
                div.setAttribute('data-word', item.name);
                div.innerHTML = '<span class="strong string">' + item.name + '</span> <span class="description">' + item.description + '</span>';
                helper.appendChild(div);
            });
            
            let owner = this;
            this._helper.querySelectorAll('div').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    owner._helper.querySelectorAll('div.active').forEach(function (div) {
                        div.classList.remove('active');
                    });
                    item.classList.add('active');
                    let event = new Event('keydown');
                    event.code = 'Enter';
                    owner._editor.dispatchEvent(event);
                });
            });
            
            document.body.appendChild(this._helper);
            this._helperUpdate();
        }
    },
    _helperHide: function () {
        if (this._helper == false) return ;
        document.body.removeChild(this._helper);
        this._helper = false;
    },
    _helperUpdate: function () {
        if (this._helper == false) return ;
        
        let selStart = this._editor.selectionStart;
        let text = this._editor.value;
        let text_before = text.substr(0, selStart);
        let a_before = text_before.split(/\r?\n/);
        let cursor_x = 0;
        let cursor_y = a_before.length;
        
        this._helper_text = '';
        /* Определяем что у нас в той же строке перед курсором */
        if (a_before.length) { 
            cursor_x = a_before[a_before.length - 1].length;
            let line = a_before[a_before.length - 1];
            let line_parts = this._splitSource(line);
            
            if (line_parts.length > 0) {
                let last_part = line_parts[line_parts.length - 1];
                cursor_x -= last_part.length; // Выравниваем всплывайку на начало фрагмента
                this._helper_text = last_part; // Вносим фрагмент для фильтрации
            }
        }
        
        if (this._helper_text == '') {
            this._helper.querySelectorAll('div').forEach(function (item) {
                item.style.display = 'block';
            });
        } else {
            if (this._helper_text[0] == "'") {
                let helper_text = this._helper_text;
                this._helper.querySelectorAll('div').forEach(function (item) {                    
                    if (item.getAttribute('data-type') == 'string') {
                        let word = "'" + item.getAttribute('data-word');
                        if (helper_text == word.substr(0, helper_text.length)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                let owner = this;
                let helper_text = this._helper_text;
                this._helper.querySelectorAll('div').forEach(function (item) {
                    if (item.getAttribute('data-type') != 'string') {
                        let word = item.getAttribute('data-word');
                        if (owner._separators.indexOf(owner._helper_text) > -1 ||
                            helper_text == word.substr(0, helper_text.length)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });               
            }
        }
        
        this._helperScrollToVisible();
        
        let top = parseInt(this._editorStyle.paddingTop) + (cursor_y * this._charSize.h);
        let left = parseInt(this._editorStyle.paddingLeft) + (cursor_x * this._charSize.w);
        
        top -= this._editor.scrollTop;
        left -= this._editor.scrollLeft;
        
        let editor = this._editor.getBoundingClientRect();
        
        left += editor.left;
        top += editor.top;
        
        let right = left + this._helper.offsetWidth;
        let bottom = top + this._helper.offsetHeight;
        
        if (right > window.innerWidth) {
            left -= right - window.innerWidth;
        }
        
        if (bottom > window.innerHeight) {
            top -= this._helper.offsetHeight;
            top -= this._charSize.h;
            top -= parseInt(this._editorStyle.paddingTop) * 2 + 2;
        }
        
        this._helper.style.left = left + 'px';
        this._helper.style.top = top + 'px';
    },
    _helperScrollToVisible: function () {
        let padding = this._helper.style.paddingTop;
        let active = this._helper.querySelector('div.active');
        if (active) {
            if (active.offsetTop - padding < this._helper.scrollTop) {
                this._helper.scrollTop = active.offsetTop - padding;
            } else {
                let item_bottom = active.offsetTop + active.offsetHeight;
                if (item_bottom > this._helper.scrollTop + this._helper.clientHeight - padding) {
                    this._helper.scrollTop = item_bottom - this._helper.clientHeight + padding;
                }
            }
        }
    },
}