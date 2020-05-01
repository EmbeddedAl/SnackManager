<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* check if there is login data from the login-site..? */
   if (isset($_POST["username"]))
   {
      /* config is needed for connection to db */
      include "config.php";

      /* open sql connection */
      $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
      if ($sqlConnection->connect_error)
          return;

      /* check username/password with database */
      $sqlStatement = "SELECT * from `users` WHERE ".
                      "`username` = '"  . $_POST['username'] . "' AND " .
                      "`password` = '" . md5($_POST['password']) . "'";

      /* query the database */
      $result = $sqlConnection->query ( $sqlStatement );
      if ($result != TRUE)
          return;

      /* fetch the result from database */
      if ($result->num_rows != 1)
      {
         echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
         return;
      }
      else
      {
         /* only one user found */
         $userFromDatabase = $result->fetch_assoc();

         $_SESSION["userid"]    = $userFromDatabase['userid'];
         $_SESSION["username"]  = $userFromDatabase['username'];
         $_SESSION["firstname"] = $userFromDatabase['firstname'];
         $_SESSION["lastname"]  = $userFromDatabase['lastname'];
         $_SESSION["email"]     = $userFromDatabase['email'];
         $_SESSION["city"]      = $userFromDatabase['city'];
         if ($userFromDatabase['isadmin'] == 1)
            $_SESSION["isadmin"] = 1;
      }
   }

?>
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
            <h2>Hi <?php echo $_SESSION["username"]; ?>,...</h2>
            <p>Welcome to SnackManager.</p>
            <p>Choose the menu item on the left!</p>
          </div>

        <?php include ("layout/footer.html"); ?>
    </div>

    </body>
</html>