<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire PHP et tout</title>
    <style>
body {
    font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
    background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h2 {
    text-align: center;
            color: #333;
        }

        label {
    font-size: 16px;
            color: #555;
        }

        input[type="text"] {
        width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
        background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
    background-color: #0056b3;
        }

        .form-group {
    margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Formulaire XAMPP</h2>
    <form action="process.php" method="POST">
        <div class="form-group">
            <label for="depart">Valeur A :</label>
            <input type="text" id="a" name="a" placeholder="Entrez la valeur A" required>
        </div>

        <div class="form-group">
            <label for="arrivee">Valeur B :</label>
            <input type="text" id="b" name="b" placeholder="Entrez la valeur B" required>
        </div>

        <input type="submit" value="Envoyer">
    </form>
</div>

</body>
</html>
