<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session();

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

echo "<h3>My current reservations:</h3>";

if ($_SESSION["MyCurrentReservations"] === NULL) {
    echo "There are no current reservations found.";
} else {

    $myCurrentReservations = $_SESSION["MyCurrentReservations"]; //voor het geval dat Guest een reservering wil cancellen

    foreach ($myCurrentReservations as $reservation) {
        echo "Reservation nr.: " . $reservation['ReservationID'];
        echo "<br>";
        echo "Date and time of reservation: ";
        $format = "d-m-Y H:i";
        date_create_immutable_from_format($format, $reservation['StartDateTime']);
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $reservation['StartDateTime']);
        echo $date->format('d-m-Y H:i');
        echo "<br>";
        echo "Total number of guests: " . $reservation['NumberOfGuests'];
        echo "<br>";
        echo "<br>";
    }
}

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=cancel"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $quantityErr = $validate->quantityErr; //de standaard foutmelding (hieronder staat ook nog een specifiekere)
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        $count = 0;
        foreach ($myCurrentReservations as $reservation) {
            $count += 1;
            if ($reservation['ReservationID'] == $outputArray['quantity']) { //je wilt geen reserveringen van andere gasten verwijderen
                header($validate->get_headerString()); //header bij geen foutmelding
                exit;
            } elseif ($count == count($myCurrentReservations)) { //als er aan het eind van de array geen match is met een geldig ReservationID:
                $quantityErr = "The number is invalid. Please check the number of the reservation you want to cancel and try again.";
            }
        }
    } // else niet nodig: het formulier wordt getoond met de foutmelding(en)
} else { //als het formulier nog niet is ingevuld:
    $quantityErr = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

?>

<html>

<body>
    <h2>Cancel reservation:</h2>
    <p>Fill in the reservation number of the reservation you want to cancel.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="number" id="quantity" name="quantity" min="1" max="10000">
        <span class="error"> <?php echo $quantityErr; ?></span><br>
        <input type="submit" value="Submit">
    </form>
</body>

</html>