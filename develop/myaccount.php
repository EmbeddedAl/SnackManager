<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   include 'config.php';
   include 'sharedphp/sharedHelpers.php';
   include 'sharedphp/sharedInputCheck.php';
   include 'sharedphp/sharedSqlWrapper.php';

   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
   {
       echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
       return;
   }

   $FormPasswordValid = "";
   $FormPassword = $_POST["password"];

   if (isset($_POST["commit"]) && ($FormPassword != ""))
   {
      /* commit password database */
      $FormPassword = $_POST["password"];

      /* check password */
      if (sharedInputCheck_isPasswordValid($FormPassword) != 1)
      {
          $FormPasswordValid = "invalid (min " . $MinPasswordLen . " characters)";
      }
      else
      {
          shareSqlWrapper_updateUserPassword($_SESSION["userid"], md5($FormPassword));
      }

      /* clear the post */
      $_POST = array();
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
            <h2>My account</h2>

            <form action="myaccount.php" method="post">
              <table>
                <tr>
                  <td align="left">Username:</td>
                  <td align="left"><?php echo $_SESSION["username"]; ?></td>
                  <td width=200><?php echo "<img src=" . sharedHelpers_getUserImageFile($_SESSION['userid']) . " border=\"4\">" ?> </td>
                </tr>
                <tr>
                    <td align="left">Firstname:</td>
                    <td align="left" colspan="2"><?php echo $_SESSION["firstname"]; ?></td>

                </tr>
                <tr>
                    <td align="left">Lastname:</td>
                    <td align="left" colspan="2"><?php echo $_SESSION["lastname"]; ?></td>
                </tr>
                <tr>
                    <td align="left">Email:</td>
                    <td align="left" colspan="2"><?php echo $_SESSION["email"]; ?></td>
                </tr>
                <tr>
                    <td align="left">City:</td>
                    <td align="left" colspan="2"><?php echo $_SESSION["city"]; ?></td>
                </tr>



                <!-- -------------------------------------- -->
                <!-- Show input tags if edit mode is active -->
                <!-- -------------------------------------- -->
                <?php if (isset($_POST["editActive"])) { ?>
                <tr>
                    <td align="left">Password:</td>
                    <td align="left"><input type="password" name="password"/></td>
                </tr>
                <tr>
                    <td align="left"></td>
                    <td align="left"><input type="submit" name="commit" value="save password" /></td>
                </tr>




                <!-- -------------------------------------- -->
                <!-- Show values only -->
                <!-- -------------------------------------- -->
                <?php } else { ?>
                <tr>
                    <td align="left">Password:</td>
                    <td align="left">********</td>
                </tr>
                <tr>
                    <td align="left"></td>
                    <td align="left" style="color:red"><?php echo $FormPasswordValid; ?></td>
                </tr>
                <tr>
                    <td align="left"></td>
                    <td align="left"><input type="submit" name="editActive" value="change password" /></td>
                </tr>
                <?php  } ?>




              </table>
              </form>
          </div>

        <?php include ("layout/footer.html"); ?>
    </div>

    </body>
</html>