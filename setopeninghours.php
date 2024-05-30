<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";

$session = new Session();

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

$validationOutput =  $_SESSION["ValidationOutput"];

$days = $validationOutput['checkbox'];
$newOpen = $validationOutput['time1'];
$newClosed = $validationOutput['time2'];
$startDate = $validationOutput['date'];

if ($days[0] !== NULL) {
    if (empty($validationOutput['radio'])) {

        $dayname = $days[0]; //de dag wordt uit de checkboxsubarray van de validatieoutput gehaald en daarna hieronder gewist...

        array_splice($validationOutput['checkbox'], 0, 1); //...zodat de volgende dag in de checkboxarray bij de volgende iteratie aan de beurt is...

        $session->setValidationOutput($validationOutput); //...en in de sessie gezet voor de volgende iteratie
        $db->setNewOpeningHours($dayname, $startDate, $newOpen, $newClosed); //met header naar setopeninghours.php voor volgende iteratie
    } else { //als de radio is aangeklikt is de betreffende dag de boel gesloten (vanaf de Startdate staan Open en Closed op NULL)
        $dayname = $days[0]; //de dag wordt uit de checkboxsubarray van de validatieoutput gehaald en daarna hieronder gewist...

        array_splice($validationOutput['checkbox'], 0, 1); //...zodat de volgende dag in de checkboxarray bij de volgende iteratie aan de beurt is...

        $session->setValidationOutput($validationOutput); //...en in de sessie gezet voor de volgende iteratie
        $db->setNewOpeningHours($dayname, $startDate, `NULL`, `NULL`); //met header naar setopeninghours.php voor volgende iteratie
    }
} else {
    header('Location: /reserveerSQL/openinghours.php?action=newopeninghoursset');
    exit;
}
