<?php
session_start();
include 'config.php'; // Include your database configuration if needed
include 'header.php'; // Include your header if needed
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Eventos</title>
    <link rel="stylesheet" href="CSS/calendar.css">
</head>
<body>
    <div class="container">
        <h2>Calendário de Eventos</h2>
        <div id="calendar"></div>
        <div id="eventModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Adicionar Evento</h3>
                <form id="eventForm">
                    <label for="eventTitle">Título do Evento:</label>
                    <input type="text" id="eventTitle" required>
                    <input type="hidden" id="eventDate">
                    <button type="submit">Salvar Evento</button>
                </form>
            </div>
        </div>
    </div>

    <script src="JS/calendar.js"></script>
</body>
</html>