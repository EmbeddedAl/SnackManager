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

if ($__dbActionsIncluded__ == TRUE)
    return;

$__dbActionsIncluded__ = TRUE;

class dbException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}


function dbInit($db=NULL)
{
    global $cash_id;
    global $procurement_id;
    global $sales_id;


    if ($db == NULL)
    {
        // config is needed for connection to db
        include 'config.php';

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

        $cash_id = $record["cash_id"];
        $procurement_id = $record["procurement_id"];
        $sales_id = $record["sales_id"];
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
        echo "Error dropping table '$table_name': " . $sqlConnection->error . "<br>";
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


function dbTransferMoney($db, $source, $target, $executor, $amount, $comment)
{
    $auto = dbGetAutocommit($db);

    try 
    {
        dbValidateAccountId($db, $source);
        dbValidateAccountId($db, $target);
        dbValidateUserId($db, $executor);

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
    return;
}
?>

