<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";

$session = new Session();

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

$newOpeningHours = $_SESSION["NewOpeningHours"];

if ($newOpeningHours[0] != NULL) {
    $day = $newOpeningHours[0]['DayOfWeek'];
    $open = $newOpeningHours[0]['NewOpen'];
    $closed = $newOpeningHours[0]['NewClosed'];

    array_splice($newOpeningHours, 0, 1); //zodat de volgende dag in de array bij de volgende iteratie aan de beurt is

    $session->setNewOpeningHours($newOpeningHours);

    $db->resetOpeningHours($day, $open, $closed, NULL, NULL, NULL);
} else {
    header('Location: /reserveerSQL/actionpage.php?action=reserve'); //het (automatisch) updaten van de openingstijden (na de expiratiedatum van de oude openingstijden) gebeurt (en passant) tijdens het reserveren; als alle openingstijden zijn ge-updatet wordt het reserveringsproces vervolgd.
    exit;
}
