<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <script src="/js/jquery-3.5.1.min.js"></script>
    <script src="/js/bootstrap.js"></script>
    
    <link rel="stylesheet" href="/css/script-editor.css">
    <script src="/js/script-editor.js"></script>
</head>
<body>
    <div id="scriptEditor" style="display: inline-block; width: 100%; height: 100%; padding: 200px; background-color: #eeeeee;"></div>
</body>
    
<script>
    $(document).ready(function () {
        let ctx = document.getElementById('scriptEditor');
        let scriptEditor = new ScriptEditor(ctx, {
            keywords: [
                'namespace',
                'use',
                'public',
                'private',
                'function',
                'class',
                'try',
                'catch',
                'finnal',
                'return',
                'extends',
                'new',
                'foreach',
                'for',
                'if',
                'else',
                'switch',
                'case',
                'default',
                'break',
                'while',
                'isset',
                'include',
            ],
            functions: [
                {name: 'get', description: 'function get(name)'},
                {name: 'set', description: 'function set(name, value, later = 0)'},
                {name: 'on', description: 'function on(name, later = 0)'},
                {name: 'off', description: 'function off(name, later = 0)'},
                {name: 'toggle', description: 'function toggle(name, later = 0)'},
                {name: 'speech', description: 'function speech(prase)'},
                {name: 'play', description: 'function play(media)'},
                {name: 'info', description: 'function info()'},
            ],
            strings: [
                {name: 'ALL', description: 'ALL'},
                {name: 'ITEM 1', description: ''},
                {name: 'ITEM 2', description: ''},
                {name: 'ITEM 3', description: ''},
                {name: 'ITEM 4', description: ''},
            ],
        });
        scriptEditor.readOnly(true);
    });
</script>    
</html>