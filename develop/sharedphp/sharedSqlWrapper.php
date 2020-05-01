<?php


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
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* insert into users */
    $sqlStatement = "INSERT INTO users (username, firstname, lastname, email, city, password, isadmin) VALUES ("
            . "'" . $username . "', '" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $city ."', '" . $passwordMD5 . "', 0)";

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
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


?>