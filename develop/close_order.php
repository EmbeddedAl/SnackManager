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
            <h2>Close Order</h2>

<?php
include_once "sharedphp/dbActions.php";

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

    if (isset($_POST['commit']) && isset($_POST['order']))
    {
        $order_id = $_POST['order'];
        dbCloseOrder($db, $_SESSION["userid"], $order_id);
        
        // close(order)
        // get orders.event_time, items.name, sum(amount)
        $summary = dbGetOrderSummary($db, $order_id);
        echo '<h2>Summary of order ' . $summary["event_time"] . '</h2>';
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
        echo '</table>';
/*        
        $to="wuff@thequiet.place";
        $from="mailer@snackmanager.org";
        $subject="Order list";
        $txt="totals:\r\n";
        
        $headers="From: $from";
        mail($to, $subject, $txt, $headers);
*/        
        
    }
    
    $_POST = array();
    $orders = dbGetFutureOrders($db);

    echo '<h2>Select order to close</h2>';
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
    echo '<td align="left"><input type="submit" name="commit" value="ok" /></td>';
    echo '<td align="left"></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';
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
