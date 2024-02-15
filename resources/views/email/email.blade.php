<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
</head>

<body>
    <h1>歡迎註冊我們的網站</h1>
    <p>Hello {{ $userData }}</p>
    <p>您的驗證碼為 {{ $code }}</p>
    <a href="http://127.0.0.1:8000/api/verify/{{ $userId }}">連結</a>
    <p>請在30分鐘內完成驗證</p>
</body>

</html>
