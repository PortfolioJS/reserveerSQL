<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";

$session = new Session();

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
} elseif ($_SESSION["username"] !== "Admin") { //wanneer de Admin niet is ingelogd
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}

$validationOutput = $_SESSION["ValidationOutput"]; //de gevalideerde input van de formulieren

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

$username = $_SESSION["username"]; //Admin

$user = $db->fetchUser($username);

$id = $user['GuestID'];
$session->setGuestID($id); //zonder deze $_SESSION['GuestID'] kan Admin geen reserveringen doen (via vooralsnog makereservation.php; op dit moment doet Admin alleen onder eigen naam reserveringen, eigenlijk zou Admin ook (telefonische) reserveringen voor anderen in de database moeten kunnen zetten)

if (isset($_GET['action']) && $_GET['action'] == 'reservationsbetween') {

    $date1 = $validationOutput['date1'];
    $time1 = $validationOutput['time1'];
    $date2 = $validationOutput['date2'];
    $time2 = $validationOutput['time2'];

    //$startDateTime/$endDateTime is periode waarover Admin de reserveringen wil zien (bijvoorbeeld een dag, een week)
    $startDateTime = $date1 . " " . $time1;
    $endDateTime = $date2 . " " . $time2;

    $reservations = $db->fetchReservationsBetweenDateTimePlus($startDateTime, $endDateTime);

    // de volgorde van de datum wordt aangepast naar Nederlandse maatstaven)
    list($year, $month, $day) = explode('-', $date1);
    $date1 = [$day, $month, $year];
    $date1 = implode('-', $date1);
    list($year, $month, $day) = explode('-', $date2);
    $date2 = [$day, $month, $year];
    $date2 = implode('-', $date2);

    $startDateTime = $date1 . " " . $time1;
    $endDateTime = $date2 . " " . $time2;

    echo "<h2>Reservations between " . $startDateTime . " and " . $endDateTime . ":</h2>";

    if (!empty($reservations)) {

        foreach ($reservations as $reservation) {
            $reservationID = $reservation['ReservationID'];
            $startDateTime = $reservation['StartDateTime'];
            list($year, $month, $daytime) = explode('-', $startDateTime); //de volgorde van de datum wordt aangepast naar Nederlandse maatstaven 
            list($day, $time) = explode(' ', $daytime);
            $startDateTime = [$day, $month, $year];
            $startDateTime = implode('-', $startDateTime);
            $startDateTime = [$startDateTime, $time];
            $startDateTime = implode(' ', $startDateTime);
            echo "Startdate/time: " . $startDateTime . "<br>";
            echo "Reservationnr.: " . $reservation['ReservationID'] . "<br>";
            echo "Username: " . $reservation['UserName'] . " / Email: " . $reservation['Email'] . "<br>";
            echo "Number of guests: " . $reservation['NumberOfGuests'] . "<br>";

            $reservationTables = $db->fetchReservationTable($reservationID);

            foreach ($reservationTables as $table) {
                echo "Tablenr: " . $table['TableID'] . " / Capacity: " . $table['Capacity'] . "<br>";
            }
            echo "<br>";
        }
    } else {
        echo "There are no reservations found for the specified period.";
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'guestsbetween') {

    $date1 = $validationOutput['date1'];
    $time1 = $validationOutput['time1'];
    $date2 = $validationOutput['date2'];
    $time2 = $validationOutput['time2'];

    //$startDateTime/$endDateTime is periode waarover Admin de reserveringen wil zien (bijvoorbeeld een dag, een week)
    $startDateTime = $date1 . " " . $time1;
    $endDateTime = $date2 . " " . $time2;

    $guestCount = $db->countGuestsBetweenDateTime($startDateTime, $endDateTime);

    // de volgorde van de datum wordt aangepast naar Nederlandse maatstaven)
    list($year, $month, $day) = explode('-', $date1);
    $date1 = [$day, $month, $year];
    $date1 = implode('-', $date1);
    list($year, $month, $day) = explode('-', $date2);
    $date2 = [$day, $month, $year];
    $date2 = implode('-', $date2);

    $startDateTime = $date1 . " " . $time1;
    $endDateTime = $date2 . " " . $time2;

    echo "<h2>Total guests between " . $startDateTime . " and " . $endDateTime . ":</h2>";
    if ($guestCount['SUM(numberOfGuests)'] !== NULL) {
        echo  $guestCount['SUM(numberOfGuests)'];
    } else {
        echo 0;
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'showfreecapacity') {

    $date1 = $validationOutput['date1'];
    $time1 = $validationOutput['time1'];

    $startDateTime = $date1 . " " . $time1; //string
    $startDateTime = new DateTime($startDateTime); //string wordt object i.v.m.: (clone $startDateTime)
    $minutes = 149; //aangenomen dat een restaurantbezoek max. 2,5 uur duurt (zelfde interval als in class_Reservation)
    $endDateTime = (clone $startDateTime)->add(new DateInterval("PT{$minutes}M"));

    $startDateTime = $startDateTime->format("Y-m-d H:i:s"); //DateTime object wordt weer omgezet naar string (richting database $db)
    $endDateTime = $endDateTime->format("Y-m-d H:i:s"); //idem

    $freeCapacity = $db->fetchFreeCapacityBetweenDateTime($startDateTime, $endDateTime);

    list($year, $month, $daytime) = explode('-', $startDateTime); //de volgorde van de datum wordt aangepast naar Nederlandse maatstaven
    list($day, $time) = explode(' ', $daytime);
    list($hours, $minutes, $seconds) = explode(':', $time);
    $time = [$hours, $minutes]; //de seconden worden niet getoond
    $time = implode(':', $time);
    $startDateTime = [$day, $month, $year];
    $startDateTime = implode('-', $startDateTime);
    $startDateTime = [$startDateTime, $time];
    $startDateTime = implode(' ', $startDateTime);

    echo "<h2>Free capacity at " . $startDateTime . ":</h2>";

    if ($freeCapacity['SUM(Capacity)'] !== NULL) {
        echo $freeCapacity['SUM(Capacity)'] . " seats available.";
    } else {
        echo "No seats available at the specified time.";
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'showfreetables') {

    $date1 = $validationOutput['date1'];
    $time1 = $validationOutput['time1'];

    $startDateTime = $date1 . " " . $time1; //string
    $startDateTime = new DateTime($startDateTime); //string wordt object i.v.m.: (clone $startDateTime)
    $minutes = 149; //aangenomen dat een restaurantbezoek max. 2,5 uur duurt (zelfde interval als in class_Reservation)
    $endDateTime = (clone $startDateTime)->add(new DateInterval("PT{$minutes}M"));

    $startDateTime = $startDateTime->format("Y-m-d H:i:s"); //DateTime object wordt weer omgezet naar string (richting database $db)
    $endDateTime = $endDateTime->format("Y-m-d H:i:s"); //idem

    $freeTables = $db->fetchFreeTablesBetweenDateTimeCapacityAscending($startDateTime, $endDateTime);

    list($year, $month, $daytime) = explode('-', $startDateTime); //de volgorde van de datum wordt aangepast naar Nederlandse maatstaven
    list($day, $time) = explode(' ', $daytime);
    list($hours, $minutes, $seconds) = explode(':', $time);
    $time = [$hours, $minutes]; //de seconden worden niet getoond
    $time = implode(':', $time);
    $startDateTime = [$day, $month, $year];
    $startDateTime = implode('-', $startDateTime);
    $startDateTime = [$startDateTime, $time];
    $startDateTime = implode(' ', $startDateTime);

    echo "<h2>Free tables at " . $startDateTime . ":</h2>";

    if (!empty($freeTables)) {
        foreach ($freeTables as $table) {
            echo "Tablenr.: " . $table['TableID'] . "<br>";
            echo "Capacity.: " . $table['Capacity'] . "<br><br>";
        }
    } else {
        echo "No free tables at the specified time.";
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'addtable') {

    $capacity = $validationOutput['quantity'];

    $db->addTable($capacity);
} else if (isset($_GET['action']) && $_GET['action'] == 'addtable2') {
    echo "<h2>You succesfully added a table to the database:</h2>";
    $dbconnection = new DatabaseConnection;
    $pdo = $dbconnection->connection;

    $db = new Database($pdo);
    $newTable = $db->fetchLastTable();
    echo "TableID: " . $newTable['TableID'];
    echo "<br>Capacity: " . $newTable['Capacity'];
} else if (isset($_GET['action']) && $_GET['action'] == 'changetable') {

    $id = $validationOutput['quantity1'];
    $capacity = $validationOutput['quantity2'];

    $db->changeTable($id, $capacity);
} else if (isset($_GET['action']) && $_GET['action'] == 'changetable2') {

    $tableID = $_SESSION["TableID"];

    $dbconnection = new DatabaseConnection;
    $pdo = $dbconnection->connection;

    $db = new Database($pdo);
    $thisTable = $db->fetchTable($tableID);

    if ($thisTable === False) { //EXTRA validatie (buiten validateadmin.php i.v.m. $db) als het ingevulde nr. (TableID) niet in de database voorkomt:

        $quantityErr[0] = "The tablenr. is invalid";
        $quantityErr[1] = ""; //anders geeft hij een foutmelding in het formulier
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=changetablewrong');
        exit;
    } else {
        echo "<h2>You succesfully changed the table capacity:</h2>";
        echo "TableID: " . $thisTable['TableID'];
        echo "<br>Capacity: " . $thisTable['Capacity'];
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'deletetable') {

    $tableID = $_SESSION["TableID"];

    $dbconnection = new DatabaseConnection;
    $pdo = $dbconnection->connection;

    $db = new Database($pdo);
    $thisTable = $db->fetchTable($tableID);

    if ($thisTable === False) { //EXTRA validatie (buiten validateadmin.php i.v.m. $db) als het ingevulde nr. (TableID) niet in de database voorkomt:
        $quantityErr[0] = "The tablenr. is invalid";
        $session->setQuantityErr($quantityErr);
        header('Location: /reserveerSQL/admin.php?action=deletetablewrong');
        exit;
    } else {
        $db->deleteTable($tableID);
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'deletetable2') {
    $tableID = $_SESSION["TableID"];
    echo "<h2>You succesfully deleted tablenr. " . $tableID . " from the database.</h2>";
} else if (isset($_GET['action']) && $_GET['action'] == 'newopeninghours') {
    $startDate = $validationOutput['date'];
    $startTime = "00:00:00";
    $startDateTime = $startDate . " " . $startTime;
    $reservationsAfterStartDate = $db->fetchReservationsAfter($startDateTime);
    if (!empty($reservationsAfterStartDate)) {
        header('Location: /reserveerSQL/admin.php?action=catchopeninghours');
        exit;
    } else {
        header('Location: /reserveerSQL/setopeninghours.php');
        exit;
    }
}
