<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session(); //ivm user/guest gegevens

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=login"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $textErr = $validate->textErr;
    $passwordErr = $validate->passwordErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        header($validate->get_headerString()); //bij geen foutmelding header naar actionpage.php
        exit;
    } else if ($validate->error === True) { //bij een foutmelding checkt hij of de checkbox is aangevinkt, zo ja: naar makeaccount.php
        foreach ($outputArray as $key => $value) {
            if (str_contains($key, 'radio')) {
                if ($outputArray['radio'] === 'noaccount') {
                    header('Location: /reserveerSQL/makeaccount.php');
                    exit;
                } else if ($outputArray['radio'] === 'forgotpassword') {
                    header('Location: /reserveerSQL/passwordreset.php'); //LET OP: de betreffende pagina passwordreset.php bestaat nog niet
                    exit;
                }
            }
        } //zo, nee (radiobutton is niet aangevinkt): dan wordt de betreffende foutmelding getoond in het formulier
    }
} else { //als het formulier nog niet is ingevuld:
    $textErr = $passwordErr = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

if (isset($_GET['action']) && $_GET['action'] == 'fail') {
    $textErr = "Invalid username and/or password!";
}

?>

<!DOCTYPE html>
<html>

<body>
    <h1>Login to make a reservation</h1>
    <p>Don't have an account? Check the box below.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="text">Username:</label><br>
        <input type="text" id="text" name="text" maxlength="55" autofocus>
        <span class="error"> <?php echo $textErr; ?></span><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" maxlength="55">
        <span class="error"> <?php echo $passwordErr; ?></span><br>
        <input type="radio" id="radio" name="radio" value="noaccount">
        <label for="radio"> I don't have an account.</label><br>
        <input type="radio" id="radio" name="radio" value="forgotpassword">
        <label for="radio"> I forgot my password.</label><br>
        <input type="submit" value="Submit">
    </form>
</body>

</html>