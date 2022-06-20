<!DOCTYPE HTML>
<html lang="{{ app('translator')->getLocale() }}">
    <head>
        <title>WATCH HOUSE</title>
        <link rel="shortcut icon" href="/favicon.ico"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <script src="/js/jquery-3.5.1.min.js"></script>
        <script src="/js/bootstrap.js"></script>
        @yield('head')
    </head>
<body>
    @yield('body')
    <script>
        @if(isset($_GET['api_token']))
        setCookie('api_token', "{{ $_GET['api_token'] }}");
        @endif
        
        function getCookie(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value) {
            document.cookie = name + '=' + value + '; path=/; max-age=360000';
        }
    </script>
</body>
</html>