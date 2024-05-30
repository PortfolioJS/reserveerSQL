<?php
class Reservation
{
    protected datetime $startDateTime;
    protected datetime $endDateTime; //=$startDateTime + 149 minuten (of zoiets, zie hieronder)
    protected int $guestID;
    protected int $numberOfGuests;
    //protected int $id;//kan waarschijnlijk weg (een reserveringsobject wordt tijdelijk aangemaakt om de reservering in goede banen te leiden,
    //d.w.z. checken of de reservering op specifiek tijdstip kan worden doorgevoerd en zo ja: de reservering in de database zetten,
    //pas dan: $id - al dan niet autoincrement)
    //evt. is het ook mogelijk in de database een additionele tabel op te nemen met afgeketste reserveringen (aanvragen),
    //die dus niet konden worden doorgezet (wegens geen plaats op tijdstip)
    //in dat geval kan het handig zijn ALLE reserveringen een $id te geven (en een timestamp van het moment van binnenkomst)
    //maar dit kan eigenlijk ook in de tabel reservations zelf gebeuren (maar dan zonder dat het $resID naar koppelingstabel reservedtables gaat)
    //als je dan de reserveringen wilt opzoeken die niet konden doorgaan, zoek je op de $id's die niet in reservedtables staan (SQL)
    //ook gecancelde reserveringen kun je eventueel bewaren op deze manier (met timestamp voor moment van reserveren en timestamp voor moment van cancellen)

    /**
     * @param string $startDateTime Data in format "d-m-y H:i".
     * @param string $endDateTime Data in format "d-m-y H:i".
     * @param int $guestID
     * @param int $numberOfGuests
     */

    function __construct($startDateTime, $guestID, $numberOfGuests)
    {
        $this->startDateTime = new DateTime($startDateTime);
        $minutes = 119; //aangenomen dat een restaurantbezoek max. 2 uur duurt
        $this->endDateTime = (clone $this->startDateTime)->add(new DateInterval("PT{$minutes}M"));
        $this->guestID = $guestID;
        $this->numberOfGuests = $numberOfGuests;
    }

    // /**
    //  * @param \Guest $guest
    //  */
    // public function setGuest($guestID)
    // {
    //     $this->guestID = $guestID;
    // }

    // /**
    //  * @return \Guest
    //  */
    // public function getGuest()
    // {
    //     return $this->guestID;
    // }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDateTime($endDateTime)
    {
        $this->endDateTime = $endDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTIme()
    {
        return $this->endDateTime;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    // /**
    //  * @param \int $numberOfGuests
    //  */
    // public function setNumberOfGuests($numberOfGuests)
    // {
    //     $this->numberOfGuests = $numberOfGuests;
    // }

    /**
     * @param \int
     */
    public function getNumberOfGuests()
    {
        return $this->numberOfGuests;
    }

    //onderstaande kan waarschijnlijk weg (zie ook comment bovenin achter: //protected int $id;)
    // /**
    //  * @param \int
    //  */
    // public function getID()
    // {
    //     return $this->id;
    // }

    function __toString()
    {
        return /*"Reservering ID: " . $this->id . */ "Datum/tijd: " . $this->startDateTime->format("d-m-y") . " om " . $this->getStartDateTime()->format("H:i") . " uur.\n"
            . "Aantal gasten: " . $this->numberOfGuests . "<br>"
            // . "End Date: " . $this->endDate->format("d-m-y") . "\n" //End Date niet nodig bij reservering voor restaurant (hoeft althans niet te worden ingevuld)
            /*. "Reservering op naam: " . $this->guest->__toString()*/; //werkt niet meer: guest vervangen door $guestID
    }
}
