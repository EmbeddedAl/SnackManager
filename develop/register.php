<?php 

    global $MaxCharUsername;
    global $MaxCharFirstname;
    global $MaxCharLastname;
    global $MaxCharEmail;
    global $MaxCharCity;
    global $DefaultUserName;
    global $DefaultFirstName;
    global $DefaultLastName;
    global $DefaultEmail;
    global $DefaultCity;
    global $DefaultPassword;
    
    include 'config.php';
    include 'sharedphp/sharedInputCheck.php';
    include "sharedphp/sharedHelpers.php";
    
    include 'sharedphp/dbActions.php';
    
    $PageTitle = "Create User";
    
    session_start();
    
    /* require user to be logged in */
    if (!isset($_SESSION["userid"]))
    {
        writeErrorPage("Access denied. You must log in first!");
        return;
    }

    /* require user to be admin */
    if (!isset($_SESSION["isadmin"]))
    {
        writeErrorPage("Access denied. Admin rights required.");
        return;
    }
	
	/* initialize all invalid markers */
	$FormUserNameValid = "";
	$FormFirstNameValid = "";
	$FormLastNameValid = "";
	$FormEmailValid = "";
	$FormCityValid = "";
	$FormPasswordValid = "";
	$MessageString = '';
	
	if (isset($_POST["commit"]))
	{
	    // check user name for validity first
        try
        {
            $newdb = new snackDb();
            $allOk = true;
            
            /* check username (and set value as it was set by the user) */
            $FormUserName = $_POST["username"];
            
            if (sharedInputCheck_isUsernameValid($FormUserName) != 1)
            {
                $allOk = false;
                $FormUserNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharUsername .")";
            }
            else if ($newdb->userNameExists($FormUserName))
            {
                $allOk = false;
                $FormUserNameValid = "User already exists";
            }

            /* check firstname (and set value as it was set by the user) */
            $FormFirstName = $_POST["firstname"];
            if (sharedInputCheck_isFirstnameValid($FormFirstName) != 1)
            {
                $allOk = false;
                $FormFirstNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharFirstname .")";
            }

            /* check lastname (and set value as it was set by the user) */
            $FormLastName = $_POST["lastname"];
            if (sharedInputCheck_isLastnameValid($FormLastName) != 1)
            {
                $allOk = false;
                $FormLastNameValid = "invalid<br> (only valid characters; max length: " . $MaxCharLastname .")";
            }

            /* check email (and set value as it was set by the user) */
            $FormEmail = $_POST["email"];
            if (sharedInputCheck_isEmailValid($FormEmail) != 1)
            {
                $allOk = false;
                $FormEmailValid = "invalid<br> (must be well formed; max length: " . $MaxCharEmail .")";
            }

            /* check City (and set value as it was set by the user) */
            $FormCity = $_POST["city"];
            if (sharedInputCheck_isCityValid($FormCity) != 1)
            {
                $allOk = false;
                $FormCityValid = "invalid<br> (only valid characters; max length: " . $MaxCharCity .")";
            }

            /* check password */
            $FormPassword = $_POST["password"];
            $FormPasswordValid = sharedInputCheck_checkPasswordValidity($FormPassword);
            $allOk &= (strlen($FormPasswordValid) == 0);
            
            /* do database access if all input data is correct */
            if ($allOk)
            {
                $newdb->createUser($FormUserName, $FormFirstName, $FormLastName, $FormEmail, $FormCity, md5($FormPassword));
                $MessageString = 'User ' . $FormUserName . ' created successfully';

                // clear the post
                $_POST = array();
            }
        }
        catch (dbException $e)
        {
            $MessageString = "Database error: ". $e->getMessage();
        }
    }
    
    
    if (!isset($_POST["commit"]))
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

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <?php include ("layout/title.html"); ?>
        <link rel="stylesheet" href="layout/style.css">
    </head>

    <body>
        <div id="page">
            <?php include ("layout/header.php"); ?>
            <?php include ("layout/nav.html"); ?>

    		<div id="content">
         		<?php if (strlen($MessageString) > 0) { echo "<h2>" . $MessageString . "</h2><br/>"; } ?>
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
			</div>

			<?php include ("layout/footer.html"); ?>
		</div>
    </body>
</html>
