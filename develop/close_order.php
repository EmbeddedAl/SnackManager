<?php session_start(); ?>

<html>
    <head>
        <?php include ("layout/title.html"); ?>
        <link rel="stylesheet" href="layout/style.css">
    </head>

    <body>
        <div id="page">
            <?php include ("layout/header.html"); ?>
            <?php include ("layout/nav.html"); ?>

            <div id="content">
<?php
include_once "sharedphp/dbActions.php";



function doCloseOrder($db, $order_id)
{
    try {
        if ($order_id == NULL)
        {
            echo "id is null";
            return FALSE;
        }
        
        dbCloseOrder($db, $_SESSION["userid"], $order_id);
            
        return TRUE;
    }
    catch (dbException $e) {
        if ($e->errorCode == dbException::ERR_ORDER_CLOSED)
        {
            return FALSE;
        }
        throw $e;
    }
}

function doShowOrder($db, $order_id)
{
    try {
        if ($order_id == NULL)
            return FALSE;

        $summary = dbGetOrderSummary($db, $order_id);

        echo '<h2>Summary of order ' . $summary["event_time"] . '</h2>';
        echo '<form action="close_order.php" method="post">';
        echo '<table>';
        echo '<tr><td>Item</td><td>Quantity</td><tr>';
        foreach ($summary["items"] as $item)
        {
            echo '<tr>';
            echo '<td>' . $item['name'] . '</td>';
            echo '<td>' . $item['amount'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // get order lines: username, item_name, amount (amount > 0)
        
        $details = dbGetOrderDetails($db, $order_id);
        echo '<h2>Details of order ' . $summary["event_time"] . '</h2>';
        echo '<table>';
        echo '<tr><td>User</td><td>Item</td><td>Quantity</td><tr>';
        foreach ($details as $user)
        {
            $username = $user["username"];
            foreach ($user["items"] as $item)
            {
                echo "<tr>";
                echo "<td>".$username."</td>";
                echo "<td>".$item["name"]."</td>";
                echo "<td>".$item["amount"]."</td>";
                echo "</tr>";
                $username = "";
            }
        }
        echo '<tr>';
        if ($summary["is_closed"] != 0)
        {
            echo '<td align="left"><input type="submit" name="return" value="ok" /></td>';
        }
        else
        {
            echo '<td align="left"><input type="hidden" name="order" value="' . $order_id . '"></td>';
            echo '<td align="left"><input type="submit" name="close" value="Close this Order" /></td>';
            echo '<td align="left"></td>';
        }
        echo '</tr>';
        echo '</table>';
            
        return TRUE;
    } 
    catch (dbException $e) {
        if ($e->errorCode == dbException::ERR_ORDER_CLOSED)
        {
            return FALSE;
        }
        throw $e;
    }
}


// require user to be logged in
if (!isset($_SESSION["userid"]))
{
    writeErrorPage("Access denied. You must log in first!");
    return;
}

// require user to be admin
if (!isset($_SESSION["isadmin"]))
{
    writeErrorPage("Access denied. Admin rights required.");
    return;
}


try {
    $db = dbInit();
    $message = "<h1>Show Order</h1>";
    
    $processed = FALSE;
    
    if (($processed == FALSE) && isset($_POST['really']))
    {
        $order_id = $_POST['order'];
        if (doCloseOrder($db, $order_id) == TRUE)
        {
            $processed = TRUE;
            $message = "<h1>Order was closed</h1>";
        }
        else 
        {
            $_POST["show"] = 1;                 
            $message = "<h1>Order could not be closed</h1>";
        }
    }
    if (($processed == FALSE) && isset($_POST['close']))
    {
        $order_id = $_POST['order'];
        echo '<form action="close_order.php" method="post">';
        echo '<table>';
        echo '<tr><td/><td>Relly? Are you sure?</td><td/></tr>';
        echo '<tr>';
        echo '<td align="left"><input type="hidden" name="order" value="' . $order_id . '"></td>';
        echo '<td align="left"><input type="submit" name="really" value="!Yes!" /></td>';
        echo '<td align="left"></td>';
        echo '</tr>';
        echo '</table>';
        echo '</form>';
        $processed = TRUE;
    }
    
    if (($processed == FALSE) && isset($_POST['show']))
    {
        $order_id = $_POST['order'];
        echo $message;
        if (doShowOrder($db, $order_id) == TRUE)
            $processed = TRUE;
    }

    if ($processed == FALSE)
    {
        $orders = dbGetFutureOrders($db);
        
        echo '<h2>Select order to display</h2>';
        echo '<form action="close_order.php" method="post">';
        echo '<table>';
        echo '<tr>';
        echo '<select name="order" size="3">';
        foreach ($orders as $order)
        {
            $ordername = $order['event_time'];
            if (0 != $order['is_closed'])
                $ordername = $ordername . "(closed)";
            $order_id = $order['id'];
            echo '<option value="' . $order_id . '">' . $ordername . '</option/>';
        }
        echo '</select>';
        echo '<tr>';
        echo '<td align="left"></td>';
        echo '<td align="left"><input type="submit" name="show" value="ok" /></td>';
        echo '<td align="left"></td>';
        echo '</tr>';
        echo '</table>';
        echo '</form>';
    }
}
catch (dbException $e) {
    echo "<h2> Database error:" . $e->getMessage() . "</h2>";
    
}
finally {
    dbClose($db);
}

?>


            </div><!-- content -->
            <?php include ("layout/footer.html"); ?>
        </div><!-- page -->
    </body>
</html>
