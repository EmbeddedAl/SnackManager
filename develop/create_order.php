<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<html>
<body>

<?php
include 'config.php';
include_once "sharedphp/dbActions.php";

/* require user to be logged in */
if (!isset($_SESSION["userid"]))
{
    writeErrorPage("Access denied. You must log in first!");
    return;
}

/* require user to be admin */
if (!isset($_SESSION["isadmin"]))
{
    writeErrorPage("Access denied. Admin rights required.");
    return;
}

$year = date("Y");
$month = date("m");
$day = date("d");

if (isset($_POST["commit"]))
{
    $year = $_POST["eventYear"];
    $month = $_POST["eventMonth"];
    $day = $_POST["eventDay"];
    $timestring = $year . "-" . $month . "-" . $day . ' 12:00:00';
    if (checkdate($month, $day, $year))
    {
         try {
             $db = dbInit();
             dbCreateOrder($db, $timestring);
            echo "<h2> Event created successfully</h2>";
            
        } catch (dbException $e) {
            echo "<h2> Database error: " . $e->getMessage() . "</h2>";
        }
        finally {
            dbClose($db);
        }
    }
    else
    {
        echo '<h2>' . $timestring . ' is not a valid date</h2>';
    }
}

echo '<form action="create_order.php" method="post">';
echo '<table>';

echo '<tr><td>Event Date:</td><td><input name="eventYear" value="'. $year . '"/>-<input name="eventMonth" value="'.$month.'"/>-<input name="eventDay" value="'.$day.'"/></tr>';
echo '<tr>';
echo '<td align="left"></td>';
echo '<td align="left"><input type="submit" name="commit" value="register" /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';


?>
</body>
</html>
