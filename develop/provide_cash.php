<?php
    session_start();

    include "sharedphp/dbActions.php";
    include 'sharedphp/sharedInputCheck.php';
   
    $PageTitle = "Pay Back Cash to User";
    
    // require user to be logged in
    if (!isset($_SESSION["userid"]))
    {
        writeErrorPage("Access denied. You must log in first!");
        return;
    }

    // require user to be admin
    if (!isset($_SESSION["isadmin"]))
    {
        writeErrorPage("Access denied. Admin rights required.");
        return;
    }

    try
    {
        $UserError="";
        $AmountError="";
        $CommentError="";
        $MessageString = "";
        
        $newdb = new snackDb();
        
        if (isset($_POST["commit"]))
        {
            
    		$FormUser = $_POST["user_id"];
    		$FormAmount = $_POST["amount"];
    		$FormComment = trim($_POST["comment"]);
    
            if (sharedInputCheck_isAmountValid($FormAmount) == 1)
            {
                $FormAmount = str_replace(",", ".", $FormAmount);
                if ((float)$FormAmount > 0)
                {
                    $AmountError = "";
                }
                else
                {
                    $AmountError = "Must not be zero";
                }
            }
            else
            {
                $AmountError = "Not a number (Format: 4.2)";
            }
    
            if ($newdb->userExists($FormUser))
            {
                $UserError = "";
            }
            else
            {
                $UserError = "User '$FormUser' not found.";
            }
            
            if (strlen($FormComment) > 0)
            {
                $CommentError = "";
            }
            else
            {
                $CommentError = "Must not be empty.";
            }
    
            if (strlen($UserError . $AmountError . $CommentError) == 0)
            {
                $sourceAccount = $newdb->getAccountIdForUser($FormUser);
                $targetAccount = $newdb->cash_accountId;
                $executor = $_SESSION["userid"];
                
                $newdb->transferMoney($sourceAccount, $targetAccount, $executor, $FormAmount, $FormComment);
                $MessageString = "User '" . $newdb->getUserNameForUser($FormUser) . "' receives " . $FormAmount . " EUR from his account<br><br>";

                // all done, prepare next round
                $FormAmount = 0;
                $FormUser = "";
                $FormComment = "Cash Withdraw";
                $_POST = array();
            }
        }
        else
        {
    		$FormUser = "";
    		$FormAmount = 0;
            $FormComment = "Cash Withdraw";
        }
        
        $users = $newdb->getUsers();
    }
    catch (dbException $e)
    {
        $MessageString = "Database error: ". $e->getMessage();
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
         	
                <form action="provide_cash.php" method="post">
                    <table>
                        <tr>
                            <td align="left">Username:</td>
                            <td align="left"><select id="user_id" name="user_id">
                            <option value=""></option>
                            <?php
                            for ($i=0; $i<count($users); $i++)
                            {
                                echo '<option value="' . $users[$i]['id'] . '">' . $users[$i]['name'] . '</option>';
                            }
                            ?>
                            </select></td>
                            <td align="left" style="color:red"><?php echo "$UserError"; ?></td>
                        </tr>
                        <tr>
                            <td align="left">Amount:</td>
                            <td align="left"><input name="amount" value="<?php echo "$FormAmount"; ?>"/></td>
                            <td align="left" style="color:red"><?php echo "$AmountError"; ?></td>
                        </tr>
                        <tr>
                            <td align="left">Comment:</td>
                            <td align="left"><input name="comment" value="<?php echo "$FormComment"; ?>"/></td>
                            <td align="left" style="color:red"><?php echo "$CommentError"; ?></td>
                        </tr>
                        <tr>
                            <td align="left"></td>
                            <td align="left"><input type="submit" name="commit" value="register" /></td>
                            <td align="left"></td>
                        </tr>
                    </table>
                </form>
            </div><!-- content -->
            <?php include ("layout/footer.html"); ?>
        </div><!-- page -->
    </body>
</html>
