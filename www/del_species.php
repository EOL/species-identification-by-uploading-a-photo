<?php

$WARNING_ERROR_BOX = '<font style="background-color: yellow; border-color: black; border-width: 1px; border-style: solid;">WARNING</font>';
$CRITICAL_ERROR_BOX = '<font style="background-color: red; border-color: black; border-width: 1px; border-style: solid;">CRITICAL</font>';
$SUCCESS_BOX = '<font style="background-color: green; border-color: black; border-width: 1px; border-style: solid;">SUCCESS</font>';

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

include("connect.php");	// open database connection
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	if (isset($_POST['species_id']) && !empty($_POST['species_id'])) { 

		$species_id = $_POST['species_id'];

		$query = "DELETE FROM species WHERE id = ".$species_id;
		$dbh->exec($query);

		$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Species deleted.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

	} else {

		$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;A species must be defined.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;		

	}

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="del_species" id="del_species" action="del_species.php" method="post">

			<div> 
				
				<h1>Delete Species</h1>
				<h3>Step 1:</h3>
				<p>What is the species name?</p>

				<div>

					<table align="center">

					<?php 

					$query = "SELECT * FROM species ORDER BY name ASC";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$species_id = $row['id'];
						$species_name = $row['name'];
						$program_id = $row['program_id'];

					?>

						<tr><td><input type="radio" name="species_id" value="<?php print $species_id; ?>"/></td><td><?php print $species_name; ?></td></tr>

					<?php 

					} 

					?>

					</table>

				</div>

			</div>

			<br/>

			<div id="submit">

				<input type="submit" value="Delete species"/>

			</div>

		</form>


		<br/>

		<div id="error">

			<?php if(isset($error_msg)) { 

				print $error_msg; 

			} else if(isset($success_msg)) { 
		
				print $success_msg; 

			} else { 

				print "&nbsp;"; 

			} ?>
		</div>

		<br/>

		<div id="page_controls">

			<div style="text-align: left;">
	
				<a href="index.php">Back</a>

			</div>

		</div>	

	</div>

	</body>

</html>
