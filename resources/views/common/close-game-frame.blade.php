<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <title>screen test</title>
</head>
<body style="background-color: black">
    <script>
        if (window.self !== window.top) {
            parent.postMessage("closeGame", "*");
        } else {
            location.href = '/';
        }
    </script>
</body>
</html>
