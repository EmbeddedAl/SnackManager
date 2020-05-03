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
    
    if (($result = $sqlConnection->query("SELECT 
            orders.id as order_id, orders.event_time as event_time, orders.is_closed, 
            items.id as item_id, items.name as item_name, 
            order_entries.user_id, order_entries.Amount as amount 
        FROM orders JOIN items LEFT OUTER JOIN order_entries ON (orders.id = order_entries.order_id) and (items.id = order_entries.item_id) 
        HAVING ((order_entries.user_id = $user_id) or (order_entries.user_id IS NULL)) and (orders.is_closed = 0) 
        ORDER BY items.name, orders.event_time")) == FALSE)
    {
        return NULL;
    }
    if ($result->num_rows == 0)
    {
        $result->free();
        if (($result = $sqlConnection->query("SELECT 
                orders.id as order_id, orders.event_time as event_time, orders.is_closed, 
                items.id as item_id, items.name as item_name, 0 as amount 
            FROM orders JOIN items WHERE (orders.is_closed = 0) 
            ORDER BY items.name, orders.event_time")) == FALSE)
        {
            return NULL;
        }
    }
    
    $x = array();
    $currItem = "";
    $orders = array();
    $itemcount = 0;
    $ordercount = 0;

    while (($row = $result->fetch_assoc()) != NULL)
    {
        if (strcmp($row["item_id"], $currItem) != 0)
        {
            if (strlen($currItem) != 0)
            {
                //write the last item entries
                $x[$itemcount] = $orders;
                $itemcount += 1;
            }
            $currItem = $row["item_id"];
            $orders = array();
            $ordercount = 0;
        }
        
        $orders[$ordercount] = array("item_id" => $row["item_id"], "item_name" => $row["item_name"], "order_id" => $row["order_id"], "event_time" => $row["event_time"]);
        if ($row["amount"] == NULL)
        {
            $orders[$ordercount]["amount"] = "0";
        }
        else
        {
            $orders[$ordercount]["amount"] = $row["amount"];
        }

        $ordercount += 1;
    }
    $x[$itemcount] = $orders;
    
    $result->free();
    return $x;
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

?>
