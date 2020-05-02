<?php

class dbException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
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
    if ($result = $db->query("SELECT @@autocommit")) {
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


function dbAddUser($db, $username, $lastname, $firstname, $mail, $city, $password, $isAdmin, $max_credit)
{
    $auto = dbGetAutocommit($db);

    try 
    {
        $db->autocommit(FALSE);

        if ($db->query("INSERT INTO accounts (id, balance) VALUES (NULL, 0)") != TRUE)
        {
            throw new dbException("Creating of account failed at dbAddUser: " . $sqlConnection->error);
        }
        $account_id = $db->insert_id;

        if ($db->query( "INSERT INTO users (lastname, firstname, username, email, city, password, isadmin, account_id, max_credit) ".
                        "VALUES ('$lastname', '$firstname', '$username', '$mail', '$city', md5('$password'), $isAdmin, $account_id, $max_credit)") != TRUE)
        {
            throw new dbException("Creating of user failed at dbAddUser: " . $sqlConnection->error);
        }
        $user_id = $db->insert_id;

        if ($db->commit() != TRUE)
        {
            throw new dbException("COMMIT failed at dbAddUser: " . $sqlConnection->error);
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


function dbGetAccountIdForUser($db, $user_id)
{
    if ($result = $db->query("SELECT account_id FROM users WHERE userid = $user_id")) {
        $row = $result->fetch_row();
        $account_id = $row[0];
        $result->free();
        return $account_id;
    }
    throw new dbException("Cannot find user");
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
