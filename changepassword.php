<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session(); //ivm user/guest gegevens

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=changep"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $passwordErr = $validate->passwordErr;
    $newPasswordErr = $validate->newPasswordErr;
    $confirmNewPasswordErr = $validate->confirmNewPasswordErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        header($validate->get_headerString()); //bij geen foutmelding header naar actionpage.php
        exit;
    }
} else { //als het formulier nog niet is ingevuld:
    $passwordErr = $newPasswordErr = $confirmNewPasswordErr = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

//redirect vanaf de actionpage.php:
if (isset($_GET['action']) && $_GET['action'] == 'wrongpassword') {
    $passwordErr = "Your (current) password is incorrect. Try again.";
}
?>
<!DOCTYPE html>
<html>


<body>
    <h2>Change password:</h2>
    <p>If you want to change your password: fill in the form below.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="password">Old password:</label><br>
        <input type="password" id="password" name="password" maxlength="55" autofocus required>
        <span class="error"> <?php echo $passwordErr; ?></span><br>
        <label for="newpassword">New password:</label><br>
        <input type="password" id="newpassword" name="newpassword" maxlength="55" required>
        <span class="error"> <?php echo $newPasswordErr; ?></span><br>
        <label for="confirmnewpassword">Confirm new password:</label><br>
        <input type="password" id="confirmnewpassword" name="confirmnewpassword" maxlength="55" required>
        <span class="error"> <?php echo $confirmNewPasswordErr; ?></span><br>
        <input type="submit" value="Submit">
    </form>

</body>

</html>