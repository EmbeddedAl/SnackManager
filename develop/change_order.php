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
                <h2>Change order</h2>
<?php
    include_once "sharedphp/dbActions.php";
    include 'sharedphp/sharedInputCheck.php';
    include "sharedphp/sharedSqlWrapper.php";

    // require user to be logged in
    if (!isset($_SESSION["userid"]))
    {
        writeErrorPage("Access denied. You must log in first!");
        return;
    }

    $user_id = $_SESSION["userid"];

    if (isset($_POST["commit"]))
    {
        foreach (array_keys($_POST) as $key)
        {
            if (strcmp(substr($key, 0, 2), "__") == 0)
            {
                $item_id = strtok(substr($key, 2), "_");
                $order_id = strtok("_");
                
                sharedSqlWrapper_setOrderEntry($user_id, $order_id, $item_id, $_POST[$key]);
            }
        }

        $_POST=array();
    }

    $x = sharedSqlWrapper_readOpenOrderEntries($user_id);
    
    if ($x == NULL)
    {
        echo "<h2>Error reading the database</h2>";
        return;
    }
    // build the form
    echo "<form action=\"change_order.php\" method=\"post\">";
    echo "  <table>";

    // erst mal die Überschriften
    echo "<tr><td>Item</td>";
    foreach($x[0] as $column)
        echo "<td>" . $column["event_time"] . "</td>";
    echo "</tr>";

    foreach ($x as $row)
    {

        echo "<tr>";

        echo "<td align=\"left\">" . $row[0]["item_name"] . "</td>";
        foreach($row as $column)
        {
            $input_name="__" . $column["item_id"] . "_" . $column["order_id"];
            echo "<td align=\"left\">";
            echo "<input name=\"" . $input_name . "\" value=\"" . $column["amount"] . "\">";
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "<tr> <td></td> <td align=\"left\"><input type=\"submit\" name=\"commit\" value=\"register\" /></td> <td></td>";
    echo "  </table>";
    echo "  </form>";

?>
            </div><!-- content -->
            <?php include ("layout/footer.html"); ?>
        </div><!-- page -->
    </body>
</html>
