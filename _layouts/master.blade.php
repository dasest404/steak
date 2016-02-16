<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>@section('title') steak @show</title>

    <base href="/steak/">

    <link rel="stylesheet" href="vendor/all.css">
    <link rel="stylesheet" href="assets/site.css">

</head>
<body class="@yield('bodyClass')">

@yield('body')

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="vendor/all.js"></script>
    <script>
        $(document)
                .ready(function() {
                    $('.help.popup').popup();
                })
        ;
    </script>
    @stack('scripts')
</body>
</html>
