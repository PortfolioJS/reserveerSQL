<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_formvalidation.php";

$session = new Session();

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
} elseif ($_SESSION["username"] !== "Admin") { //wanneer de Admin niet is ingelogd (oftewel: alleen Admin heeft toegang tot deze pagina, althans dat is de bedoeling...)
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

if (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'reservationsbetween') { //nadat het formulier is ingevuld vindt de validatie plaats
    $validate = new Formvalidation($_POST, "actionadmin.php?action=reservationsbetween"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $dateErr = $validate->dateErr;
    $timeErr = $validate->timeErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setDateErr($dateErr);
        $session->setTimeErr($timeErr);
        header('Location: /reserveerSQL/admin.php?action=reservationsbetweenwrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'guestsbetween') { //nadat het formulier is ingevuld vindt de validatie plaats
    $validate = new Formvalidation($_POST, "actionadmin.php?action=guestsbetween"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $dateErr = $validate->dateErr;
    $timeErr = $validate->timeErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setDateErr($dateErr);
        $session->setTimeErr($timeErr);
        header('Location: /reserveerSQL/admin.php?action=guestsbetweenwrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'showfreecapacity') { //nadat het formulier is ingevuld vindt de validatie plaats
    $validate = new Formvalidation($_POST, "actionadmin.php?action=showfreecapacity"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $dateErr = $validate->dateErr;
    $timeErr = $validate->timeErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setDateErr($dateErr);
        $session->setTimeErr($timeErr);
        header('Location: /reserveerSQL/admin.php?action=showfreecapacitywrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'showfreetables') { //nadat het formulier is ingevuld vindt de validatie plaats
    $validate = new Formvalidation($_POST, "actionadmin.php?action=showfreetables"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $dateErr = $validate->dateErr;
    $timeErr = $validate->timeErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setDateErr($dateErr);
        $session->setTimeErr($timeErr);
        header('Location: /reserveerSQL/admin.php?action=showfreetableswrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'addtable') {
    $validate = new Formvalidation($_POST, "actionadmin.php?action=addtable"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $quantityErr = $validate->quantityErr;
    //extra validatie voor even aantal Capaciteit tafel
    if ($outputArray['quantity'] % 2 > 0) {
        $validate->error = True;
        $quantityErr[0] = "Only even numbers allowed.";
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=addtablewrong');
        exit;
    }
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=addtablewrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'changetable') {
    $validate = new Formvalidation($_POST, "actionadmin.php?action=changetable"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $quantityErr = $validate->quantityErr;
    //extra validatie voor even aantal Capaciteit tafel:
    if ($outputArray['quantity2'] % 2 > 0) {
        $validate->error = True;
        $quantityErr[1] = "Only even numbers allowed.";
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=changetablewrong');
        exit;
    }
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        $session->setTableID($outputArray['quantity1']);
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=changetablewrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'deletetable') {
    $validate = new Formvalidation($_POST, "actionadmin.php?action=deletetable"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $quantityErr = $validate->quantityErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij naar de formulierpagina om de melding te tonen
        $session->setTableID($outputArray['quantity']);
        header($validate->get_headerString()); //header bij geen foutmelding
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=deletetablewrong');
        exit;
    }
} elseif (!empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'newopeninghours') {
    $validate = new Formvalidation($_POST, "actionadmin.php?action=newopeninghours"); //tweede argument is laatste stukje headerString
    $validate->validateForm();
    $outputArray = $validate->get_outputArray();
    $session->setValidationOutput($outputArray);
    $timeErr = $validate->timeErr;
    $dateErr = $validate->dateErr;
    if ($validate->error === False) { //als er wel een foutmelding is moet hij op de formulierpagina blijven staan om de melding te tonen
        // evt. extra validatie(test) (niet nodig in formulier op Adminpagina):
        // $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        // foreach ($outputArray['checkbox'] as $day)
        //     if (in_array($day, $daysOfWeek, True) === False) {
        //         echo "NOT IN WEEK";
        //     } else {
        //         echo "IN WEEK";
        //     }
        // exit;
        header($validate->get_headerString()); //bij geen foutmelding header naar actionpage.php
        exit;
    } else { //terug naar het formulier met foutmeldingen in de sessie:
        $session->setTimeErr($timeErr);
        $session->setDateErr($dateErr);
        header('Location: /reserveerSQL/admin.php?action=newopeninghourswrong');
        exit;
    }
}
