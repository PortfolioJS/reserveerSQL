<?php

class Database
{
    public $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    //eerste 5 SQL queries overgenomen uit login-systeem, tabel-naam aangepast (users => guests)
    public function fetchUser($username): array|bool
    {
        $query = "SELECT * FROM guests WHERE username = :username";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user;
    }
    //Onderstaande functie doet hetzelfde als bovenstaande (verschil: maar zoekt op $id i.p.v. $username)
    public function fetchUserviaID($id): array|bool
    {
        $query = "SELECT * FROM guests WHERE guestid = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user;
    }

    public function insertUser($username, $email, $passwordHash): array
    {
        $query = "INSERT INTO guests (username, Email, password) VALUES (:username, :email, :password)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $passwordHash,]);
        header('Location: /reserveerSQL/myaccount.php?action=create');
        exit;
    }

    public function updateUser($username, $email, $id): array
    {
        $query = "UPDATE guests SET username=:username, email=:email WHERE guestid=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $id]);
        header('Location: /reserveerSQL/myaccount.php?action=change');
        exit;
    }

    public function updatePassword($newpasswordHash, $id): array
    {
        $query = "UPDATE guests SET password=:password WHERE guestid=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['password' => $newpasswordHash, 'id' => $id]);
        header('Location: /reserveerSQL/myaccount.php?action=changep');
        exit;
    }

    public function addTable($capacity): array
    { //deze functie moet alleen beschikbaar komen voor de Admin (wanneer die is ingelogd)
        $query = "INSERT INTO tables (capacity) VALUES (:capacity)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['capacity' => $capacity,]); //LET OP: bij validatie zorgen dat alleen even aantallen kunnen worden ingevoerd ivm: if ($REST % 2 != 0) {$REST += 1;} (in makeReservation() functie op actionpage.php?action=reserve). ZOIETS DUS: if ($capacity % 2 != 0) {echo "Alleen even nummers";} OF: makeReservation() aanpassen zodat ook tafels met oneven Capacity mogelijk zijn
        header('Location: /reserveerSQL/actionadmin.php?action=addtable2');
        exit;
    }

    public function fetchLastTable(): array|bool
    { //omdat addTable() (hierboven) werkt met autoincrement PHPMyAdmin (voor TableID) kan de laatst toegevoegde tafel zo worden getoond
        $query = "SELECT * FROM tables ORDER BY TableID DESC LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $lastTable = $stmt->fetch();
        return $lastTable;
    }

    public function fetchTable($id): array|bool
    {
        $query = "SELECT * FROM tables WHERE tableid = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $table = $stmt->fetch();
        return $table;
    }

    public function changeTable($id, $capacity): array
    { //deze functie moet alleen beschikbaar komen voor de Admin (wanneer die is ingelogd)
        $query = "UPDATE tables SET capacity=:capacity WHERE tableid=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id, 'capacity' => $capacity]); //LET OP: bij validatie zorgen dat alleen even aantallen kunnen worden ingevoerd ivm: if ($REST % 2 != 0) {$REST += 1;} (in makeReservation() functie op actionpage.php?action=reserve). ZOIETS DUS: if ($capacity % 2 != 0) {echo "Alleen even nummers";} OF: makeReservation() aanpassen
        header('Location: /reserveerSQL/actionadmin.php?action=changetable2');
        exit;
    }

    public function deleteTable($id): array|bool
    {
        $query = "DELETE FROM tables WHERE tableid = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        header('Location: /reserveerSQL/actionadmin.php?action=deletetable2');
        exit;
    }

    public function fetchOpeningHours($dayname): array|bool
    {
        $query = "SELECT * FROM openinghours WHERE DayOfWeek = :dayname";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['dayname' => $dayname]);
        $openingHours = $stmt->fetch();
        return $openingHours;
    }

    public function fetchAllOpeningHours(): array|bool
    {
        $query = "SELECT * FROM openinghours";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $openingHours = $stmt->fetchall();
        return $openingHours;
    }

    public function fetchAllNewOpeningHours($now): array|bool
    { //bedoeld voor automatisch resetten (nieuwe) openingstijden na startdatum (in combinatie met resetOpeningHours(), hieronder) LET OP: dit resetten vindt plaats tijdens het reserveringsproces, zie: actionpage.php?action=reserve
        $query = "SELECT * FROM openinghours WHERE Startdate <= :now"; //WHERE: alleen startdatums die al zijn ingegegaan worden gepakt
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['now' => $now]);
        $openingHours = $stmt->fetchall();
        return $openingHours;
    }

    public function setNewOpeningHours($dayname, $startDate, $newOpen, $newClosed): array|bool
    {
        $query = "UPDATE openinghours SET Startdate= :startdate, NewOpen= :newopen, NewClosed= :newclosed WHERE DayOfWeek= :dayname";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['dayname' => $dayname, 'startdate' => $startDate, 'newopen' => $newOpen, 'newclosed' => $newClosed]);
        header('Location: /reserveerSQL/setopeninghours.php');
        exit;
    }

    public function resetOpeningHours($dayname, $open, $closed, $startDate, $newOpen, $newClosed): array|bool
    {
        $query = "UPDATE openinghours SET Open = :open, Closed= :closed, Startdate= :startdate, NewOpen= :newopen, NewClosed= :newclosed WHERE DayOfWeek= :dayname";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['dayname' => $dayname, 'open' => $open, 'closed' => $closed,  'startdate' => $startDate, 'newopen' => $newOpen, 'newclosed' => $newClosed]);
        header('Location: /reserveerSQL/resetopeninghours.php');
        exit;
    }

    // public function fetchGuestsReservations($guestID): array|bool
    // {
    //     $query = "SELECT guests.UserName, guests.Email, reservations.ReservationID, reservedtables.TableID, tables.Capacity, reservations.StartDateTime, reservations.NumberOfGuests
    //     FROM reservations
    //     INNER JOIN reservedtables ON reservations.reservationID=reservedtables.reservationID
    //     INNER JOIN guests ON reservations.GuestID=guests.GuestID 
    //     INNER JOIN tables ON reservedtables.TableID=tables.TableID 
    //     WHERE reservations.guestID = :id";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['id' => $guestID]);
    //     $myReservations = $stmt->fetchall();
    //     return $myReservations;
    // }

    public function fetchGuestsReservations($guestID): array|bool
    { //de gast heeft niet zoveel informatie nodig als in bovenstaande weggecommente query (die is evt. wel handig voor de Admin)
        $query = "SELECT reservations.ReservationID, reservations.StartDateTime, reservations.NumberOfGuests
        FROM reservations
        WHERE reservations.guestID = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $guestID]);
        $myReservations = $stmt->fetchall();
        return $myReservations;
    }

    public function fetchReservation($id): array|bool
    {
        $query = "SELECT * FROM reservations WHERE reservationid = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $reservation = $stmt->fetch();
        return $reservation;
    }

    public function fetchReservationsBetweenDateTime($startDateTime, $endDateTime): array|bool
    { //zoekt alle reserveringen op specifiek tijdstip
        $query = "SELECT * FROM reservations 
        WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
        OR endDateTime BETWEEN :startdatetime AND :enddatetime";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $reservations = $stmt->fetchall();
        return $reservations;
    }

    public function fetchReservationsAfter($startDateTime): array|bool
    { //zoekt alle reserveringen NA specifiek tijdstip (nodig om Admin te waarschuwen bij het veranderen van de openingstijden in het geval dat er al reserveringen in de database staan op een tijdstip NA de startdatum van de nieuwe openingstijden)
        $query = "SELECT * FROM reservations 
        WHERE startDateTime >= :startdatetime";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime]);
        $reservations = $stmt->fetchall();
        return $reservations;
    }

    // public function fetchReservationsBetweenDateTimePlus($startDateTime, $endDateTime): array|bool
    // { //zoekt alle reserveringen op specifiek tijdstip, met bijbehorende info over de gast en de tafel (tables.capacity verwijderd)
    //     //bij reserveringen met meerdere tafels laat print_r een subarray zien per tafel, waarbij behalve het tableID/capacity alle andere data identiek zijn (LET OP: omdat het lastig is de relevante data vervolgens overzichtelijk te tonen, heb ik deze methode niet gebruikt. In plaats daarvan heb ik hem vervangen door onderstaande twee methodes: fetchReservationsBetweenDateTimePlusMinusTable() en fetchReservationTable().)
    //     $query = "SELECT reservations.ReservationID, guests.UserName, guests.Email, reservedtables.TableID, tables.Capacity, reservations.StartDateTime, reservations.EndDateTime, reservations.NumberOfGuests
    //     FROM reservations
    //     INNER JOIN reservedtables ON reservations.reservationID=reservedtables.reservationID
    //     INNER JOIN tables ON reservedtables.tableID=tables.tableID
    //     INNER JOIN guests ON reservations.GuestID=guests.GuestID 
    //     WHERE reservations.startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR reservations.endDateTime BETWEEN :startdatetime AND :enddatetime";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $reservations = $stmt->fetchall();
    //     return $reservations;
    // }

    public function fetchReservationsBetweenDateTimePlus($startDateTime, $endDateTime): array|bool
    { //zelfde als bovenstaande, minus tableID/capacity (zodat het aantal subarrays beperkt blijft, zie comment hierboven) ORDER BY StartDateTime toegevoegd omdat het voor een restauranthouder wel handig is reserveringen op chronologische volgorde te zien (i.p.v. reservationID)
        $query = "SELECT reservations.ReservationID, guests.UserName, guests.Email, reservations.StartDateTime, reservations.EndDateTime, reservations.NumberOfGuests
        FROM reservations
        INNER JOIN guests ON reservations.GuestID=guests.GuestID 
        WHERE reservations.startDateTime BETWEEN :startdatetime AND :enddatetime
        OR reservations.endDateTime BETWEEN :startdatetime AND :enddatetime
        ORDER BY reservations.StartDateTime";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $reservations = $stmt->fetchall();
        return $reservations;
    }

    public function fetchReservationTable($reservationID): array|bool
    { //haalt alle tafels op die bij betreffende reservering horen
        $query = "SELECT reservedtables.TableID, tables.Capacity FROM reservedtables 
        INNER JOIN tables ON reservedtables.TableID=tables.TableID
        WHERE reservedtables.reservationID = :reservationID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['reservationID' => $reservationID]);
        $tables = $stmt->fetchall();
        return $tables;
    }

    public function fetchFreeTablesBetweenDateTimeCapacityDescending($startDateTime, $endDateTime): array|bool
    { //handig bij het verdelen van splitreservations (de grote tafels eerst) LET OP: in de $freeTables outputarray staat eerst de Capacity en dan pas de TableID (SELECT Capacity, TableID), omdat de Capacity bepalend is bij het omdraaien van de array van DESC naar ASC - met de asort() functie, die wordt gebruikt in de makeReservation() functie (soms is het omdraaien van de volgorde handig, zie toelichting in makeReservation() op de actionpage.php?action=reserve)
        $query = "SELECT Capacity, TableID FROM tables WHERE TableID NOT IN
        (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
        OR endDateTime BETWEEN :startdatetime AND :enddatetime)
        ORDER BY Capacity DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $freeTables = $stmt->fetchall();
        return $freeTables;
    }

    public function fetchFreeTablesBetweenDateTimeCapacityAscending($startDateTime, $endDateTime): array|bool
    { //handig voor de Admin/restauranthouder
        $query = "SELECT TableID, Capacity FROM tables WHERE TableID NOT IN
        (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
        OR endDateTime BETWEEN :startdatetime AND :enddatetime)
        ORDER BY Capacity ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $freeTables = $stmt->fetchall();
        return $freeTables;
    }

    // public function fetchBiggestFreeTableBetweenDateTime($startDateTime, $endDateTime): array|bool
    // { //zelfde als bovenstaande maar dan met limiet 1; dit is efficiënter dan (let op de index [0]):
    //     //$freeTables[0] (na het callen van bovenstaande methode fetchFreeTablesBetweenDateTimeCapacityDescending)
    //     $query = "SELECT TableID, Capacity FROM tables WHERE TableID NOT IN
    //     (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime)
    //     ORDER BY Capacity DESC
    //     LIMIT 1";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $freeTables = $stmt->fetch();
    //     return $freeTables;
    // }

    // public function fetchSmallestFreeTableBetweenDateTime($startDateTime, $endDateTime): array|bool
    // { //zelfde als bovenstaande maar dan pakt hij de kleinste vrije tafel
    //     $query = "SELECT TableID, Capacity FROM tables WHERE TableID NOT IN
    //     (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime)
    //     ORDER BY Capacity ASC
    //     LIMIT 1";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $freeTables = $stmt->fetch();
    //     return $freeTables;
    // }

    // public function fetchFreeTablesOfCapacityBetweenDateTime($capacity, $startDateTime, $endDateTime): array|bool
    // { // OR Capacity= :capacity - 1 (zie hieronder) bijvoorbeeld bij reservering 3 personen wil je een 4 persoonstafel
    //     $query = "SELECT TableID, Capacity FROM tables WHERE Capacity= :capacity OR Capacity= :capacity - 1 AND TableID NOT IN
    //     (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime)";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['capacity' => $capacity, 'startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $freeTable = $stmt->fetchall();
    //     return $freeTable;
    // }

    public function fetchFreeCapacityBetweenDateTime($startDateTime, $endDateTime): array|bool
    { //wordt gebruikt om te checken of reservering qua aantal gasten mogelijk is op tijdstip, zie actionpage.php?action=reserve
        $query = "SELECT SUM(Capacity) FROM tables WHERE TableID NOT IN
        (SELECT TableID FROM reservedtables WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
        OR endDateTime BETWEEN :startdatetime AND :enddatetime)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $freeCapacity = $stmt->fetch();
        return $freeCapacity;
    }

    public function countGuestsBetweenDateTime($startDateTime, $endDateTime): array|bool
    { //kan handig zijn voor Admin/restauranthouder
        $query = "SELECT SUM(numberOfGuests) FROM reservations WHERE ReservationID IN
        (SELECT ReservationID FROM reservations WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
        OR endDateTime BETWEEN :startdatetime AND :enddatetime)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
        $guestCount = $stmt->fetch();
        return $guestCount;
    }

    // public function countReservationsBetweenDateTime($startDateTime, $endDateTime): array|bool
    // { //telt reserveringen tussen begin- en eindtijd
    //     $query = "SELECT COUNT(reservationID)
    //     FROM reservations
    //     WHERE startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $reservationcount = $stmt->fetch();
    //     return $reservationcount;
    // }

    // public function fetchReservedTablesBetweenDateTime($capacity, $startDateTime, $endDateTime): array|bool
    // { //zoekt alle x=-persoonstafels die tussen begin- en eindtijd bezet zijn
    //     $query = "SELECT tables.TableID, tables.Capacity, reservedtables.StartDateTime, reservedtables.EndDateTime
    //     FROM tables
    //     INNER JOIN reservedtables ON tables.TableID=reservedtables.TableID
    //     WHERE Capacity= :capacity
    //     AND startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime
    //     ";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['capacity' => $capacity, 'startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $reservation = $stmt->fetchall();
    //     return $reservation;
    // }

    // public function countReservedTablesBetweenDateTime($capacity, $startDateTime, $endDateTime): array|bool
    // { //TELT alle x-persoonstafels die tussen begin- en eindtijd bezet zijn
    //     $query = "SELECT COUNT(tables.TableID), tables.Capacity, reservedtables.StartDateTime, reservedtables.EndDateTime
    //     FROM tables
    //     INNER JOIN reservedtables ON tables.TableID=reservedtables.TableID
    //     WHERE Capacity= :capacity
    //     AND startDateTime BETWEEN :startdatetime AND :enddatetime
    //     OR endDateTime BETWEEN :startdatetime AND :enddatetime
    //     ";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['capacity' => $capacity, 'startdatetime' => $startDateTime, 'enddatetime' => $endDateTime]);
    //     $countReservedTablesOfCapacity = $stmt->fetch();
    //     return $countReservedTablesOfCapacity;
    // }

    // public function countTablesOfCapacity($capacity): array|bool
    // { //TELT alle x-persoonstafels
    //     $query = "SELECT COUNT(tables.TableID), tables.Capacity
    //     FROM tables
    //     WHERE Capacity= :capacity";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute(['capacity' => $capacity]);
    //     $countTablesOfCapacity = $stmt->fetch();
    //     return $countTablesOfCapacity;
    // }

    public function insertReservation($reservationID, $guestID, $numberOfGuests, $startDateTime, $endDateTime): array
    { //LET OP: tableID staat in koppeltabel reservedtables (via toreservedtables.php) ivm reserveringen met meerdere tafels
        //OOK een optie: kolom tableID weer terugzetten (in reservations), maar dan als array (die meerdere tableID's kan bevatten)
        $query = "INSERT INTO reservations (reservationID, guestID, numberOfGuests, startDateTime, endDateTime) VALUES (:reservationID, :guestID, :numberOfGuests, :startDateTime, :endDateTime)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['reservationID' => $reservationID, 'guestID' => $guestID, 'numberOfGuests' => $numberOfGuests, 'startDateTime' => $startDateTime, 'endDateTime' => $endDateTime,]);
        header('Location: /reserveerSQL/toreservedtables.php');
        exit;
    }

    public function getLastReservationID()
    { //na insertReservation() haalt getLastReservationID() het (laatst toegevoegde) reservationID op voor gebruik in insertReservedTable()
        $query = "SELECT reservationID FROM reservations
        ORDER BY reservationID DESC
        LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $reservationID = $stmt->fetch();
        return $reservationID;
    }

    public function insertReservedTable($reservationID, $tableID, $startDateTime, $endDateTime): array
    { //na insertReservation() en getNewReservationID() kan de reservering ook in koppeltabel reservedtables worden gezet
        $query = "INSERT INTO reservedtables (reservationID, tableID, startDateTime, endDateTime) VALUES (:reservationID, :tableID, :startDateTime, :endDateTime)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['reservationID' => $reservationID, 'tableID' => $tableID, 'startDateTime' => $startDateTime, 'endDateTime' => $endDateTime,]);
        header('Location: /reserveerSQL/toreservedtables.php');
        exit;
    }

    public function cancelReservation($reservationID): int
    { // haalt de betreffende reservering zowel uit de reservations als de reservedtables
        $query = "DELETE reservations, reservedtables
        FROM reservations 
        JOIN reservedtables
        ON reservations.reservationID = reservedtables.reservationID
        WHERE reservations.reservationID = :reservationID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['reservationID' => $reservationID,]);
        header('Location: /reserveerSQL/myaccount.php?action=cancel');
        exit;
    }
}
