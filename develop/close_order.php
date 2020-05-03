<html>
<body>
<?php

$wait_time=3;

echo "<h2> Closing an order is not implemented yet!</h2>";
echo "You will be redirected to the menu in $wait_time seconds<br>";
echo "<meta http-equiv=\"refresh\" content=\"$wait_time; url=start.php\">";

$to="wuff@thequiet.place";
$from="mailer@thequiet.place";
$subject="Order list";
$txt="totals:\r\n";

$headers="From: $from";
mail($to, $subject, $txt, $headers);

?>
</body>
</html>
