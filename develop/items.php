<?php 
    session_start();
    
    include "sharedphp/sharedHelpers.php";
    
    include 'sharedphp/dbActions.php';
    
    $PageTitle = "Manage Items";
    
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
    
    $MessageString = "";
    
    try {
        $newdb = new snackDb();
        
        do {
            $out = "";
            
            if (strlen($MessageString) > 0)
                $out = '<h2>' . $MessageString . '</h2>';

            $MessageString = "";
        
            if (isset($_POST["Ok"]))
            {
                if (!isset($_POST['itemprice'])) {
                    $MessageString = "Item price must not be empty!";
                } elseif (strlen($_POST['itemname']) == 0) {
                    $MessageString = "Item name must not be empty!";
                } else {
                    $existingId = $newdb->getItemByName($_POST["itemname"]);
                    if (($existingId != 0) && ($existingId != intval($_POST['itemid']))) {
                        $MessageString = "Name already exists";
                    }
                }
                
                if (strlen($MessageString) > 0) {
                    $_POST = array();
                    continue;
                }
                
                // depending on action
                if (intval($_POST['itemid']) == 0) {
                    // add
                    $newdb->addItem($_POST['itemname'], $_POST['itemprice']);
                    $MessageString = "Item added successfully";
                }
                else {
                    // update
                    $newdb->updateItem($_POST['itemid'], $_POST['itemname'], $_POST['itemprice']);
                    $MessageString = "Item changed successfully";
                }
                
                $_POST = array();
                continue;
            }
            
            if (isset($_POST['Edit_Item']) && !isset($_POST['itemid']) ) {
                $MessageString = "No Item selected for editing";
        
                $_POST = array();
                continue;
            }
        
            $items = $newdb->getItems();
            
            $out = $out . 
                '<form action="items.php" method="post">' .
                    '<table>' .
                        '<tr>' .
                            '<td>Select</td><td align="left">Item Name</td><td align="left">Price</td>' .
                        '</tr>';

            $disable="";
            if (isset($_POST['Add_New_Item']))
            {
                $out = $out .
                '<tr>' .
                '<td align="left"><input type="radio" name="itemid" value="" checked/></td>' .
                '<td align="left"><input name="itemname" value="' . $_POST["itemname"] . '"/></td>' .
                '<td align="right"><input name="itemprice" value="' . $_POST["itemprice"] . '" pattern="[0-9]{1,3}([.][0-9]{1,2})?"/></td>' .
                '</tr>';
                $disable=" disabled";
            }
            
            if (isset($_POST['Edit_Item']) && isset($_POST['itemid']) )
            {
                $disable=" disabled";
            }
            
            for ($i=0; $i<count($items); $i++)
            {
                if (isset($_POST['Edit_Item']) && ($_POST['itemid'] == $items[$i]['id'])) {
                    $out = $out .
                    '<tr>' .
                    '<td align="left"><input type="radio" name="itemid" value="' . $items[$i]['id'] . '" checked/></td>' .
                    '<td align="left"><input name="itemname" value="' . $items[$i]['name'] . '"/></td>' .
                    '<td align="right"><input name="itemprice" value="' . $items[$i]['price'] . '" pattern="[0-9]{1,3}([.][0-9]{1,2})?"/></td>' .
                    '</tr>';
                }
                else {
                    $out = $out .
                        '<tr>' .
                            '<td align="left"><input type="radio" name="itemid" value="' . $items[$i]['id'] . '"' . $disable . '/></td>' .
                            '<td align="left">' . $items[$i]['name'] . '</td>' . 
                            '<td align="right">' . formatCurrency($items[$i]['price']) . '</td>' .
                        '</tr>';
                }
            }
            
            if (isset($_POST['Edit_Item']) || isset($_POST['Add_New_Item'])) {
                $out = $out .
                '<tr>' .
                '<td align="left"><input type="submit" name="Ok" value="Ok" /></td><td align="left"><input type="submit" name="Cancel" value="Cancel" /></td><td></td>' .
                '</tr>';
            }
            else {
                $out = $out .
                        '<tr>' .
                            '<td align="left"><input type="submit" name="Add New Item" value="Add New Item" /></td><td align="left"><input type="submit" name="Edit Item" value="Edit Item" /></td><td align="left"><input type="submit" name="Delete Item" value="Delete Item" /></td>' .
                        '</tr>';
            }
            
            $out = $out .
                    '</table>' .
                 '</form>';

            break;            
        } while (true);
    } catch (dbException $e) {
        throw $e;
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

			<?php echo $out; ?>
			
			</div>
		</div>
	</body>
</html>

