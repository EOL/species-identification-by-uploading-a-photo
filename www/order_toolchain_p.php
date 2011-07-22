<?php

include("connect.php");

foreach ($_GET['listItem'] as $position => $program_id) :

	$species = $_GET['species'];
	$order = $position + 1;

	$query = "UPDATE process SET `order` =".$order." WHERE program_id = ".$program_id." AND species_id =".$species;
	$dbh->exec($query);

	//print $species."\t".$order."\t".$program_id."\n";

endforeach;

$today = date("H:i:s");

print "Order updated. (".$today.")";

?>
