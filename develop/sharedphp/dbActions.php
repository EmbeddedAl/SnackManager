<?php

/*
Buchhalterung:
Wir haben Kasse (cash), Einkauf (procurement), Verkauf (sales) und User.
Die Summe ist immer null.

Einzahlung einer Summe eines Users:
    Kasse += Summe: Wir haben mehr Bares
    User -= Summe; Wir haben mehr Schulden beim User

Einkauf über Summe gegen Bargeld:
    Einkauf += Summe: Der Händler hat mehr Schulden bei uns (und gleicht sie in Naturalien aus)
    Kasse -= Summe: Bezahlen müssen wir trotzdem

Abrechnung einer Bestellung über Summe von User:
    User += Summe: Wir haben weniger Schulden beim User
    Verkauf -= Summe: Dafür mehr Schulden beim Mülleimer, wir zahlen mit Naturalien

Am Ende sagt:
Kasse: wieviel Bares wir haben sollten
User: unser Vermögen beim User, d.h. solange der User Guthaben bei uns hat ist der Wert negativ
Verkauf: Wieviel wir insgesamt verkauft haben (Wir haben Geld bekommen, also negativ)
Einkauf: Wieviel wir insgesamt eingekauft haben (wir haben Geld hergegeben, also positiv)
Unser verfügbares Vermögen ist also Kasse + Summe(User)
*/

include 'config.php';

class dbException extends Exception
{
    public $errorCode;
    
    const ERR_GENERIC = 0;
    const ERR_ORDER_CLOSED = 1;
    
    public function __construct($message, $code = ERR_GENERIC)
    {
        parent::__construct($message);
        $this->errorCode = $code;
    }
}


class queryResult
{
    public $record = null;
    private $data = null;
    
    public function __construct($result)
    {
        $this->data = $result;        
    }
    
    public function __destruct()
    {
        $this->data->close();
    }

    public function nextRecord()
    {
        $this->record = $this->data->fetch_assoc();
        return ($this->record != null);
    }
}

class dbDatabase
{
    public $db = NULL;
    public $transactionCount = 0;
    
    public function __construct()
    {
        global $dbhost;
        global $dbuser;
        global $dbpass;
        global $dbase;
        
        // open sql connection
        $this->db = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );

        if ($this->db->connect_error)
        {
            throw new dbException('Error connecting the database: ' . $this->db->error);
        }
        
    }

    
    public function __destruct()
    {
        if ($this->db != NULL)
        {
            $this->db->close();
        }
    }
    
    
    public function beginTransaction()
    {
        if ($this->transactionCount == 0)
        {
            $this->db->autocommit(false);
        }
        $this->transactionCount += 1;
    }
    
    
    public function commitTransaction()
    {
        if ($this->transactionCount == 0)
        {
            throw new dbException('No active transaction for commit');
        }
        
        $this->transactionCount -= 1;
        
        if ($this->transactionCount == 0)
        {
            if ($this->db->commit() != true)
            {
                $this->db->rollback();
                $this->db->autocommit(true);

                throw new dbException('Commit transaction failed');
            }

            $this->db->autocommit(true);
        }
    }

    
    public function abortTransaction()
    {
        if ($this->transactionCount == 0)
        {
            throw new dbException('No active transaction for commit');
        }
        
        $this->db->rollback();
        $this->db->autocommit(true);
        $this->transactionCount -= 1;
        
        if ($this->transactionCount != 0)
        {
            $this->transactionCount = 0;
            throw new dbException('Aborting a nested transaction is not possible');
        }
    }
    
    
    public function query($query)
    {
        $result = $this->db->query($query);

        if ($result == false)
            throw new dbException('Query failed:' . $this->db->error);

        return new queryResult($result);
    }
    

    public function affectedRows()
    {
        return $this->db->affected_rows;
    }

}


class snackDb extends dbDatabase
{
    public $cash_accountId = NULL;
    public $procurement_accountId = NULL;
    public $sales_accountId = NULL;
    
    public function __construct()
    {
        try {
            parent::__construct();
            
            $managementInfo = $this->query("SELECT * from management LIMIT 1");
            $managementInfo->nextRecord();
            
            $this->cash_accountId = $managementInfo->record['cash_id'];
            $this->procurement_accountId = $managementInfo->record['procurement_id'];
            $this->sales_accountId = $managementInfo->record['sales_id'];
        } 
        catch (Exception $e) {
            throw new dbException("Cannot read management table: " . $this->db->error);
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
    

    public function getAccountIdForUser($userId)
    {
        $resultSet = $this->query('SELECT account_id FROM users WHERE userid = ' . $userId);
        
        if (!$resultSet->nextRecord())        
            throw new dbException('Cannot find user id ' . $userId . 'in accounts');
        
        return $resultSet->record['account_id'];
    }
    
    
    public function setPasswordHashForUser($userId, $passwordHash)
    {
        $query = "UPDATE users SET password ='" . $passwordHash . "' where userid = " . $userId;
        echo "<br/>" . $query . "<br/>";
        if (!$this->db->query("UPDATE users SET password ='" . $passwordHash . "' where userid = " . $userId))
        {
            throw new dbException('Update of password failed: ' . $this->db->error);
        }
    }
    
    
    public function getAccountName($accountId)
    {
        switch ($accountId)
        {
            case $this->cash_accountId:
                return 'Cash Register';
            case $this->procurement_accountId:
                return "Procurement";
            case $this->sales_accountId:
                return "Sales";
            default:
                return $accountId;
        }
    }
    
    
    public function getAccountBalance($accountId)
    {
        $accountData = $this->query('SELECT balance FROM accounts WHERE id = ' . $accountId);
        if (!$accountData->nextRecord())
        {
            throw new dbException("Cannot read account balance: " . $this->db->error);
        }
        
        return $accountData->record['balance'];
        
    }
    
    
    public function getAccountHistory($userId)
    {
        $accountId = $this->getAccountIdForUser($userId);

        $result = array('balance' => $this->getAccountBalance($accountId), 'history' => array());

        $historyData = $this->query('SELECT * FROM account_log WHERE source_id = ' . $accountId . ' OR target_id = ' . $accountId . ' ORDER BY timestamp DESC');
        $cnt = 0;
        
        while ($historyData->nextRecord())
        {
            if ($historyData->record['source_id'] == $accountId)
            {
                $otherId = $historyData->record['target_id'];
                $entryValue = $historyData->record['amount'];
            }
            else
            {
                $otherId = $historyData->record['source_id'];
                $entryValue = 0 - $historyData->record['amount'];
            }
            $result['history'][$cnt++] = array('timestamp' => $historyData->record['timestamp'], 'entryValue' => $entryValue, 'ContraAccount' => $otherId, 'Comment' => $historyData->record['comment']);
        }
        
        return $result;
    }
    
    public function getOpenOrderData($userId)
    {
        $orders = $this->query("SELECT id, event_time FROM orders WHERE is_closed = 0 ORDER BY event_time ASC");
        $cnt = 0;
        $order_xlat = array();
        
        while ($orders->nextRecord())
        {
            $order_xlat[$orders->record["id"]] = array("cnt" => $cnt, "event_time"=>$orders->record["event_time"]);
            $cnt += 1;
        }
        if (sizeof($order_xlat) == 0)
        {
            throw new dbException("No open events found.");
        }
        
        $items = $this->query("SELECT id, name, price FROM items ORDER BY name");            
        $cnt = 0;
        $item_xlat = array();
        
        while($items->nextRecord())
        {
            $item_xlat[$items->record["id"]] = array("cnt" => $cnt, "name"=>$items->record["name"], 'price' => $items->record['price']);
            $cnt += 1;
        }
        if (sizeof($item_xlat) == 0)
        {
            throw new dbException("No items found.");
        }

        // create array that contains eventdata, itemdata, ordered_qty for each item OUTER JOIN event
        $amounts=array();
        for ($i=0; $i<count($item_xlat); $i++)
        {
            $amounts[$i] = array();
            for ($j=0; $j<count($order_xlat); $j++)
            {
                $amounts[$i][$j] = array("amount" => 0);
            }
        }
            
        foreach(array_keys($item_xlat) as $itemId)
        {
            $itemindex = $item_xlat[$itemId]["cnt"];
            foreach(array_keys($order_xlat) as $orderId)
            {
                $orderindex = $order_xlat[$orderId]["cnt"];
                $amounts[$itemindex][$orderindex]["item_id"] = $itemId;
                $amounts[$itemindex][$orderindex]["item_name"] = $item_xlat[$itemId]["name"];
                $amounts[$itemindex][$orderindex]["price"] = $item_xlat[$itemId]["price"];
                $amounts[$itemindex][$orderindex]["order_id"] = $orderId;
                $amounts[$itemindex][$orderindex]["event_time"] = $order_xlat[$orderId]["event_time"];
            }
        }
            
        // fill in the ordered quantities for this user from the database
        $entries = $this->query("SELECT order_entries.order_id, order_entries.item_id, order_entries.amount FROM order_entries JOIN orders ON order_entries.order_id = orders.id WHERE order_entries.user_id = $userId AND orders.is_closed = 0");
        
        // it is ok if we have an empty result set here
        while ($entries->nextRecord())
        {
            $orderindex = $order_xlat[$entries->record["order_id"]]["cnt"];
            $itemindex = $item_xlat[$entries->record["item_id"]]["cnt"];
            $amounts[$itemindex][$orderindex]["amount"] = $entries->record["amount"];
        }
        
        return $amounts;
    }
    
    public function setOrderEntry($userId, $orderId, $itemId, $qty)
    {
        if ($qty == 0)
        {
            $this->db->query("DELETE FROM order_entries WHERE order_id = $orderId AND item_id = $itemId AND user_id = $userId");
        }
        else 
        {
            $result = $this->query("SELECT * from order_entries WHERE order_id=$orderId AND item_id=$itemId AND user_id=$userId");
            
            if ($result->nextRecord())
            {
                // exists, do UPDATE
                $this->db->query("UPDATE order_entries SET amount=$qty WHERE order_id = $orderId AND item_id = $itemId AND user_id = $userId");
            }
            else
            {
                $this->db->query("INSERT INTO order_entries(order_id, item_id, user_id, amount) VALUES ($orderId, $itemId, $userId, $qty)");
            }
        }
    }
}




function dbInit($db=NULL)
{
    if ($db == NULL)
    {
        // config is needed for connection to db
        global $dbhost;
        global $dbuser;
        global $dbpass;
        global $dbase;
        
        include_once 'config.php';

        // open sql connection
        $db = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
        if ($db->connect_error)
        {
            throw new dbException("Error connecting the database: " . $db->error);
        }
    }

    if ($result = $db->query("SELECT * from management LIMIT 1"))
    {
        $record = $result->fetch_assoc();

        $GLOBALS['cash_id'] = $record["cash_id"];
        $GLOBALS['procurement_id'] = $record["procurement_id"];
        $GLOBALS['sales_id'] = $record["sales_id"];
    }
    else
    {
        throw new dbException("Cannot read management table: " . $db->error);
    }
    return $db;
}

function dbClose($db)
{
    if ($db != NULL)
        $db->close();
}

function dbDropTable($db, $table_name)
{
    if ($db->query("DROP TABLE IF EXISTS $table_name" ) != TRUE)
    {
        echo "Error dropping table '$table_name': " . $db->error . "<br>";
    }
    else
    {
        echo "Table '$table_name' dropped<br>";
    }
    return;
}

function dbGetAutocommit($db)
{
    if ($result = $db->query("SELECT @@autocommit"))
    {
        $row = $result->fetch_row();
        $auto = $row[0];
        $result->free();
        return $auto;
    }
    throw new dbException("cannot determine autocommit status");
}


function dbValidateUserId($db, $user_id)
{
    if ($result = $db->query("SELECT account_id FROM users WHERE userid = $user_id")) 
    {
        $rows = $result->num_rows;
        $result->free();
        if ($rows == 1)
            return;
    }
    throw new dbException("Cannot find user $user_id");
}


function dbValidateAccountId($db, $account_id)
{
    if ($result = $db->query("SELECT id FROM accounts WHERE id = $account_id")) 
    {
        $rows = $result->num_rows;
        $result->free();
        if ($rows == 1)
            return;
    }
    throw new dbException("Cannot find account $account_id");
}


function dbAddUser($db, $username, $password, $lastname="", $firstname="", $mail="", $city="", $isAdmin=0, $max_credit=0)
{
    $auto = dbGetAutocommit($db);

    try 
    {
        $db->autocommit(FALSE);

        if ($db->query("INSERT INTO accounts (id, balance) VALUES (NULL, 0)") != TRUE)
        {
            throw new dbException("Creating of account failed at dbAddUser: " . $db->error);
        }
        $account_id = $db->insert_id;

        if ($db->query( "INSERT INTO users (lastname, firstname, username, email, city, password, isadmin, account_id, max_credit) ".
                        "VALUES ('$lastname', '$firstname', '$username', '$mail', '$city', '$password', $isAdmin, $account_id, $max_credit)") != TRUE)
        {
            throw new dbException("Creating of user failed at dbAddUser: " . $db->error);
        }
        $user_id = $db->insert_id;

        if ($db->commit() != TRUE)
        {
            throw new dbException("COMMIT failed at dbAddUser: " . $db->error);
        }
    }
    catch (dbException $e)
    {
        $db->rollback();
        throw $e;
    }
    finally
    {
        $db->autocommit($auto);
    }

    return $user_id;
}


function dbGetUserId($db, $username)
{
    if ($result = $db->query("SELECT userid FROM users WHERE username = '$username'")) {
        $row = $result->fetch_row();
        $id = $row[0];
        $result->free();
        return $id;
    }

    throw new dbException("Cannot find username '$username'");
}

function dbGetAccountIdForUser($db, $user_id)
{
    if ($result = $db->query("SELECT account_id FROM users WHERE userid = $user_id")) {
        $row = $result->fetch_row();
        $account_id = $row[0];
        $result->free();
        return $account_id;
    }
    throw new dbException("Cannot find user id '$user_id'");
}


function dbTransferMoney($db, $source, $target, $executor, $amount, $comment, $join_transaction=0)
{
    if ($join_transaction == 0)
    {
        $auto = dbGetAutocommit($db);
    }
    
    try 
    {
        dbValidateAccountId($db, $source);
        dbValidateAccountId($db, $target);
        dbValidateUserId($db, $executor);

        if ($join_transaction == 0)
            $db->autocommit(FALSE);

        if ($db->query("INSERT INTO account_log (source_id, target_id, amount, comment, executor_id) VALUES ($source, $target, $amount, '$comment', $executor)") != TRUE)
        {
            throw new dbException("Error writing account_log: " . $db->error);
        }

        if ($db->query("UPDATE accounts SET balance = balance - $amount WHERE id = $source") != TRUE)
        {
            throw new dbException("Error withrawing Cash from $source: " . $db->error);
        }

        if ($db->query("UPDATE accounts SET balance = balance + $amount WHERE id = $target") != TRUE)
        {
            throw new dbException("Error adding Cash to $target: " . $db->error);
        }

        if ($join_transaction == 0)
        {
            if ($db->commit() != TRUE)
            {
                throw new dbException("COMMIT failed at dbAddUser: " . $db->error);
            }
        }
    }
    catch (dbException $e)
    {
        $db->rollback();
        throw $e;
    }
    finally
    {
        if ($join_transaction == 0)
        {
            $db->autocommit($auto);
        }
    }
    return;
}


function dbGetFutureOrders($db)
{
    if ($orders = $db->query("SELECT id, event_time, is_closed FROM orders WHERE CAST(event_time AS DATE) >= CURRENT_DATE()")) {
        
        $result = array();
        
        while (($row = $orders->fetch_assoc()) != NULL)
        {
            $result[$row['id']] = array ('id' => $row['id'], 'event_time' => $row['event_time'], 'is_closed' => $row['is_closed']);
        }
        
        $orders->free();
        
        return $result;
    }
    throw new dbException("Problems retrieving future orders");
}


function dbCloseOrder($db, $executor_id, $order_id)
{
    global $sales_id;
    
    if (($orders = $db->query("SELECT orders.* FROM orders WHERE orders.id = $order_id")) == NULL)
        throw new dbException("Problems retrieving order $order_id");
        
    if (($row = $orders->fetch_assoc()) == NULL)
        throw new dbException("Problems retrieving order $order_id");
    
    $closed = $row["is_closed"];
    $orders->free;
    
    if ($closed != 0)
    {
        throw new dbException("Order already closed", dbException::ERR_ORDER_CLOSED);
    }
    
    // get entries
    // SELECT order_entries.user_id, sum(order_entries.Amount * items.price) as amount FROM `order_entries` join items on order_entries.item_id = items.id WHERE order_entries.order_id = 1 group by order_entries.user_id
    
    if (($orders = $db->query("SELECT order_entries.user_id, sum(order_entries.Amount * items.price) as amount FROM `order_entries` join items on order_entries.item_id = items.id WHERE order_entries.order_id = $order_id group by order_entries.user_id")) == NULL)
    {
        throw new dbException("Problems retrieving order entries for $order_id");
    }
    
    try {
        $auto = dbGetAutocommit($db);
        $db->autocommit(FALSE);
        $comment = "Close Order $order_id";
        
        if (!($db->query('UPDATE orders SET is_closed=1 WHERE id = ' . $order_id )))
        {
            throw new dbException("Cannot close order");
        }
        
        while (($row = $orders->fetch_assoc()) != NULL)
        {
            $userAccount = dbGetAccountIdForUser($db, $row["user_id"]);
            
            dbTransferMoney($db, $sales_id, $userAccount, $executor_id, $row["amount"], $comment, 1);
        }
        
        $db->commit();
        
    } catch (dbException $e) {
        $db->rollback();
        throw $e;
    }
    finally {
        $db->autocommit($auto);
    }
}


function dbGetOrderSummary($db, $order_id)
{
    if ($orders = $db->query("SELECT orders.is_closed, orders.event_time, SUM(order_entries.Amount) AS qty, items.name FROM orders JOIN order_entries ON orders.id = order_entries.order_id JOIN items ON order_entries.item_id = items.id WHERE orders.id = $order_id GROUP BY orders.id, items.id ORDER BY items.name")) {
        
        $result = array();
        $result["items"] = array();
        $cnt = 0;
        
        while (($row = $orders->fetch_assoc()) != NULL)
        {
            $result["event_time"] = $row["event_time"];
            $result["is_closed"] = $row["is_closed"];
            $item = array("name" => $row["name"], "amount" => $row["qty"]);
            $result["items"][$cnt] = $item;
            $cnt += 1;
        }
        
        $orders->free();
        
        return $result;
    }
    throw new dbException("Problems retrieving order summary");
}


function dbGetOrderDetails($db, $order_id)
{
    if ($orders = $db->query("SELECT users.username, items.name, order_entries.amount FROM users JOIN order_entries ON users.userid = order_entries.user_id JOIN items ON order_entries.item_id = items.id WHERE order_entries.order_id = $order_id and order_entries.amount > 0 ORDER BY username, name")) {
        
        $result = array();
        $cnt = 0;
        $itemCnt = 0;
        $currUser = "";
        $user = array("items" => array());
        
        while (($row = $orders->fetch_assoc()) != NULL)
        {
            if (strcmp($currUser, $row["username"]) != 0)
            {
                if (strlen($currUser) > 0)
                    $result[$cnt++] = $user;
                
                $user = array("items" => array());
                $itemCnt = 0;
                $currUser = $row["username"];
            }
            
            $item = array("name" => $row["name"], "amount" => $row["amount"]);
            $user["username"] = $row["username"];
            $user["items"][$itemCnt++] = $item;
        }
        $result[$cnt++] = $user;
        
        $orders->free();
        
        return $result;
    }
    throw new dbException("Problems retrieving order summary");
}

function dbGetAccountHistory($db, $user_id)
{
    $accountNo = dbGetAccountIdForUser($db, $user_id);
    
    if ($account = $db->query("SELECT balance FROM accounts WHERE id = " . $accountNo)) {
        $result = array("currentAmount" => $account->fetch_assoc()["balance"], "history" => array());
    }
    else
        throw new dbException("Problems retrieving account information");
        
    if ($entries = $db->query("SELECT * FROM account_log WHERE source_id = ". $accountNo . " OR target_id = " . $accountNo . " ORDER BY timestamp DESC")) {
        $cnt = 0;
        
        while (($entry = $entries->fetch_assoc()) != NULL)
        {
            if ($entry["source_id"] == $account)
            {
                $other = $entry["target_id"];
                $amount = $entry["amount"];
                
            }
            else
            {
                $other = $entry["source_id"];
                $amount = 0-$entry["amount"];
            }
            
            $result["history"][$cnt] = array("Date" => $entry["timestamp"], "AmountChange" => $amount, "OtherAccount" => $other, "Comment" => $entry["comment"]);
            $cnt += 1;
        }
        
        return $result;
    }
    throw new dbException("Problems retrieving account history");
    
}

function dbCreateOrder($db, $time)
{
    $timevar = new DateTime($time);
    $delta = new DateInterval('PT21H');
    $closeTime = $timevar->sub($delta);

    if ($db->query("SELECT * from orders where event_time = '" . $closeTime->format('Y-m-d H:i:s') . "' AND is_closed = 0"))
    {
        throw new dbException("Event already exists");
    }
    
    if ($db->query("INSERT INTO orders (event_time, closing_time, is_closed) VALUES (" . $timevar->format('Y-m-d H:i:s') . ", " . $closeTime->format('Y-m-d H:i:s') . ", 0)"))
    {
        
    }
    else
    {
        throw new dbException("Problem creating order");
    }
    return;
}
?>

