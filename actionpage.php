<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";
require_once __DIR__ . "/classes/class_reservation.php";

$session = new Session();

$validationOutput = $_SESSION["ValidationOutput"];

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

if (isset($_GET['action']) && $_GET['action'] == 'login') {

    $username = $validationOutput["text"];
    $password = $validationOutput["password"];

    $user = $db->fetchUser($username);

    if ($user && password_verify($password, $user['Password'])) {
        $session->setLogin($username);
        if ($_SESSION["username"] === "Admin") {
            header('Location: /reserveerSQL/admin.php');
            exit;
        } else {
            header('Location: /reserveerSQL/myaccount.php?action=login');
            exit;
        }
    } else {
        header('Location: /reserveerSQL?action=fail');
        exit;
    }
    // } elseif (isset($_GET['action']) && $_GET['action'] == 'admin') {
} elseif (isset($_GET['action']) && $_GET['action'] == 'create') {

    $username = $validationOutput["text"];
    $email = $validationOutput["email"];
    $password = $validationOutput["newpassword"];
    // $confirmPewpassword = $validationOutput["confirmnewpassword"];//niet meer nodig: is in de validatie al gecheckt

    $user = $db->fetchUser($username); //eerst wordt gecheckt of de (new)$username al bestaat in de database...

    //... als de $username al bestaat: redirect naar makeaccount.php + melding 'choose another username')
    if ($user == True) {
        header('Location: /reserveerSQL/makeaccount.php?action=usernamealreadyexists');
        exit;
    } else {
        $session->setLogin($username);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $db->insertUser($username, $email, $passwordHash);
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'change') {
    $username = $_SESSION["username"]; //is al eerder bij de inlog gevalideerd (dus kan zo richting database)

    $user = $db->fetchUser($username);

    $id = $user['GuestID']; //nodig voor update username/email en ook (!) om te checken of de nieuwe $username al elders in de database staat (zie hieronder)

    $newusername = $validationOutput["text"];
    $email = $validationOutput["email"];
    $password = $validationOutput["password"];

    $session->setEmail($email); //nodig voor autoaanvullen bij redirect naar changeaccount

    $user = $db->fetchUser($newusername); //eerst wordt gecheckt of de (new)$username al bestaat in de database...

    //... als de $username al bestaat (i.c.m. een ANDER $user['id'] dan het $id, want als $user alleen het emailadres wil veranderen, willen we geen foutmelding): redirect naar changeaccount.php + melding 'choose another username')
    if ($user == True && $id != $user['GuestID']) {
        header('Location: /reserveerSQL/changeaccount.php?action=usernamealreadyexists');
        exit;
    } else if ($user == True && $id == $user['GuestID']) {
        if (password_verify($password, $user['Password'])) { //als het wachtwoord klopt:
            //de (resterende) input wordt hier in de sessie gezet:
            // $session->setLogin($username);//HIER NIET NODIG: de nieuwe $username is dezelfde als de bestaande in de session

            //de gegevens worden in de database geüpdatet (aan de hand van het $id):
            $db->updateUser($newusername, $email, $id);
        } else {
            $_SESSION["newusername"] = $newusername; // is nodig voor autoaanvullen changeaccount
            header('Location: /reserveerSQL/changeaccount.php?action=wrongpassword');
            exit;
        }
    } else { //als de nieuwe gebruikersnaam niet bestaat wordt de array $user opgehaald aan de hand van het $id:
        $user = $db->fetchUserviaID($id);

        if (password_verify($password, $user['Password'])) { //als het wachtwoord klopt:
            //de (resterende) input wordt hier in de sessie gezet:
            $session->setLogin($newusername); //de nieuwe $username

            //de gegevens worden in de database geüpdatet (aan de hand van het $id):
            $db->updateUser($newusername, $email, $id);
        } else {
            $_SESSION["newusername"] = $newusername; // is nodig voor autoaanvullen changeaccount
            header('Location: /reserveerSQL/changeaccount.php?action=wrongpassword');
            exit;
        }
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'changep') {
    $username = $_SESSION["username"]; //is al eerder bij de inlog gevalideerd (dus kan zo richting database)

    $user = $db->fetchUser($username);

    $id = $user['GuestID']; //nodig voor update password (zie hieronder)

    $oldPassword = $validationOutput["password"];
    $newPassword = $validationOutput["newpassword"];
    $confirmNewPassword = $validationOutput["confirmnewpassword"];

    if (password_verify($oldPassword, $user['Password'])) { //als het wachtwoord klopt:
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $db->updatePassword($newPasswordHash, $id);
    } else {
        header('Location: /reserveerSQL/changepassword.php?action=wrongpassword');
        exit;
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'reserve') {
    $date = $validationOutput['date'];
    $startTime = $validationOutput['time'];
    $numberOfGuests = $validationOutput['quantity'];

    //$startDateTime kwam binnen via formulier en moet na de validatie in de juiste volgorde gezet worden (zie hieronder)
    $startDateTime = $date . " " . $startTime; //let op volgorde "y-m-d" (ivm communicatie database)

    $now = date("Y-m-d H:i:s"); //zodat alleen reserveringen in de toekomst worden doorgezet:

    if ($startDateTime < $now) { //zodat alleen reserveringen in de toekomst worden doorgezet
        $_SESSION["date"] = $date; //geen methode voor gemaakt in class_Session
        $_SESSION["time"] = $startTime; //idem
        $_SESSION["quantity"] = $numberOfGuests; //idem
        header('Location: /reserveerSQL/makereservation.php?action=wrongdatetime');
        exit;
    }

    $guestID = $_SESSION["GuestID"];

    $nReservation = new Reservation($startDateTime, $guestID, $numberOfGuests);
    //LET OP format("y-m-d"): anders gaat het niet goed in de communicatie met de database:
    $endDateTime = $nReservation->getEndDateTime()->format("y-m-d") . " " . $nReservation->getEndDateTime()->format("H:i:s");

    //eerst wordt gecheckt of tijdstip van reservering overeenkomt met de openingstijden:
    $startTime = $nReservation->getStartDateTime()->format("H:i:s"); //seconden toegevoegd ivm vergelijking: bijv. $startTime < $openingHours['Open'] : 17:00 < 17:00:00 (ipv 17:00 = 17:00:00, waardoor een reservering op openingstijd zonder seconden mis zou gaan)
    //omdat ook de max. duur van het restaurantbezoek (in class_Reservation ingesteld op 119 minuten) wordt meegenomen bij het checken van de openingstijden - om te voorkomen dat bijvoorbeeld een reservering wordt geplaatst 1 minuut voor sluitingstijd:
    $endTime = $nReservation->getEndDateTime()->format("H:i:s");

    list($year, $month, $day) = explode('-', $date); //om te checken welke dag het is: de datum wordt hieronder omgezet...

    $calendar = CAL_GREGORIAN;

    $jd = cal_to_jd($calendar, $month, $day, $year); //...in Julian Days...

    $checkDay = cal_from_jd($jd, $calendar); //...en weer teruggezet in een kalenderdatum met extra info in de array, zoals: dag van de week

    $dayName = $checkDay['dayname'];

    //de openingstijden van de betreffende dag worden uit de $db gehaald:
    $openingHours = $db->fetchOpeningHours($dayName);

    if ($openingHours['Startdate'] !== NULL) { //als er een startdatum is aangegeven (oftewel op die datum gaan de nieuwe openingstijden in):
        if ($date >= $openingHours['Startdate']) {
            //LET OP: deze eerste if heeft op zich met de reservering niks te maken (maar dit is volgens mij wel een geschikte plek voor deze functionaliteit: updaten (resetten) openingstijden datebase na startdatum nieuwe openingstijden)
            //N.B.: het updaten van de nieuwe openingstijden gebeurt alleen als de dag van de reservering overeenkomt met een dag die nieuwe openingstijden heeft 'openstaan' ('Startdate' !== NULL en <= $now) en dan worden meteen alle nieuwe openingstijden met een verstreken ingangsdatum meegepakt.
            if ($openingHours['Startdate'] <= $now) { //als de nieuwe openingstijden zijn ingegaan, kan de Tabel openinghours automatisch worden ge-updatet (als het mogelijk/nodig is voor de hele week, zie: setnewopeninghours.php):

                $newOpeningHours = $db->fetchAllNewOpeningHours($now);

                $session->setNewOpeningHours($newOpeningHours);

                header('Location: /reserveerSQL/resetopeninghours.php');
                exit; //nadat de openingstijden automatisch zijn aangepast staat $openingHours['Startdate'] === NULL, waarna het reserveringsproces via de header op resetopeningshours.php op deze pagina wordt voortgezet (hieronder: else { //$openingHours['Startdate'] === NULL)
            }
            //als de startdatum nog NIET is gepasseerd worden hieronder reserveringen voor na de startdatum afgehandeld
            if ($openingHours['NewOpen'] === NULL && $openingHours['NewClosed'] === NULL) { //in principe zou een NULL genoeg moeten zijn
                $_SESSION["DayName"] = $dayName; //geen methode voor in class Session
                header('Location: /reserveerSQL/openinghours.php?action=dayclosed');
                exit;
            } elseif ($startTime < $openingHours['NewOpen'] or $startTime > $openingHours['NewClosed'] or $endTime < $openingHours['NewOpen'] or $endTime > $openingHours['NewClosed']) {
                header('Location: /reserveerSQL/openinghours.php?action=timeclosed');
                exit;
            }
        }
    } else { //$openingHours['Startdate'] === NULL, oftewel alleen de standaard openingstijden staan in de database (al dan niet na bovenstaande reset)
        if ($openingHours['Open'] === NULL && $openingHours['Closed'] === NULL) { //in principe zou een NULL genoeg moeten zijn
            $_SESSION["DayName"] = $dayName; //geen methode voor in class Session
            header('Location: /reserveerSQL/openinghours.php?action=dayclosed');
            exit;
        } elseif ($startTime < $openingHours['Open'] or $startTime > $openingHours['Closed'] or $endTime < $openingHours['Open'] or $endTime > $openingHours['Closed']) {
            header('Location: /reserveerSQL/openinghours.php?action=timeclosed');
            exit;
        }
    }

    //Behorend bij functie makeReservation():
    $toReservedTables = []; //staat buiten de functie, omdat de functie zichzelf kan aanroepen; in dat geval zou hij de array weer leeghalen
    $secondIteration = False;

    function makeReservation($rest, $freeTables)
    {
        global $toReservedTables;
        global $secondIteration;
        $index = 0;

        if ($rest % 2 != 0) {
            $rest += 1; //zodat ook als $rest oneven is, hieronder de laatste tafel wordt gepakt (alle tafels hebben een even Capacity is de aanname/verwachting; in een restaurant met bijv. ook drie- en vijfpersoonstafels kan deze if else weg, maar dan moet er voor $rest = 1 nog steeds worden gecorrigeerd - tenzij er ook eenpersoonstafels in dit restaurant staan)
        }

        foreach ($freeTables as $table) {
            $index += 1;
            if ($rest == 0) {
                break;
            } else if ($rest > $table['Capacity']) {
                $toReservedTables[] = $table; //de tafel wordt in de array gezet met ID's die moeten worden geloopt door de insertReservedTable() methode LET OP: in die methode verwijst de header naar de pagina met deze loop: toreservedtables.php
                $rest -= $table['Capacity'];
            } else if ($rest === $table['Capacity']) {
                $toReservedTables[] = $table; //idem
                $rest = $rest - $rest;
                break;
            } else if ($index == count($freeTables) && $secondIteration === False) { // aan het eind van de array: als $rest < blijkt dan capaciteit v. alle tafels in de array: probeer dezelfde loop met de array achterstevoren (misschien past het dan wel precies - niet in elk scenario - bijvoorbeeld bij reservering van 8 personen pakt makeReservation() eerst een tafel van 6; als er geen tafels van ($rest) 2 vrij zijn, pakt hij dan achterstevoren 2 tafels van 4, mits die beschikbaar zijn: als er maar 1 tafel van 4 is, wordt het uiteindelijk alsnog een tafel van 4 en een van 6, maar pas in de laatste iteratie, nadat $rest += 2; ZIE HIERONDER)
                foreach ($toReservedTables as $table) { //Eerst wel $toReservedTables LEEGHALEN en $rest 'resetten':
                    $rest += $table['Capacity']; // zodat $rest weer optelt tot het aantal gasten van de reservering
                }
                $toReservedTables = []; //wordt weer op leeg gezet voor de tweede iteratie
                $secondIteration = True;
                asort($freeTables); //van descending naar ascending (op basis van table Capacity - niet tableID)
                makeReservation($rest, $freeTables);
            } else if ($index == count($freeTables) && $secondIteration === True) { // als hij op dit punt terechtkomt is er geen andere mogelijkheid dan:
                $rest += 2; //ZIE HIERBOVEN hetzelfde voorbeeld met de reservering van 8 personen: als er maar 1 tafel van 4 vrij is, die heeft hij hierboven al gepakt na het omdraaien van de array, en $rest = 4, maar alleen die van 6 is vrij, pakt hij uiteindelijk toch de tafel van 6 (de 2-persoonstafels waren in dit voorbeeld immers al bezet, anders had hij in de eerste ronde al een tafel van 6 en een van 2 gepakt)
                if ($rest === $table['Capacity']) {
                    $toReservedTables[] = $table; //idem, de laatste tafel wordt in de array gezet etc.
                    $rest = $rest - $rest;
                    break;
                    // } else {$rest += 2; //LET OP: de (hierboven) niet weggecommente $rest += 2; (hierboven) gaat uit van de aanname dat de capaciteitsinterval tussen de tafels 2 is (bijv. 2, 4, 6); als die echter groter is (bijv. 4, 8) zou gelden: $rest += 4; ook mogelijk: een wisselende capaciteitsinterval (bijv. 2, 4, 8), als dit laatste het geval is, moet op deze regel de weggecommente else {met extra $rest += 2; (etc.)} worden toegevoegd (deze manier van denken kun je in principe tot in het oneindige doorvoeren, maar in de praktijk worden nu de meest realistische scenario's wel afgedekt.)
                    //     if ($rest === $table['Capacity']) {
                    //         $toReservedTables[] = $table; //idem, de laatste tafel wordt in de array gezet etc.
                    //         $rest = $rest - $rest;
                    //         break;
                    //     }
                }
            }
        }
        return $toReservedTables;
    }

    $freeCapacity = $db->fetchFreeCapacityBetweenDateTime($startDateTime, $endDateTime);
    $freeTables = $db->fetchFreeTablesBetweenDateTimeCapacityDescending($startDateTime, $endDateTime);
    // $freeTable = $db->fetchFreeTableOfCapacityBetweenDateTime($nReservation->getNumberOfGuests(), $startDateTime, $endDateTime);
    //misschien kan ik bovenstaande weggecommente methode gebruiken voor ENKELVOUDIGE reserveringen (die niet over meerdere tafels hoeven worden verdeeld)
    //maar wat levert het op qua efficiency (je hoeft niet meer in PHP te loopen over een array (gevonden met SQL) omdat je met SQL al precies de juiste tafel hebt gevonden, als die vrij is),
    //afgewogen tegen een toegenomen complexiteit (bovenstaande makeReservation() functie kan immers ook al enkelvoudige reserveringen aan, maar doet er wellicht iets langer over)?

    if ($nReservation->getNumberOfGuests() > $freeCapacity['SUM(Capacity)']) {
        echo "Unfortunately, at this date/time there are not enough seats available for your reservation.";
        exit;
    } else {
        $rest = $nReservation->getNumberOfGuests();
        makeReservation($rest, $freeTables);
    }

    $session->setReservationTables($toReservedTables); //de output van makeReservation() gaat in de sessie naar toreservedtables.php

    $lastID = $db->getLastReservationID();
    $reservationID = $lastID['reservationID'] + 1; //'autoincrement' (toegevoegd ivm combi insertReservation() + insertReservedTable)
    //LET OP: het reservationID wordt gebruikt in insertReservedTable()-methode voor de koppelingstabel reservedtables...
    //...waarbij behalve reservationID ook tableID nodig is (staat in $toReservedTables array) en begin- en eindtijd (zie hieronder)

    $session->setReservationID($reservationID); //naar toreservedtables.php

    $session->setStartDateTime($startDateTime); //naar toreservedtables.php

    $session->setEndDateTime($endDateTime); //naar toreservedtables.php
    //de reservering wordt in tabel reservations gezet (en daarna via to reservedtables.php) in koppeltabel reservedtables:
    $insert = $db->insertReservation($reservationID, $guestID, $nReservation->getNumberOfGuests(), $startDateTime, $endDateTime);
} elseif (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    $reservationID = $validationOutput['quantity'];
    $cancel = $db->cancelReservation($reservationID);
}
