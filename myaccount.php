<?php
require_once __DIR__ . '/classes/class_databaseconnection.php';
require_once __DIR__ . '/classes/class_database.php';
require_once __DIR__ . '/classes/class_session.php';
$session = new Session();

if (empty($_SESSION["username"])) { //wanneer er niet is ingelogd ($session->setLogin($username))
    header('Location: /reserveerSQL'); //de inlogpagina
    exit;
}
?>
<!DOCTYPE html>
<html>

<body>

    <?php
    if (isset($_GET['action']) && $_GET['action'] == 'login') {
        echo "<h1>Welcome " . $_SESSION["username"] . "! <br> Your Login was succesfull.</h1>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'create') {
        echo "<h2> Welcome " . $_SESSION["username"] . "! <br> You succesfully created a new account!</h2>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'change') {
        echo "<h2> Welcome " . $_SESSION["username"] . "! <br> You succesfully changed your account!</h2>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'changep') {
        echo "<h2> Welcome " . $_SESSION["username"] . "! <br> You succesfully changed your password!</h2>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'reserve') {
        echo "<h2> Welcome " . $_SESSION["username"] . "! <br> Your new reservation was succesfull, check below!</h2>";
    } elseif (isset($_GET['action']) && $_GET['action'] == 'cancel') {
        echo "<h2> Welcome " . $_SESSION["username"] . "! <br> Your reservation was succesfully cancelled, check below!</h2>";
    }

    $username = $_SESSION["username"];

    $dbconnection = new DatabaseConnection;
    $pdo = $dbconnection->connection;

    $db = new Database($pdo);
    $user = $db->fetchUser($username);

    echo "<h3>My account:</h3>";
    echo "Username: ";
    print_r($user['UserName']);
    echo "<br>";
    echo "Email: ";
    print_r($user['Email']);
    echo "<br>";

    $email = $user['Email'];
    $session->setEmail($email);
    $id = $user['GuestID'];
    $session->setGuestID($id);
    //deze 2 sessies zijn voor het automatisch invullen van het formulier changeaccount.php; bovendien wordt $_SESSION['GuestID'] gebruikt bij actionpage.php?action=reserve
    //de andere 2 sessies zijn bij het inloggen al ingevuld (vanaf makeaccount2.php was al wel een sessie e-mail aangemaakt)
    ?>

    <Change>
        <h4>Change account:</h4>
        <form action="/reserveerSQL/changeaccount.php" method="post">
            <label for="haveaccount"> If you want to make changes to your account, click </label>
            <input type="submit" value="Change">
        </form>

        <Logout>
            <form method="post" action="/reserveerSQL/logout.php">
                <input type="submit" value="Logout">
            </form>
</body>

</html>
<?php
echo "<br>";
echo "<h3>My current reservations:</h3>";

$myReservations = $db->fetchGuestsReservations($user['GuestID']);

if (!empty($myReservations)) {

    $now = date("Y-m-d H:i:s"); //zodat alleen reserveringen in de toekomst worden gepakt uit de array (zie if hieronder)
    $count = 0;
    $noReservations = True;
    $myCurrentReservations = [];

    foreach ($myReservations as $reservation) {
        $count += 1;
        if ($reservation['StartDateTime'] > $now) { // evt. oude reserveringen worden niet getoond
            $noReservations = False;
            $myCurrentReservations[] = $reservation; //voor het geval dat Guest een reservering wil cancellen
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
        } else {
            if ($count == count($myReservations) && $noReservations === True) {
                echo "There are no current reservations found.";
            }
        }
    }
    $session->setCurrentReservations($myCurrentReservations); //voor het geval dat Guest een reservering wil cancellen
} else {
    echo "No reservations found.";
}

?>

<Make>
    <h3>Make a reservation:</h3>
    <form action="/reserveerSQL/makereservation.php" method="post">
        <label for="makereservation"> Reserve </label>
        <input type="submit" value="NOW">
    </form>
    </body>
    <Cancel>
        <h3>Cancel reservation:</h3>
        <form action="/reserveerSQL/cancelreservation.php" method="post">
            <label for="cancelreservation"> Cancel </label>
            <input type="submit" value="here">
        </form>
        </body>

        </html>