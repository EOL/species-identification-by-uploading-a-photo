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

	if (isset($_POST['species_name']) && !empty($_POST['species_name'])) { 

		$species_name = $_POST['species_name'];

		if (isset($_POST['program_id']) && !empty($_POST['program_id'])) {

			$program_id = $_POST['program_id'];

			$query = "SELECT count(*) FROM species WHERE name = '".$species_name."'";
			$result = $dbh->query($query);

			$count = $result->fetchColumn();

			if ($count > 0) {

				$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Species name must be unique.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

			} else {

				$query = "INSERT INTO species (`name`, `program_id`) VALUES ('".$species_name."',".$program_id.")";
				$dbh->exec($query);

				$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Species added.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

			}

		} else {

			$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;A program must be assigned.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

		}

	} else {

		$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Species name must be defined.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;		

	}

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="add_species" id="add_species" action="add_species.php" method="post">

			<div>

				<h1>Add Species</h1>
				<h3>Step 1:</h3>
				<p>What is the species name?</p>

				<input type="text" name="species_name"></input>

			</div>

			<br/>

			<div>

				<h3>Step 2:</h3>
				<p>Assign a program to the species:</p>

				<div>

					<table align="center">

					<?php 

					$query = "SELECT * FROM programs";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$program_id = $row['id'];
						$program_name = $row['name'];
						$program_binary = $row['binary'];

					?>

						<tr><td><input type="radio" name="program_id" value="<?php print $program_id; ?>"/></td><td><?php print $program_name; ?></td></tr>

					<?php 

					} 

					?>

					</table>

				</div>

			</div>

			<br/>

			<div id="submit">

				<input type="submit" value="Add species"/>

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
