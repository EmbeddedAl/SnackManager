<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">
<?php
    include 'config.php';
    include 'sharedphp/sharedInputCheck.php';
    include "sharedphp/sharedSqlWrapper.php";

    /* require user to be logged in */
    if (!isset($_SESSION["userid"]))
    {
        echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
        return;
    }

    /* require user to be admin */
    if (!isset($_SESSION["isadmin"]))
    {
        echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
        return;
    }
	
	$RegistrationCompleted = FALSE;
	
	/* initialize all invalid markers */
	$FormUserNameValid = "";
	$FormFirstNameValid = "";
	$FormLastNameValid = "";
	$FormEmailValid = "";
	$FormCityValid = "";
	$FormPasswordValid = "";

	if (isset($_POST["commit"]))
	{
		/* check username (and set value as it was set by the user) */
		$FormUserName = $_POST["username"];
		if (sharedSqlWrapper_userExists($FormUserName) == 0)
		{
			if (sharedInputCheck_isUsernameValid($FormUserName) != 1)
			{
				$FormUserNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharUsername .")";
			}
		}
		else
		{
			$FormUserNameValid = "user already exists<br> (or database error)";
		}

		/* check firstname (and set value as it was set by the user) */
		$FormFirstName = $_POST["firstname"];
		if (sharedInputCheck_isFirstnameValid($FormFirstName) != 1)
		{
			$FormFirstNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharFirstname .")";
		}

		/* check lastname (and set value as it was set by the user) */
		$FormLastName = $_POST["lastname"];
		if (sharedInputCheck_isLastnameValid($FormLastName) != 1)
		{
			$FormLastNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharLastname .")";
		}

		/* check email (and set value as it was set by the user) */
		$FormEmail = $_POST["email"];
		if (sharedInputCheck_isEmailValid($FormEmail) != 1)
		{
			$FormEmailValid = "invalid<br> (must be well formed; max length: " . $MaxCharEmail .")";
		}

		/* check City (and set value as it was set by the user) */
		$FormCity = $_POST["city"];
		if (sharedInputCheck_isCityValid($FormCity) != 1)
		{
			$FormCityValid = "invalid<br> (only valid characters; max length: " . $MaxCharCity .")";
		}

		/* check password */
		$FormPassword = $_POST["password"];
		if (sharedInputCheck_isPasswordValid($FormPassword) != 1)
		{
			$FormPasswordValid = "invalid<br> (min " . $MinPasswordLen . " characters)";
		}

	   /* do database access if all input data is correct */
	   if ($FormUserNameValid == "" &&
		   $FormFirstNameValid == "" &&
		   $FormLastNameValid == "" &&
		   $FormEmailValid == "" &&
		   $FormCityValid == "" &&
		   $FormPasswordValid == "")
	   {
			$RegistrationCompleted = TRUE;

			if (shareSqlWrapper_userCreate($FormUserName, $FormFirstName, $FormLastName, $FormEmail, $FormCity, md5($FormPassword)) < 0)
			{
				echo "Error inserting user into table <br>";
			}

			/* clear the post */
			$_POST = array();

			/* destroy and clear session */
			session_destroy();
			$_SESSION = array();
	   }
   }
   else
   {
	   // For the form, set the initial values
	   $FormUserName = $DefaultUserName;
	   $FormFirstName = $DefaultFirstName;
	   $FormLastName = $DefaultLastName;
	   $FormEmail = $DefaultEmail;
	   $FormCity = $DefaultCity;
	   $FormPassword = $DefaultPassword;
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
			<?php if ($RegistrationCompleted == TRUE) { ?>
				<h2>Registration completed.</h2>
			<?php } ?>

			<?php if ($RegistrationCompleted == FALSE) { ?>
				<h2>Register a new user</h2>

				<form action="register.php" method="post">
				  <table>
					<tr>
					  <td align="left">Username:</td>
					  <td align="left"> <input name="username" value="<?php echo $FormUserName; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormUserNameValid; ?> </td>
					</tr>

					<tr>
					  <td align="left">First name:</td>
					  <td align="left"> <input name="firstname" value="<?php echo $FormFirstName; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormFirstNameValid; ?> </td>
					</tr>

					<tr>
					  <td align="left">Last name:</td>
					  <td align="left"><input name="lastname" value="<?php echo $FormLastName; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormLastNameValid; ?> </td>
					</tr>

					<tr>
					  <td align="left">email:</td>
					  <td align="left"><input name="email" value="<?php echo $FormEmail; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormEmailValid; ?> </td>
					</tr>

					<tr>
					  <td align="left">City:</td>
					  <td align="left"><input name="city" value="<?php echo $FormCity; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormCityValid; ?> </td>
					</tr>

					<tr>
					  <td align="left">Password:</td>
					  <td align="left"><input type="password" name="password" value="<?php echo $FormPassword; ?>"></td>
					  <td align="left" style="color:red"><?php echo $FormPasswordValid; ?></td>
					</tr>
					<tr>
					  <td align="left"></td>
					  <td align="left"><input type="submit" name="commit" value="register" /></td>
					</tr>

				  </table>
				</form>
				<?php } ?>
             
          </div>

        <?php include ("layout/footer.html"); ?>
    </div>
    </body>

</html>