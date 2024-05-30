<?php

class Session
{
    public function __construct()
    {
        // Alleen een sessie starten, als die nog niet gestart is
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        } //OF: if(session_status() === PHP_SESSION_NONE) {session_start();}
    }

    public function setLogin($username)
    {
        $_SESSION["username"] = $username;
    }

    // onderstaande sessie wordt onder meer gebruikt voor auto-aanvullen email wanneer een formulier (opnieuw) moet worden ingevuld
    // (bijvoorbeeld bij niet-matchende wachtwoorden bij het aanmaken van een account, of wanneer de gebruiker zijn accountgegevens/wachtwoord wil veranderen) 
    public function setEmail($email)
    {
        $_SESSION["email"] = $email;
    }

    public function setValidationOutput($validationOutput)
    {
        $_SESSION["ValidationOutput"] = $validationOutput;
    }

    //onderstaande sessie wordt gebruikt bij het doen van een nieuwe reservering (na inloggen)
    public function setGuestID($guestID)
    {
        $_SESSION["GuestID"] = $guestID;
    }

    //onderstaande sessie wordt gebruikt bij het doen van een nieuwe reservering (voor koppelingstabel reservedtables)
    public function setReservationID($reservationID)
    {
        $_SESSION["ReservationID"] = $reservationID;
    }

    //onderstaande sessie wordt gebruikt bij het veranderen van de Table Capacity (validateadmin.php)
    public function setTableID($tableID)
    {
        $_SESSION["TableID"] = $tableID;
    }

    //onderstaande sessie wordt gebruikt bij het doen van een nieuwe reservering (voor koppelingstabel reservedtables)
    public function setStartDateTime($startDateTime)
    {
        $_SESSION["StartDateTime"] = $startDateTime;
    }

    //onderstaande sessie wordt gebruikt bij het doen van een nieuwe reservering (voor koppelingstabel reservedtables)
    public function setEndDateTime($endDateTime)
    {
        $_SESSION["EndDateTime"] = $endDateTime;
    }

    //onderstaande sessie wordt gebruikt bij het doen van een nieuwe reservering (voor koppelingstabel reservedtables)
    public function setReservationTables($toReservedTables)
    {
        $_SESSION["ReservationTables"] = $toReservedTables;
    }

    public function setNewOpeningHours($newOpeningHours)
    {
        $_SESSION["NewOpeningHours"] = $newOpeningHours;
    }

    public function setCurrentReservations($myCurrentReservations)
    {
        $_SESSION["MyCurrentReservations"] = $myCurrentReservations;
    }

    public function setDateErr($dateErr)
    {
        $_SESSION["DateErr"] = $dateErr;
    }

    public function setTimeErr($timeErr)
    {
        $_SESSION["TimeErr"] = $timeErr;
    }

    public function setQuantityErr($quantityErr)
    {
        $_SESSION["QuantityErr"] = $quantityErr;
    }

    public function logout()
    {
        session_destroy();
    }
}
