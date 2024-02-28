<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de Vérification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .verification-code {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 400px;
            text-align: center;
        }

        .verification-code h2 {
            margin-bottom: 20px;
        }

        .verification-code p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .verification-code .code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .verification-code .note {
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="verification-code">
        <h2>Code de Vérification</h2>
        <p>Votre code de vérification est :</p>
        <div class="code">{{$code}}</div>
        <p class="note">Ce code expire dans une heure (1H).</p>
    </div>

</body>
</html>
