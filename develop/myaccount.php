<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   include 'sharedphp/sharedHelpers.php';
   include 'sharedphp/sharedInputCheck.php';
   include 'sharedphp/dbActions.php';
   
   $PageTitle = "My Account";
   
   
   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
   {
       echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
       return;
   }

   
   $FormPasswordValid = "";

   try 
   {
       $newdb = new snackDb();
       $AccountBalance = 0 - $newdb->getAccountBalance($newdb->getAccountIdForUser($_SESSION['userid']));
       
       if (isset($_POST["commit"]))
       {
           $FormPassword = $_POST["password"];
           
           // clear the post
           $_POST = array();
           
           // try to commit password to database
           $FormPasswordValid = sharedInputCheck_isPasswordValid($FormPassword);
           
           if (strlen($FormPasswordValid) == 0)
           {
               // No error message, seems ok
               $newdb->setPasswordHashForUser($_SESSION["userid"], md5($FormPassword));
           }
       }
   } 
   catch (dbException $e) 
   {
       $FormPasswordValid = $e->getMessage();
   }
   
?>




<html>
    <head>
        <?php include ("layout/title.html"); ?>
        <link rel="stylesheet" href="layout/style.css">
    </head>

    <body>
        <div id="page">
            <?php 
                include ("layout/header.php");

                include ("layout/nav.html"); 
            ?>
            
            <div id="content">
                <form action="myaccount.php" method="post">
                    <table>
                        <tr>
                            <td align="center" colspan="3"><?php echo "<img src=" . sharedHelpers_getUserImageFile($_SESSION['userid']) . " border=\"4\">" ?> </td>
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
<?php 
                        if (isset($_POST["editActive"])) 
                        {
                            // trying to edit the password, show input field
                            echo '<tr>';
                            echo '<td align="left">Password:</td>';
                            echo '<td align="left"><input type="password" name="password"/></td>';
                            echo '<td align="right"><input type="submit" name="commit" value="save password" /></td>';
                            echo '</tr>';
                        }
                        else 
                        {
                            if (strlen($FormPasswordValid) > 0)
                            {
                                // we have a error message to display
                                echo '<tr>';
                                echo '<td align="left"></td>';
                                echo '<td align="left" style="color:red">' . $FormPasswordValid . '</td>';
                                echo '</tr>';
                            }
                            echo '<tr>';
                            echo '<td align="left">Password:</td>';
                            echo '<td align="left">********</td>';
                            echo '<td align="right"><input type="submit" name="editActive" value="change password" /></td>';
                            echo '</tr>';
                        }
?>            
            		</table>
            	</form>
            	<br/>
<?php
                echo '<h2>Account Details</h2><br/>';
                
                try 
                {
                    $accountInfo = $newdb->getAccountHistory($_SESSION['userid']);
                    
                    $balance = 0 - $accountInfo['balance'];
                    $history = $accountInfo['history'];
                    
                    echo '<table>';
                    echo '<tr><th>Date</th><th align="right">Value</th><th>Contra Account</th><th>Comment</th><th align="right">New Balance</th></tr>';
                    
                    for ($i=0; $i<count($history); $i++)
                    {
                        echo '<tr>';
                        echo '<td>' . $history[$i]['timestamp'] . '</td>';
                        echo '<td align="right">' . formatCurrency($history[$i]['entryValue']) . '</td>';
                        echo '<td>' . $newdb->getAccountName($history[$i]['ContraAccount']) . '</td>';
                        echo '<td>' . $history[$i]['Comment'] . '</td>';
                        echo '<td align="right">' . formatCurrency($balance) . '</td>';
                        echo '</tr>';
                        
                        $balance -= $history[$i]['entryValue'];
                    }
                    echo '</table>';
                }
                catch (dbException $e)
                {
                    echo "<h2> Database error:" . $e->getMessage() . "</h2>";
                }
?>
            
			</div>';
			<?php include ("layout/footer.html"); ?>
	    </div>
    </body>
</html>