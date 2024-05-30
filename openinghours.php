<?php
require_once __DIR__ . "/classes/class_session.php";
require_once __DIR__ . "/classes/class_database.php";
require_once __DIR__ . "/classes/class_databaseconnection.php";

$session = new Session();

$dbconnection = new DatabaseConnection;
$pdo = $dbconnection->connection;

$db = new Database($pdo);

if (!empty($_SESSION["DayName"])) {
    $dayName = $_SESSION["DayName"];
}

if (isset($_GET['action']) && $_GET['action'] == 'dayclosed') {
    echo "We are closed on " . $dayName . "s. Check our hours of opening below.<br><br>";
} elseif (isset($_GET['action']) && $_GET['action'] == 'timeclosed') {
    echo "We are closed at this time (or it's close to closing time). Please check our new hours of opening below. Bear in mind it's not possible to reserve a table less than 2 hours before closing time.<br><br>";
}

$openingHours = $db->fetchAllOpeningHours();

?>
<!DOCTYPE html>
<html>

<table>
    <caption>Opening hours:</caption>
    <tbody>
        <tr>
            <th></th>
            <th>Open</th>
            <th>Closed</th>
        </tr>
        <tr>
            <th>Sunday</th>
            <td><?php $open = $openingHours[0]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1]; //de seconden ($openA[2]) worden niet getoond
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[0]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1]; //idem (etc.)
                } ?></td>
        </tr>
        <tr>
            <th>Monday</th>
            <td><?php $open = $openingHours[1]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[1]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
        <tr>
            <th>Tuesday</th>
            <td><?php $open = $openingHours[2]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[2]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
        <tr>
            <th>Wednesday</th>
            <td><?php $open = $openingHours[3]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[3]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
        <tr>
            <th>Thursday</th>
            <td><?php $open = $openingHours[4]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[4]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
        <tr>
            <th>Friday</th>
            <td><?php $open = $openingHours[5]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[5]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
        <tr>
            <th>Saturday</th>
            <td><?php $open = $openingHours[6]['Open'];
                if ($open !== NULL) {
                    $openA = explode(':', $open);
                    echo $openA[0] . ':' . $openA[1];
                } else {
                    echo "CLOSED";
                } ?></td>
            <td><?php $closed = $openingHours[6]['Closed'];
                if ($closed !== NULL) {
                    $closedA = explode(':', $closed);
                    echo $closedA[0] . ':' . $closedA[1];
                } ?></td>
        </tr>
    </tbody>
</table>

<?php
echo "<br>";
if (isset($_GET['action']) && $_GET['action'] == 'newopeninghoursset') {
    echo "New opening hours are set:<br><br>";
}

foreach ($openingHours as $day) {
    if ($day['Startdate'] !== NULL) { //als er ook maar 1 dag is waar een startdatum voor nieuwe openingstijden staat, dan worden alle nieuwe openingstijden getoond (voor dagen waarbij de openingstijden hetzelfde blijven - waarbij de Admin geen nieuwe openingstijden en startdatum heeft ingevuld - worden de actuele openingstijden gepakt uit de $db).

?>
        <!DOCTYPE html>
        <html>

        <table>
            <caption>New opening hours:</caption>
            <tbody>
                <tr>
                    <th></th>
                    <th>Startdate</th>
                    <th>Open</th>
                    <th>Closed</th>
                </tr>
                <tr>
                    <th>Sunday</th>
                    <td><?php $startDate = $openingHours[0]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate); //de volgorde van de datum wordt aangepast naar Nederlandse maatstaven
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[0]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1]; //de seconden ($openA[2]) worden niet getoond
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[0]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1]; //idem (etc.)
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[0]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[0]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Monday</th>
                    <td><?php $startDate = $openingHours[1]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[1]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[1]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[1]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[1]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Tuesday</th>
                    <td><?php $startDate = $openingHours[2]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[2]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[2]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[2]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[2]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Wednesday</th>
                    <td><?php $startDate = $openingHours[3]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[3]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[3]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[3]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[3]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Thursday</th>
                    <td><?php $startDate = $openingHours[4]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[4]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[4]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[4]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[4]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Friday</th>
                    <td><?php $startDate = $openingHours[5]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[5]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[5]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[5]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[5]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
                <tr>
                    <th>Saturday</th>
                    <td><?php $startDate = $openingHours[6]['Startdate'];
                        if ($startDate !== NULL) {
                            list($year, $month, $day) = explode('-', $startDate);
                            $startDate = [$day, $month, $year];
                            $startDate = implode('-', $startDate);
                            echo $startDate;
                        } ?></td>
                    <td><?php $newOpen = $openingHours[6]['NewOpen'];
                        if ($newOpen !== NULL) {
                            $newOpenA = explode(':', $newOpen);
                            echo $newOpenA[0] . ':' . $newOpenA[1];
                        } elseif ($startDate !== NULL && $newOpen === NULL) {
                            echo "CLOSED";
                        } elseif ($startDate === NULL) {
                            $open = $openingHours[6]['Open'];
                            if ($open !== NULL) {
                                $openA = explode(':', $open);
                                echo $openA[0] . ':' . $openA[1];
                            } else {
                                echo "CLOSED";
                            }
                        } ?></td>
                    <td><?php $newClosed = $openingHours[6]['NewClosed'];
                        if ($newClosed !== NULL) {
                            $newClosedA = explode(':', $newClosed);
                            echo $newClosedA[0] . ':' . $newClosedA[1];
                        } elseif ($startDate !== NULL && $newClosed === NULL) {
                            echo "";
                        } elseif ($startDate === NULL) {
                            $closed = $openingHours[6]['Closed'];
                            if ($closed !== NULL) {
                                $closedA = explode(':', $closed);
                                echo $closedA[0] . ':' . $closedA[1];
                            } else {
                                echo "";
                            }
                        } ?></td>
                </tr>
            </tbody>
        </table>

<?php

        break; //de foreach loop kan gebreakt worden nadat de nieuwe openingstijden worden getoond
    }
}
