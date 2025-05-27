<?php
session_start();
include 'config.php'; // Include your database connection
include 'header.php'; // Include your header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    <link rel="stylesheet" href="CSS/calendar.css">
</head>
<body>
    <h2>Event Calendar</h2>
    <div id="calendar"></div>
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add Event</h3>
            <form id="eventForm">
                <input type="hidden" id="eventDate" name="eventDate">
                <label for="eventTitle">Event Title:</label>
                <input type="text" id="eventTitle" name="eventTitle" required>
                <button type="submit">Add Event</button>
            </form>
        </div>
    </div>

    <script src="JS/calendar.js"></script>
</body>
</html>