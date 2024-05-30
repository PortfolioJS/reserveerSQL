<?php

require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";
require_once __DIR__ . "/classes/class_session.php";


$session = new Session();

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

$reservationID = $_SESSION["ReservationID"];

$startDateTime = $_SESSION['StartDateTime'];

$endDateTime = $_SESSION['EndDateTime'];

$toReservedTables = $_SESSION["ReservationTables"];

if ($toReservedTables[0] != NULL) { //kan misschien ook: if ($toReservedTables != False) of: if (!empty($toReservedTables))

    $tableID = $toReservedTables[0]['TableID'];

    array_splice($toReservedTables, 0, 1); //zodat de volgende tafel in de array bij de volgende iteratie aan de beurt is

    $session->setReservationTables($toReservedTables);

    $db->insertReservedTable($reservationID, $tableID, $startDateTime, $endDateTime); //methode bevat header die weer verwijst naar toreservedtables.php (voor de volgende iteratie van deze if-else tot de $toReservedTables array leeg is)
} else {
    header('Location: /reserveerSQL/myaccount.php?action=reserve');
    exit;
}
