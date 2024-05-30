<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session(); //ivm user/guest gegevens

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=create"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $textErr = $validate->textErr;
    $emailErr = $validate->emailErr;
    $newPasswordErr = $validate->newPasswordErr;
    $confirmNewPasswordErr = $validate->confirmNewPasswordErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        header($validate->get_headerString()); //bij geen foutmelding header naar actionpage.php
        exit;
    }
} else { //als het formulier nog niet is ingevuld:
    $textErr = $emailErr = $newPasswordErr = $confirmNewPasswordErr = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

//redirect vanaf de actionpage.php:
if (isset($_GET['action']) && $_GET['action'] == 'usernamealreadyexists') {
    $textErr = "Username already in use. Please choose another username.";
}


?>
<!DOCTYPE html>
<html>

<body>
    <h1>Make account:</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="text">Username:</label><br>
        <input type="text" id="text" name="text" maxlength="55" autofocus required>
        <span class="error"> <?php echo $textErr; ?></span><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" maxlength="55" required>
        <span class="error"> <?php echo $emailErr; ?></span><br>
        <label for="newpassword">Password:</label><br>
        <input type="password" id="newpassword" name="newpassword" maxlength="55" required>
        <span class="error"> <?php echo $newPasswordErr; ?></span><br>
        <label for="confirmnewpassword">Confirm password:</label><br>
        <input type="password" id="confirmnewpassword" name="confirmnewpassword" maxlength="55" required>
        <span class="error"> <?php echo $confirmNewPasswordErr; ?></span><br>
        <input type="submit" value="Submit">
    </form>
</body>

</html>