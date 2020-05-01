<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
session_destroy ();
$_SESSION = array ();
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
            <h2>Login</h2>
            <p>Please login to SnackManager or contact your admin to create a new user!</p>
            <form action="start.php" method="post">
              <table>
                <tr>
                  <td align="right">User:</td>
                  <td align="left"><input name="username" /></td>
                </tr>
                <tr>
                  <td align="right">Password:</td>
                  <td align="left"><input type="password" name="password"/></td>
                </tr>
                <tr>
                  <td align="right"></td>
                  <td align="left"><input type="submit" value="Login" /></td>
                </tr>
              </table>
            </form>
          </div>

        <?php include ("layout/footer.html"); ?>
    </div>

    </body>
</html>