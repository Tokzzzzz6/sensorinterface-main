
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#153653">

    <title>Offline - Sensor Monitor</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f2f5fa;
            font-family: Arial, sans-serif;
        }

        .offline-card {
            max-width: 480px;
            padding: 40px;
            border-radius: 20px;
            background: white;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
    </style>
</head>
<body>
    <main class="offline-card">
        <h1 class="mb-3">You're offline</h1>

        <p class="text-secondary">
            The sensor dashboard needs a network connection to retrieve
            the latest readings from the ESP32 and Laravel server.
        </p>

        <p class="text-secondary">
            Reconnect to your network and try again.
        </p>

        <button class="btn btn-primary" onclick="location.reload()">
            Try Again
        </button>
    </main>
</body>
</html>