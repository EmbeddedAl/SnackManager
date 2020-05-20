<?php 
    session_start();
    
    include "sharedphp/sharedHelpers.php";
    
    // require user to be logged in
    if (!isset($_SESSION["userid"]))
    {
        writeErrorPage("Access denied. You must log in first!");
        return;
    }
    
    include_once "sharedphp/dbActions.php";
    //include 'sharedphp/sharedInputCheck.php';
    //include "sharedphp/sharedSqlWrapper.php";
    
    try
    {
        $newdb = new snackDb();
        $AccountBalance = 0 - $newdb->getAccountBalance($newdb->getAccountIdForUser($_SESSION['userid']));
    }
    catch (dbException $e)
    {
        writeErrorPage("Unrecoverable error accessing the database.");
        return;
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
            <?php 
                $PageTitle = 'Change Order'; 
                include ('layout/header.php');
                include ('layout/nav.html'); 
            ?>

            <div id="content">
<?php

    $user_id = $_SESSION["userid"];

    if (isset($_POST["commit"]))
    {
        try 
        {
            foreach (array_keys($_POST) as $key)
            {
                if (strcmp(substr($key, 0, 2), "__") == 0)
                {
                    $item_id = strtok(substr($key, 2), "_");
                    $order_id = strtok("_");
                    
                    $newdb->setOrderEntry($user_id, $order_id, $item_id, $_POST[$key]);
                }
            }
        } 
        catch (dbException $e) 
        {
            echo "<br/> database error: " . $newdb->db->error . "<br/>";
        }
        
        // wipe last submit
        $_POST=array();
    }
    
    if ($AccountBalance < 0)
    {
        echo "<h2>Insufficient funds. Please pay first</h2>";
    }
    else 
    {
        try 
        {
            $x = $newdb->getOpenOrderData($user_id);
            
            // build the form
            echo '<form action="change_order.php" method="post">';
            echo '  <table>';
            
            // erst mal die Überschriften
            echo '<tr><th>Item</th><th align="right">Price</th>';
            foreach($x[0] as $column)
                echo '<th>' . $column['event_time'] . '</th>';
                echo '</tr>';
                
                foreach ($x as $row)
                {
                    
                    echo '<tr>';
                    
                    echo '<td align="left">' . $row[0]['item_name'] . '</td>';
                    echo '<td align="right">' . formatCurrency($row[0]['price']) . '</td>';
                    foreach($row as $column)
                    {
                        $input_name="__" . $column['item_id'] . '_' . $column['order_id'];
                        echo '<td align="left">';
                        echo '<input name="' . $input_name . '" type="number" min="0" value="' . $column["amount"] . '">';
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                echo '<tr> <td></td> <td align="left"><input type="submit" name="commit" value="save" /></td> <td></td>';
                echo '  </table>';
                echo '  </form>';
        } 
        catch (dbException $e) 
        {
            echo "<h2>Error reading the database: " . $newdb->db->error . "</h2>";
        }        
    }
?>
            </div><!-- content -->
            <?php include ("layout/footer.html"); ?>
        </div><!-- page -->
    </body>
</html>
