<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* destroy session */
   session_destroy();

   $_SESSION = array();
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
            <h2>Logout</h2>
            <p>You have been logged out.</p>
          </div>

        <?php include ("layout/footer.html"); ?>
    </div>

    </body>
</html>