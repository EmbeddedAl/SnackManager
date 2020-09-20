<?php
    session_start();

    include "sharedphp/dbActions.php";
    include 'sharedphp/sharedInputCheck.php';
    
    $PageTitle = "Pay for a Purchase";
  
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

    $AmountError="";
    $CommentError="";
    $DefaultComment="Type of purchase";
    $MessageString = "";

    try 
    {
        $newdb = new snackDb();
        
        if (isset($_POST["commit"]))
        {
            
            
    		$FormAmount = $_POST["amount"];
    		$FormComment = trim($_POST["comment"]);
    		$Receiver = trim($_POST["user_id"]);
    
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
    
            if (strlen($FormComment) == 0)
            {
                $CommentError = "Must not be empty.";
            }
            else
            {
                if (strcmp(trim($DefaultComment), trim($FormComment)) == 0)
                {
                    $CommentError = "You must provide the type of purchase";
                }
                else
                    $CommentError = "";
            }
    
            if (strlen($AmountError . $CommentError) == 0)
            {
                $targetAccount = $newdb->procurement_accountId;
                $executor = $_SESSION["userid"];
                
                $sourceAccount = $newdb->cash_accountId;
                $newdb->transferMoney($sourceAccount, $targetAccount, $executor, $FormAmount, $FormComment);
                $MessageString = "Provide $FormAmount EUR for purchasing $FormComment";
                
                $user_id = intval($Receiver);
                if ($user_id > 0) {
                    // handle al payment by Receiver
                    $sourceAccount = $newdb->getAccountIdForUser($user_id);
                    $targetAccount = $newdb->cash_accountId;
                    $newdb->transferMoney($sourceAccount, $targetAccount, $executor, $FormAmount, $FormComment);
                    $MessageString = "$FormAmount EUR booked to account of " . $newdb->getUserNameForUser($user_id) . " for purchasing $FormComment";
                }
                // all done, prepare next round
                $FormAmount = 0;
                $FormComment = $DefaultComment;
                $_POST = array();
            }
        }
        else
        {
    		$FormAmount = 0;
            $FormComment = $DefaultComment;
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

                <form action="provide_cash_for_purchase.php" method="post">
                    <table>
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
                            <td align="left">Receiver:</td>
                            <td align="left"><select id="user_id" name="user_id">
                            <option value="Cash out" default>Cash out</option>
                            <?php
                            for ($i=0; $i<count($users); $i++)
                            {
                                echo '<option value="' . $users[$i]['id'] . '">' . $users[$i]['name'] . '</option>';
                            }
                            ?>
                            </select></td>
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
