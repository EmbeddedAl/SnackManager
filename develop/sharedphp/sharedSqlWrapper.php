<?php

include_once "sharedphp/dbActions.php";

function sharedSqlWrapper_connect()
{
    /* config is needed for connection to db */
    include 'config.php';

    /* open sql connection */
    $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
    if ($sqlConnection->connect_error)
        return null;

    return $sqlConnection;
}


function sharedSqlWrapper_disconnect($sqlConnection)
{
    $sqlConnection->close();
}



function sharedSqlWrapper_getSetting($settingName)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `settings` WHERE `setting` = '" . $settingName . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* this is only exactly one line valid */
    if ($sqlResult->num_rows != 1)
        goto end;

    /* get the actual data base row */
    $sqlRow = $sqlResult->fetch_assoc();

    /* extract the value */
    $returnValue = $sqlRow['value'];

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_setSetting($settingName, $settingValue)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* update into users */
    $sqlStatement = "UPDATE settings SET VALUE ='" . $settingValue . "' where setting = '". $settingName . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


function sharedSqlWrapper_userExists($username)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `username` = '"  . $username . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* if there is a result, the user exists */
    if ($sqlResult->num_rows != 0)
        /* user exists */
        $returnValue = 1;
    else
        /* user does not exits */
        $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}



function sharedSqlWrapper_getFirstName($userid)
{
    $returnValue = "undefined";

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `userid` = '"  . $userid . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* there should be exactly one users */
    if ($sqlResult->num_rows == 1)
    {
        /* get the actual data base row */
        $sqlRow = $sqlResult->fetch_assoc();
        $returnValue = $sqlRow['firstname'];
    }

    end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}



function sharedSqlWrapper_getLastName($userid)
{
    $returnValue = "undefined";

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `userid` = '"  . $userid . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* there should be exactly one users */
    if ($sqlResult->num_rows == 1)
    {
        /* get the actual data base row */
        $sqlRow = $sqlResult->fetch_assoc();
        $returnValue = $sqlRow['lastname'];
    }

    end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function shareSqlWrapper_userCreate($username, $firstname, $lastname, $email, $city, $passwordMD5)
{
    /*
    // open connection

    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return -1;
    */        

    $sqlConnection = NULL;

    try
    {
        $sqlConnection = dbInit();
        dbAddUser($sqlConnection, $username, $passwordMD5, $lastname, $firstname, $email, $city);
    }
    catch (dbException $e)
    {
        return -2;
    }
    finally
    {
        dbClose($sqlConnection);
    }

    return 0;
}


function shareSqlWrapper_updateUserPassword($userid, $passwordMD5)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* update into users */
    $sqlStatement = "UPDATE users SET password ='" . $passwordMD5 . "' where userid = " . $userid;

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


function sharedSqlWrapper_readOpenOrderEntries($user_id)
{
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return NULL;

    if (($orders = $sqlConnection->query("SELECT id, event_time FROM orders WHERE is_closed = 0 ORDER BY event_time")) == FALSE)
    {
        sharedSqlWrapper_disconnect($sqlConnection);
        return NULL;
    }
    $cnt = 0;
    $order_xlat = array();

    while (($row = $orders->fetch_assoc()) != NULL)
    {
        $order_xlat[$row["id"]] = array("cnt" => $cnt, "event_time"=>$row["event_time"]);
        $cnt += 1;
    }

    $orders->free();


    if (($items = $sqlConnection->query("SELECT id, name FROM items ORDER BY name")) == FALSE)
    {
        sharedSqlWrapper_disconnect($sqlConnection);
        return NULL;
    }
    $cnt = 0;
    $item_xlat = array();
    while (($row = $items->fetch_assoc()) != NULL)
    {
        $item_xlat[$row["id"]] = array("cnt" => $cnt, "name"=>$row["name"]);
        $cnt += 1;
    }
    $items->free();
    

    $amounts=array();
    for ($i=0; $i<count($item_xlat); $i++)
    {
        $amounts[$i] = array();
        for ($j=0; $j<count($order_xlat); $j++)
        {
            $amounts[$i][$j] = array("amount" => 0);
        }
    }

    
    foreach(array_keys($item_xlat) as $itemkey)
    {
        $itemindex = $item_xlat[$itemkey]["cnt"];
        foreach(array_keys($order_xlat) as $orderkey)
        {
            $orderindex = $order_xlat[$orderkey]["cnt"];
            $amounts[$itemindex][$orderindex]["item_id"] = $itemkey;
            $amounts[$itemindex][$orderindex]["item_name"] = $item_xlat[$itemkey]["name"];
            $amounts[$itemindex][$orderindex]["order_id"] = $orderkey;
            $amounts[$itemindex][$orderindex]["event_time"] = $order_xlat[$orderkey]["event_time"];
        }
    }

    
    if (($entries = $sqlConnection->query("SELECT order_entries.order_id, order_entries.item_id, order_entries.amount FROM order_entries JOIN orders ON order_entries.order_id = orders.id WHERE user_id = $user_id AND orders.is_closed = 0")) == FALSE)
    {
        sharedSqlWrapper_disconnect($sqlConnection);
        return NULL;
    }
    while (($row = $entries->fetch_assoc()) != NULL)
    {
        $orderindex = $order_xlat[$row["order_id"]]["cnt"];
        $itemindex = $item_xlat[$row["item_id"]]["cnt"];
        $amounts[$itemindex][$orderindex]["amount"] = $row["amount"];
    }
    $entries->free();

    return $amounts;
}


function sharedSqlWrapper_setOrderEntry($user_id, $order_id, $item_id, $amount)
{
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return NULL;

    $need_insert = TRUE;
    if (($result = $sqlConnection->query("SELECT * from order_entries WHERE order_id=$order_id AND item_id=$item_id AND user_id=$user_id")) != FALSE)
    {
        if ($result->num_rows > 0)
        {
            $need_insert = FALSE;
        }
        $result->free();
    }
    if ($need_insert == TRUE)
    {
        $sqlConnection->query("INSERT INTO order_entries(order_id, item_id, user_id, amount) VALUES ($order_id, $item_id, $user_id, $amount)");
    }
    else
    {
        $sqlConnection->query("UPDATE order_entries SET amount=$amount WHERE order_id = $order_id AND item_id = $item_id AND user_id = $user_id");
    }
}

function sharedSqlWrapper_getOrderTotals()
{
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return NULL;

    $x = array();

    // "SELECT orders.event_time, items.name, order_entries.Amount FROM order_entries JOIN orders ON order_entries.order_id = orders.id JOIN items ON order_entries.item_id = items.id"
}

?>
