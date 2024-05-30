<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session(); //ivm user/guest gegevens

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=reserve"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $dateErr = $validate->dateErr;
    $timeErr = $validate->timeErr;
    $quantityErr = $validate->quantityErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } // else niet nodig: het formulier wordt getoond met de foutmelding(en)
} else if (isset($_GET['action']) && $_GET['action'] == 'wrongdatetime') { //additionele validatie vanuit actionpage.php?action=reserve (had ook in class_formvalidation gekund, maar die blijft op deze wijze generiek zodat bijvoorbeeld de Admin WEL op oude datetimes kan zoeken)
    $dateErr[0] = "Reservations in the past are not possible. Check if the date (or time) is valid.";
    $timeErr[0] = "(Fill in time in 24-hour format: 1.00 pm should be 13.00.)";
    $quantityErr[0] = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
} else { //als het formulier nog niet is ingevuld:
    $dateErr[0] = $timeErr[0] = $quantityErr[0] = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

?>
<html>

<body>
    <h1>Make a reservation</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="date">Which date? </label>
        <input type="date" id="date" name="date" value="<?php if (isset($_GET['action']) && $_GET['action'] == 'wrongdatetime') {
                                                            echo $_SESSION["date"];
                                                        } else {
                                                            echo ""; //autoaanvullen wordt niet voor de reguliere validatie gebruikt
                                                        } ?>">
        <span class="error"> <?php echo $dateErr[0]; ?></span><br>
        <label for="time">Which time? </label>
        <input type="time" id="time" name="time" value="<?php if (isset($_GET['action']) && $_GET['action'] == 'wrongdatetime') {
                                                            echo $_SESSION["time"];
                                                        } else {
                                                            echo ""; //autoaanvullen wordt niet voor de reguliere validatie gebruikt
                                                        } ?>">
        <span class="error"> <?php echo $timeErr[0]; ?></span><br>
        <label for="quantity">How many guests?</label>
        <input type="number" id="quantity" name="quantity" value="<?php if (isset($_GET['action']) && $_GET['action'] == 'wrongdatetime') {
                                                                        echo $_SESSION["quantity"];
                                                                    } else {
                                                                        echo ""; //autoaanvullen wordt niet voor de reguliere validatie gebruikt
                                                                    } ?>" min="1" max="40">
        <span class="error"> <?php echo $quantityErr[0]; ?></span><br>
        <input type="submit" value="Confirm">
    </form>
</body>