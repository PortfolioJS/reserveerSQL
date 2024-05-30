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

if (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'reservationsbetweenwrong') { //als reservationsbetween fout is ingevuld
    $dateErr = $_SESSION["DateErr"];
    $timeErr = $_SESSION["TimeErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'guestsbetweenwrong') { //als guestsbetween fout is ingevuld
    $dateErr = $_SESSION["DateErr"];
    $timeErr = $_SESSION["TimeErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'showfreecapacitywrong') { //als showfreecapacity fout is ingevuld
    $dateErr = $_SESSION["DateErr"];
    $timeErr = $_SESSION["TimeErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'addtablewrong') { //als addtable fout is ingevuld
    $quantityErr = $_SESSION["QuantityErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'changetablewrong') { //als changetable fout is ingevuld
    $quantityErr = $_SESSION["QuantityErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'deletetablewrong') { //als changetable fout is ingevuld
    $quantityErr = $_SESSION["QuantityErr"];
    $timeErr = $_SESSION["TimeErr"];
} elseif (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'newopeninghourswrong') { //als reservationsbetween fout is ingevuld
    $dateErr = $_SESSION["DateErr"];
    $timeErr = $_SESSION["TimeErr"];
}
// elseif (empty($_POST)) { //als geen formulier nog is ingevuld:
//     $dateErr[0] = $timeErr[0] = $dateErr[1] = $timeErr[1] =  ""; //een lege string omdat de foutmeldingen wel moeten worden gedefinieerd OOK als ze niet hoeven worden getoond
// bovenstaande regels weggecomment omdat een lege string niet meer nodig is in dit formulier (door if (isset($_GET['action']) etc.); eventueel de andere formulieren ook op deze wijze aanpassen

?>
<html>

<body>
    <h2>Show reservations over period:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=reservationsbetween" method="post">
        <label for="date1">Which startdate? </label>
        <input type="date" id="date1" name="date1">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'reservationsbetweenwrong') {
                                    echo $dateErr[0];
                                } ?></span><br>
        <label for="time1">Which time? </label>
        <input type="time" id="time1" name="time1" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'reservationsbetweenwrong') {
                                    echo $timeErr[0];
                                } ?></span><br>
        <label for="date2">Which enddate? </label>
        <input type="date" id="date2" name="date2">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'reservationsbetweenwrong') {
                                    echo $dateErr[1];
                                } ?></span><br>
        <label for="time2">Which time? </label>
        <input type="time" id="time2" name="time2" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'reservationsbetweenwrong') {
                                    echo $timeErr[1];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Count guests over period:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=guestsbetween" method="post">
        <label for="date1">Which startdate? </label>
        <input type="date" id="date1" name="date1">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'guestsbetweenwrong') {
                                    echo $dateErr[0];
                                } ?></span><br>
        <label for="time1">Which time? </label>
        <input type="time" id="time1" name="time1" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'guestsbetweenwrong') {
                                    echo $timeErr[0];
                                } ?></span><br>
        <label for="date2">Which enddate? </label>
        <input type="date" id="date2" name="date2">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'guestsbetweenwrong') {
                                    echo $dateErr[1];
                                } ?></span><br>
        <label for="time2">Which time? </label>
        <input type="time" id="time2" name="time2" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'guestsbetweenwrong') {
                                    echo $timeErr[1];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Show free capacity at date/time:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=showfreecapacity" method="post">
        <label for="date1">Which date? </label>
        <input type="date" id="date1" name="date1">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'showfreecapacitywrong') {
                                    echo $dateErr[0];
                                } ?></span><br>
        <label for="time1">Which time? </label>
        <input type="time" id="time1" name="time1" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'showfreecapacitywrong') {
                                    echo $timeErr[0];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Show free tables at date/time:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=showfreetables" method="post">
        <label for="date1">Which date? </label>
        <input type="date" id="date1" name="date1">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'showfreetableswrong') {
                                    echo $dateErr[0];
                                } ?></span><br>
        <label for="time1">Which time? </label>
        <input type="time" id="time1" name="time1" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'showfreetableswrong') {
                                    echo $timeErr[0];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Add table:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=addtable" method="post">
        <label for="quantity">Fill in the capacity of the table you want to add:</label>
        <input type="number" id="quantity" name="quantity">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'addtablewrong') {
                                    echo $quantityErr[0];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Change table capacity:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=changetable" method="post">
        <label for="quantity1">Fill in tablenr:</label>
        <input type="number" id="quantity1" name="quantity1">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'changetablewrong') {
                                    echo $quantityErr[0];
                                } ?></span><br>
        <label for="quantity2">Fill in the (new) capacity of tablenr:</label>
        <input type="number" id="quantity2" name="quantity2">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'changetablewrong') {
                                    echo $quantityErr[1];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Delete table:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=deletetable" method="post">
        <label for="quantity">Fill in tablenr:</label>
        <input type="number" id="quantity" name="quantity">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'deletetablewrong') {
                                    echo $quantityErr[0];
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Set new opening hours:</h2>
    <form action="/reserveerSQL/validateadmin.php?action=newopeninghours" method="post">
        <label for="checkbox">Check the box(es) of the day(s) for which you want to change the hours of opening:</label><br>
        <label><input type="checkbox" id="Sunday" name="checkbox[]" value="Sunday">Sunday</label><br>
        <label><input type="checkbox" id="Monday" name="checkbox[]" value="Monday">Monday</label><br>
        <label><input type="checkbox" id="Tuesday" name="checkbox[]" value="Tuesday">Tuesday</label><br>
        <label><input type="checkbox" id="Wednesday" name="checkbox[]" value="Wednesday">Wednesday</label><br>
        <label><input type="checkbox" id="Thursday" name="checkbox[]" value="Thursday">Thursday</label><br>
        <label><input type="checkbox" id="Friday" name="checkbox[]" value="Friday">Friday</label><br>
        <label><input type="checkbox" id="Saturday" name="checkbox[]" value="Saturday">Saturday</label><br>
        <label for="time1">New time of opening:</label>
        <input type="time" id="time1" name="time1" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'newopeninghourswrong') {
                                    echo $timeErr[0];
                                } ?></span><br>
        <label for="time2">New closing-time:</label>
        <input type="time" id="time2" name="time2" value="00:00">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'newopeninghourswrong') {
                                    echo $timeErr[1];
                                } ?></span><br>
        <label><input type="radio" id="Closed" name="radio" value="Closed">Closed (check this box when the restaurant is closed on this (these) day(s))</label><br>
        <label for="date">Startdate of the new hours of opening:</label>
        <input type="date" id="date" name="date">
        <span class="error"> <?php if (isset($_GET['action']) && $_GET['action'] == 'newopeninghourswrong') {
                                    echo $dateErr[0];
                                } elseif (isset($_GET['action']) && $_GET['action'] == 'catchopeninghours') {
                                    echo "<h4>NOTICE: The database already contains reservations after your proposed startdate. Before you set the new opening hours, make sure there are no inconsistencies between the new opening hours and existing reservations.</h4>";
                                } ?></span><br>
        <input type="submit" value="Submit">
    </form>
</body>