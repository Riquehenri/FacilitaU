<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - <?php echo isset($page_title) ? $page_title : 'Sistema'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        nav {
            margin: 10px 0;
        }
        nav a {
            color: #fff;
            margin: 0 10px;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            text-align: center;
        }
        form {
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }
        form label {
            display: block;
            margin: 5px 0;
        }
        form input, form select, form textarea {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }
        form textarea {
            resize: vertical;
        }
        form button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <h1>FacilitaU</h1>
        <nav>
            <?php
            session_start();
            if (isset($_SESSION['usuario_id'])) {
                echo '<a href="menu_' . $_SESSION['tipo'] . '.php">Menu</a>';
                echo '<a href="logout.php">Sair</a>';
            } else {
                echo '<a href="index.php">Login</a>';
            }
            ?>
        </nav>
    </header>
    <div class="container">