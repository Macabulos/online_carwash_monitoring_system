<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <link rel="stylesheet" href="css/error.css">
</head>
<body>
    <div class="imag"></div>
    <button class="back-button" onclick="goBack()">Go Back To Page</button>

    <script>
        function goBack() {
            window.location.href = 'index.php'; // Change this URL to your actual dashboard path
        }
    </script>
</body>
</html>
