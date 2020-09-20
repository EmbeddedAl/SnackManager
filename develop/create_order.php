<?php
    session_start();

    include "sharedphp/sharedHelpers.php";
    include "sharedphp/dbActions.php";
    
    $PageTitle = "Create Event";
    
    
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
    

    $eventDate = date("Y-m-d"); 
    $MessageString = "";
    
    try 
    {
        $newdb = new snackDb();
        
        if (isset($_POST["commit"]))
        {
            $timestring = $_POST['eventDate'];
            $time = new DateTime($timestring);
            if (!$time)
            {
                $MessageString = $timestring . ' is not a valid date</h2>';
            }
            else
            {
                $newdb->createEvent($timestring . ' 12:00:00');
                
                $MessageString = "Event created successfully for " . $timestring;
                
                $time->add(new DateInterval('P7D')); // 1 week later
                $eventDate = date("Y-m-d", $time->getTimestamp());
            }
        }
    } 
    catch (dbException $e) 
    {
        $MessageString = "Database error: ". $e->getMessage();
    }
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <?php include ("layout/title.html"); ?>
        <link rel="stylesheet" href="layout/style.css">
    </head>

    <body>
        <div id="page">
            <?php include ("layout/header.php"); ?>
            <?php include ("layout/nav.html"); ?>

            <div id="content">
            	<?php if (strlen($MessageString) > 0) { echo "<h2>" . $MessageString . "</h2><br/>"; } ?>

                <form action="create_order.php" method="post">
                    <table>
                    	<tr>
                    		<td>Event Date:</td><td><input name="eventDate" type="date" value="<?php echo $eventDate; ?>" /></td>
                    	</tr>
                    	<tr>
                        	<td align="left"></td>
                        	<td align="left"><input type="submit" name="commit" value="register" /></td>
                    	</tr>
                    
                    </table>
                </form>
            </div>
            <?php include ("layout/footer.html"); ?>
        </div><!-- page -->
    </body>
</html>
