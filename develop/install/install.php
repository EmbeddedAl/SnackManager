<html>
<body>
<?php
    include "../config.php";
    include "dbActions.php";

    echo "<h1> Intallation </h1>";
    echo "<h2> Your config file settings: </h2>";
    echo "Configuration: <br>";
    echo "> User:   " . $dbuser . "<br>";
    echo "> Pass:   " . $dbpass . "<br>";
    echo "> Host:   " . $dbhost . "<br>";
    echo "> Dbase:  " . $dbase . "<br>";

    /* Open sql connection */
    $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
    if ($sqlConnection->connect_error)
    {
        echo "Connection to Sql server failed" . $sqlConnection->connect_error;
        return;
    }
    echo "Connection to sql server established";

    /* install Database button */
    if (isset ( $_POST ['installDatabase'] ))
    {
        echo "<h2> Install and test database:</h2>";

        /* create table users */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS users (
                            userid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            lastname varchar(".$MaxCharLastname.") DEFAULT NULL,
                            firstname varchar(".$MaxCharFirstname.") DEFAULT NULL,
                            username varchar(".$MaxCharUsername.") DEFAULT NULL,
                            email varchar(".$MaxCharEmail.") DEFAULT NULL,
                            city varchar(".$MaxCharCity.") DEFAULT NULL,
                            password varchar(32) DEFAULT NULL,
                            isadmin INT NOT NULL,
                            account_id INT NOT NULL,
                            max_credit DECIMAL(6,2)
                            ) DEFAULT CHARSET=latin1";
        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'users' returned successfully<br>";

        /* create table accounts */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS accounts (
                            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            balance DECIMAL(6,2)
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'accounts': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'accounts' returned successfully<br>";


        /* create table account_log */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS account_log (
                            source_id INT NOT NULL,
                            target_id INT NOT NULL,
                            amount DECIMAL(6,2) DEFAULT 0,
                            comment varchar(256) DEFAULT NULL,
                            executor_id INT NOT NULL,
                            timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'account_log': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'account_log' returned successfully<br>";


        /* create table items */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS items (
                            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            price DECIMAL(6,2),
                            name varchar(48)
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'items': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'items' returned successfully<br>";


        /* create table orders */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS orders (
                            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            event_time DATETIME NOT NULL,
                            closing_time DATETIME NOT NULL,
                            is_closed INT NOT NULL
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'orders': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'orders' returned successfully<br>";


        /* create table order_entries */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS order_entries (
                            order_id INT NOT NULL,
                            user_id INT NOT NULL,
                            item_id INT NOT NULL,
                            Amount INT DEFAULT 0,
                            timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'order_entries': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'order_entries' returned successfully<br>";


        /* create table management */
        $sqlStatement = "CREATE TABLE IF NOT EXISTS management (
                            cash_id INT NOT NULL,
                            correction_id INT NOT NULL
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'management': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'management' returned successfully<br>";

        echo "End of routine<br>";
        return;
    }

    if (isset ( $_POST ['insertUsers'] ))
    {
        echo "<h2> Insert demo users to database:</h2>";

        $sqlStatement = "INSERT INTO accounts (id, balance) VALUES
                            (1, 0),
                            (2, 0),
                            (4, 0),
                            (5, 0),
                            (6, 0),
                            (7, 0),
                            (8, 0),
                            (9, 0),
                            (10, 0),
                            (11, 0),
                            (12, 0),
                            (13, 0)";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error inserting into table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'accounts' returned successfully<br>";

        if ($sqlConnection->query("insert into management (cash_id, correction_id) values (1, 2)") != TRUE)
        {
            echo "Error adding data to table 'management': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'management' returned successfully<br>";
        

        /* insert demo users */
        $sqlStatement = "INSERT INTO users (lastname, firstname, username, email, city, password, isadmin, account_id, max_credit) VALUES
                            ('Hamming',    'Richard',   'hamming',    'info0@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 1, 4, 0),
                            ('Leibniz',    'Gottfried', 'leibnitz',   'info1@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0, 5, 0),
                            ('Lovelace',   'Ada',       'lovelace',   'info2@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0, 6, 0),
                            ('Ritchie',    'Dennis',    'ritchie',    'info3@ThisShouldNotBeAValid.Domain', 'Munich',    md5('password'), 0, 7, 0),
                            ('Shannon',    'Claude',    'shannon',    'info4@ThisShouldNotBeAValid.Domain', 'Munich',    md5('password'), 0, 8, 0),
                            ('Stallman',   'Richard',   'stallman',   'info5@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0, 9, 0),
                            ('Tannenbaum', 'Andrew',    'tannenbaum', 'info6@ThisShouldNotBeAValid.Domain', 'Hamburg',   md5('password'), 0, 10, 0),
                            ('Torvalds',   'Linus',     'torvalds',   'info7@ThisShouldNotBeAValid.Domain', 'Stuttgart', md5('password'), 0, 11, 0),
                            ('Turing',     'Alan',      'turing',     'info8@ThisShouldNotBeAValid.Domain', 'Leipzig',   md5('password'), 0, 12, 0),
                            ('Zuse',       'Konrad',    'zuse',       'info9@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0, 13, 0)";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error inserting into table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'users' returned successfully<br>";


        if ($sqlConnection->query("INSERT INTO items (Name, Price) VALUES ('Weisswurst', 1.70)") != TRUE)
        {
            echo "INSERT weisswurst INTO items failed: " . $sqlConnection->error . "<br>";
            return;
        }
        $weisswurst_id = $sqlConnection->insert_id;

        if ($sqlConnection->query("INSERT INTO items (Name, Price) VALUES ('Breze', 0.70)") != TRUE)
        {
            echo "INSERT weisswurst INTO items failed: " . $sqlConnection->error . "<br>";
            return;
        }
        $breze_id = $sqlConnection->insert_id;

        echo "INSERT INTO 'items' returned successfully<br>";


        $sqlStatement = "INSERT INTO orders (id, event_time, closing_time, is_closed) VALUES
                            (NULL, '2020-05-21 12:00:00', '2020-05-20 15:30:00', 0),
                            (NULL, '2020-05-28 12:00:00', '2020-05-27 15:00:00', 0)";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error inserting into table 'orders': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'orders' returned successfully<br>";
        $order_id = $sqlConnection->insert_id;

        /********************
        * create a new user *
        ********************/
        try
        {
            $user_id = dbAddUser($sqlConnection, 'ab', 'Badforest', 'Albert', 'ab@invalid.local', 'Inningen', 'password', 0, 0);
            echo "User created with id $user_id<br>";
        }
        catch (dbException $e)
        {
            echo "Creation of user failed: " . $e->getMessage() . "<br>";
            return;
        }


        /* and give him some money */
        $cash_id = 1;
        $amount = 10.00;
        $comment="Einzahlung";
        $executor_user_id = 1;

        try
        {
            $account_id = dbGetAccountIdForUser($sqlConnection, $user_id);
            dbTransferMoney($sqlConnection, $cash_id, $account_id, $executor_user_id, $amount, $comment);
            echo "transfer done.<br>";
        }
        catch (dbException $e)
        {
            echo "Cash transfer failed: " . $e->getMessage() . "<br>";
            return;
        }

        /* let him order something */
        if ($sqlConnection->query("INSERT INTO order_entries (order_id, user_id, item_id, amount) VALUES (".$order_id.", ".$user_id.", ".$weisswurst_id.", 3), (".$order_id.", ".$user_id.", ".$breze_id.", 2)") != TRUE)
        {
            echo "INSERT into order entries failed: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "End of routine<br>";
        return;
    }

    if (isset ( $_POST ['dropAll'] ))
    {
        echo "<h2> Drop all tables from database:</h2>";

        dbDropTable($sqlConnection, "users");
        dbDropTable($sqlConnection, "accounts");
        dbDropTable($sqlConnection, "account_log");
        dbDropTable($sqlConnection, "management");
        dbDropTable($sqlConnection, "items");
        dbDropTable($sqlConnection, "orders");
        dbDropTable($sqlConnection, "order_entries");
        dbDropTable($sqlConnection, "account_log");
        echo "End of routine<br>";
        return;
    }
?>
</body>
</html>
