<html>
<body>
<?php
    include "../config.php";
    include "../sharedphp/dbActions.php";

    echo "<h1> Intallation </h1>";
    echo "<h2> Your config file settings: </h2>";
    echo "Configuration: <br>";
    echo "> User:   " . $dbuser . "<br>";
    echo "> Pass:   " . $dbpass . "<br>";
    echo "> Host:   " . $dbhost . "<br>";
    echo "> Dbase:  " . $dbase . "<br>";

    // Open sql connection
    $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
    if ($sqlConnection->connect_error)
    {
        echo "Connection to Sql server failed" . $sqlConnection->connect_error;
        return;
    }
    echo "Connection to sql server established<br>";

    // install Database button
    if (isset ( $_POST ['installDatabase'] ))
    {
        echo "<h2> Install and test database:</h2>";

        // create table users
        if ($sqlConnection->query(
                "CREATE TABLE IF NOT EXISTS users (
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
                    ) DEFAULT CHARSET=latin1"
                ) != TRUE)
        {
            echo "Error creating table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'users' returned successfully<br>";

        // create table accounts
        if ($sqlConnection->query(
                "CREATE TABLE IF NOT EXISTS accounts (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    balance DECIMAL(6,2)
                    ) DEFAULT CHARSET=latin1") != TRUE)
        {
            echo "Error creating table 'accounts': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'accounts' returned successfully<br>";

        // create table account_log
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


        // create table items
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


        // create table orders
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


        // create table order_entries
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


        // create table management
        $sqlStatement = "CREATE TABLE IF NOT EXISTS management (
                            cash_id INT NOT NULL,
                            procurement_id INT NOT NULL,
                            sales_id INT NOT NULL
                            ) DEFAULT CHARSET=latin1";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table 'management': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'management' returned successfully<br>";

        // create system accounts
        if ($sqlConnection->query ("INSERT INTO accounts (id, balance) VALUES (1, 0), (2, 0), (3, 0)") != TRUE)
        {
            echo "Error inserting into table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'accounts' returned successfully<br>";


        if ($sqlConnection->query("insert into management (cash_id, procurement_id, sales_id) values (1, 2, 3)") != TRUE)
        {
            echo "Error adding data to table 'management': " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'management' returned successfully<br>";

        echo "End of routine<br>";
        return;
    }

    if (isset ( $_POST ['insertUsers'] ))
    {
        echo "<h2> Insert demo users to database:</h2>";

        try
        {
            dbInit($sqlConnection);
        }
        catch (dbException $e)
        {
            echo "Error initializing the database: " . $e->getMessage() . "<br>";
            return;
        }

        // create some users
        try
        {
            dbAddUser($sqlConnection, "hamming",    md5("password"), "Hamming",     "Richard",      "info0@ThisShouldNotBeAValid.Domain", "Berlin",     1);
            dbAddUser($sqlConnection, "leibnitz",   md5("password"), "Leibnitz",    "Gottfried",    "info1@ThisShouldNotBeAValid.Domain", "Berlin",     0);
            dbAddUser($sqlConnection, "lovelace",   md5("password"), "Lovelace",    "Ada",          "info2@ThisShouldNotBeAValid.Domain", "Berlin",     0);
            dbAddUser($sqlConnection, "richie",     md5("password"), "Richie",      "Dennis",       "info3@ThisShouldNotBeAValid.Domain", "Munich",     0);
            dbAddUser($sqlConnection, "shannon",    md5("password"), "Shannon",     "Claude",       "info4@ThisShouldNotBeAValid.Domain", "Munich",     0);
            dbAddUser($sqlConnection, "stallman",   md5("password"), "Stallman",    "Richard",      "info5@ThisShouldNotBeAValid.Domain", "Berlin",     0);
            dbAddUser($sqlConnection, "tannenbaum", md5("password"), "Tannenbaum",  "Andrew",       "info6@ThisShouldNotBeAValid.Domain", "Hamburg",    0);
            dbAddUser($sqlConnection, "torvalds",   md5("password"), "Torvalds",    "Linus",        "info7@ThisShouldNotBeAValid.Domain", "Stuttgart",  0);
            dbAddUser($sqlConnection, "turing",     md5("password"), "Turing",      "Alan",         "info8@ThisShouldNotBeAValid.Domain", "Leipzig",    0);
            dbAddUser($sqlConnection, "zuse",       md5("password"), "Zuse",        "Konrad",       "info9@ThisShouldNotBeAValid.Domain", "Berlin",     0);
            echo "INSERT INTO 'users' returned successfully<br>";
        }
        catch (dbException $e)
        {
            echo "Error adding users: " . $e->getMessage() . "<br>";
            return;
        }

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

        // create a new user
        try
        {
            $user_id = dbAddUser($sqlConnection, 'ab', 'password', 'Badforest', 'Albert', 'ab@invalid.local', 'Inningen');
            echo "User created with id $user_id<br>";
        }
        catch (dbException $e)
        {
            echo "Creation of user failed: " . $e->getMessage() . "<br>";
            return;
        }


        // and give him some money
        $amount = 10.00;
        $comment="Einzahlung";
        $executor_user_id = 1;

        try
        {
            $account_id = dbGetAccountIdForUser($sqlConnection, $user_id);
            dbTransferMoney($sqlConnection, $account_id, $cash_id, $executor_user_id, $amount, $comment);
            echo "transfer done.<br>";
        }
        catch (dbException $e)
        {
            echo "Cash transfer failed: " . $e->getMessage() . "<br>";
            return;
        }

        // let him order something
        if ($sqlConnection->query("INSERT INTO order_entries (order_id, user_id, item_id, amount) VALUES (".$order_id.", ".$user_id.", ".$weisswurst_id.", 3), (".$order_id.", ".$user_id.", ".$breze_id.", 2)") != TRUE)
        {
            echo "INSERT into order entries failed: " . $sqlConnection->error . "<br>";
            return;
        }


        // Einkauf gegen Kasse
        dbTransferMoney($sqlConnection, $cash_id, $procurement_id, $executor_user_id, $amount, $comment);

        // Abrechnung einer Breze, man beachte source und target
        dbTransferMoney($sqlConnection, $sales_id, $account_id, $executor_user_id, $amount, $comment);

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
