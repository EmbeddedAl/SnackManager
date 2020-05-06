<?php session_start(); ?>

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
            <h2>Provide cash</h2>

<?php
    include_once "sharedphp/dbActions.php";
    include 'sharedphp/sharedInputCheck.php';
    include "sharedphp/sharedSqlWrapper.php";
    
    global $cash_id;

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

    $UsernameError="1";
    $AmountError="2";
    $CommentError="3";

    if (isset($_POST["commit"]))
    {
        
		$FormUsername = $_POST["username"];
		$FormAmount = $_POST["amount"];
		$FormComment = trim($_POST["comment"]);

        if (sharedInputCheck_isAmountValid($FormAmount) == 1)
        {
            $FormAmount = str_replace(",", ".", $FormAmount);
            if ((float)$FormAmount == 0)
            {
                $AmountError = "Must not be zero";
            }
            else
            {
                $AmountError = "";
            }
        }
        else
        {
            $AmountError = "Not a number (Format: 4.2)";
        }

		if (sharedSqlWrapper_userExists($FormUsername) == 1)
        {
            $UsernameError = "";
        }
        else
        {
            $UsernameError = "User '$FormUsername' not found.";
        }
        
        if (strlen($FormComment) == 0)
        {
            $CommentError = "Must not be empty.";
        }
        else
        {
            $CommentError = "";
        }

        if (strlen($UsernameError . $AmountError . $CommentError) == 0)
        {
            $db = NULL;
            
            try
            {
                $db = dbInit();

                $targetAccount = dbGetAccountIdForUser($db, dbGetUserId($db, $FormUsername));
                $sourceAccount = $cash_id;
                $executor = $_SESSION["userid"];
                dbTransferMoney($db, $sourceAccount, $targetAccount, $executor, $FormAmount, $FormComment);
                echo "User '$FormUsername' receives $FormAmount EUR from his account<br><br>";

                // all done, prepare next round
                $FormAmount = 0;
                $FormUsername = "";
                $FormComment = "Cash Withdraw";
                $_POST = array();
                
            }
            catch (dbException $e)
            {
                echo "<h2>Error: ". $e->getMessage() . "</h2>";
            }
            finally
            {
                dbClose($db);
            }
        }
    }
    else
    {
		$FormUsername = "";
		$FormAmount = 0;
        $FormComment = "Cash Withdraw";
    }

?>
                <form action="provide_cash.php" method="post">
                    <table>
                        <tr>
                            <td align="left">Username:</td>
                            <td align="left"><input name="username" value="<?php echo "$FormUsername"; ?>"/></td>
                            <td align="left" style="color:red"><?php echo "$UsernameError"; ?></td>
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
