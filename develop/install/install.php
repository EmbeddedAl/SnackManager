<html>
<body>
<?php
    include "../config.php";

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
                            isadmin INT NOT NULL
                            ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error creating table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "CREATE TABLE IF NOT EXISTS 'users' returned successfully<br>";

        echo "End of routine<br>";
        return;
    }

    if (isset ( $_POST ['insertUsers'] ))
    {
        echo "<h2> Insert demo users to database:</h2>";

        /* insert demo users */
        $sqlStatement = "INSERT INTO users (lastname, firstname, username, email, city, password, isadmin) VALUES
                            ('Hamming',    'Richard',   'hamming',    'info0@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 1),
                            ('Leibniz',    'Gottfried', 'leibnitz',   'info1@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0),
                            ('Lovelace',   'Ada',       'lovelace',   'info2@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0),
                            ('Ritchie',    'Dennis',    'ritchie',    'info3@ThisShouldNotBeAValid.Domain', 'Munich',    md5('password'), 0),
                            ('Shannon',    'Claude',    'shannon',    'info4@ThisShouldNotBeAValid.Domain', 'Munich',    md5('password'), 0),
                            ('Stallman',   'Richard',   'stallman',   'info5@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0),
                            ('Tannenbaum', 'Andrew',    'tannenbaum', 'info6@ThisShouldNotBeAValid.Domain', 'Hamburg',   md5('password'), 0),
                            ('Torvalds',   'Linus',     'torvalds',   'info7@ThisShouldNotBeAValid.Domain', 'Stuttgart', md5('password'), 0),
                            ('Turing',     'Alan',      'turing',     'info8@ThisShouldNotBeAValid.Domain', 'Leipzig',   md5('password'), 0),
                            ('Zuse',       'Konrad',    'zuse',       'info9@ThisShouldNotBeAValid.Domain', 'Berlin',    md5('password'), 0)";

        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error inserting into table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "INSERT INTO 'users' returned successfully<br>";

        echo "End of routine<br>";
        return;
    }
    if (isset ( $_POST ['dropAll'] ))
    {
        echo "<h2> Drop all tables from database:</h2>";

        /* drop table users */
        $sqlStatement = "DROP TABLE IF EXISTS users";
        if ($sqlConnection->query ( $sqlStatement ) != TRUE)
        {
            echo "Error dropping table: " . $sqlConnection->error . "<br>";
            return;
        }
        echo "DROP TABLE IF EXISTS 'users' returned successfully<br>";
        echo "End of routine<br>";
        return;
    }
?>
</body>
</html>
