<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session(); //ivm user/guest gegevens

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

if (!empty($_POST)) { //nadat het formulier is ingevuld vindt de validatie plaats (evt foutmeldingen worden in het formulier getoond)
    $validate = new Formvalidation($_POST, "actionpage.php?action=change"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $textErr = $validate->textErr;
    $emailErr = $validate->emailErr;
    $passwordErr = $validate->passwordErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        header($validate->get_headerString()); //bij geen foutmelding header naar actionpage.php
        exit;
    }
} else { //als het formulier nog niet is ingevuld:
    $textErr = $emailErr = $passwordErr = ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
}

//redirect vanaf de actionpage.php:
if (isset($_GET['action']) && $_GET['action'] == 'usernamealreadyexists') {
    $textErr = "Username already in use. Please choose another username.";
} elseif (isset($_GET['action']) && $_GET['action'] == 'wrongpassword') {
    $passwordErr = "Invalid password! Fill in your password and try again.";
}
?>
<!DOCTYPE html>
<html>

<body>
    <h2>Change account:</h2>
    <p>Check your account in the pre-filled form below and fill in the changes you want to make.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="text">Username:</label><br>
        <input type="text" id="text" name="text" value="<?php if (isset($_GET['action']) && $_GET['action'] == 'usernamealreadyexists') {
                                                            echo ""; //oftewel als de nieuwe username al wordt gebruikt (in de database) wordt na de redirect vanaf changeaccount2 een leeg veld getoond
                                                        } else if (isset($_GET['action']) && $_GET['action'] == 'wrongpassword') {
                                                            echo $_SESSION["newusername"]; //oftewel wanneer het wachtwoord verkeerd is, hoeft de nieuwe gebruikersnaam niet opnieuw ingevuld te worden
                                                        } else {
                                                            echo $_SESSION["username"]; //oftewel wanneer het reguliere pad wordt gevolgd (geen redirect vanaf changeaccount2), dan wordt de sessie die bij de inlog was ingevuld hier opnieuw ingevuld
                                                        } ?>" maxlength="55" autofocus required>
        <span class="error"> <?php echo $textErr; ?></span><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $_SESSION["email"]; ?>" maxlength="55" required>
        <span class="error"> <?php echo $emailErr; ?></span><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" maxlength="55" required>
        <span class="error"> <?php echo $passwordErr; ?></span><br>
        <input type="submit" value="Submit">
    </form>

    <h3>Change password:</h3>
    <Change>
        <form action="/reserveerSQL/changepassword.php" method="post">
            <label for="haveaccount"> If you want to change your password, click </label>
            <input type="submit" value="Change">
        </form>

</body>

</html>