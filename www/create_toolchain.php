<?php

$WARNING_ERROR_BOX = '<font style="background-color: yellow; border-color: black; border-width: 1px; border-style: solid;">WARNING</font>';
$CRITICAL_ERROR_BOX = '<font style="background-color: red; border-color: black; border-width: 1px; border-style: solid;">CRITICAL</font>';
$SUCCESS_BOX = '<font style="background-color: green; border-color: black; border-width: 1px; border-style: solid;">SUCCESS</font>';

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

// open database connection

include("connect.php");

//var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	if (isset($_POST['species'])) { 

		$species = $_POST['species'];

	} 

	if (isset($_POST['processing_tools'])) { 

		$processing_tools = $_POST['processing_tools'];

	} else {

		$error_msg = $WARNING_ERROR_BOX."&nbsp;&nbsp;&nbsp;Toolchain added. No processing tools selected for toolchain.&nbsp;&nbsp;&nbsp;".$WARNING_ERROR_BOX;

	}

	if (isset($_POST['matching_tool'])) {

		$matching_tool = $_POST['matching_tool'];

		$query = "SELECT count(*) FROM process WHERE species_id = ".$species;
		$result = $dbh->query($query);

		$count = $result->fetchColumn();

		if ($count > 0) {	// check toolchain for this species doesn't exist already

			$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Species already has a toolchain. Toolchain must be deleted first.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

		} else {		// add the toolchain

			$order = 0;

			if (isset($processing_tools)) {		// add processing tools to toolchain

				for($i = 0; $i < count($processing_tools); $i++) {

					$order = $i + 1;

					$query = "INSERT INTO process (`species_id`, `program_id`, `order`) VALUES (".$species.",".$processing_tools[$i].",".$order.")";
					$dbh->exec($query);

				}

			}

			// add matching tool to toolchain

			$order++;

			$query = "INSERT INTO process (`species_id`, `program_id`, `order`) VALUES (".$species.",".$matching_tool.",".$order.")";
			$dbh->exec($query);

			// update species table to reflect addition of toolchain

			$query = "UPDATE species SET has_toolchain = 1 WHERE id = ".$species;
			$dbh->exec($query);
		
			$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Toolchain added.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

		}

	} else {

		$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Toolchain requires a matching tool.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

	}

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="create_toolchain" id="create_toolchain" action="create_toolchain.php" method="post">

			<div>

				<h1>Create toolchain</h1>
				<h3>Step 1:</h3>
				<p>What species is this for?</p>

				<select name="species">

					<?php

					$query = "SELECT * FROM species";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$id = $row['id'];
						$process_id = $row['process_id'];
						$name = $row['name'];

					?>

						<option value="<?php print $id; ?>"><?php print $name; ?></option>

					<?php 

					}

					?>

				</select>

			</div>

			<br/>

			<div>

				<h3>Step 2:</h3>
				<p>Add processing tools to the toolchain.</p>

				<div>

					<table align="center">

					<?php 

					$query = "SELECT * FROM programs WHERE is_match = 0";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$id = $row['id'];
						$name = $row['name'];
						$path = $row['path'];
						$description = $row['description'];
						$parameter_delimiter = $row['parameter_delimiter'];
						$is_match = $row['is_match'];

					?>

						<tr><td><input type="checkbox" name="processing_tools[]" value="<?php print $id; ?>"/></td><td><?php print $name; ?></td><td><?php print $description; ?></td></tr>

					<?php 

					} 

					?>

					</table>

				</div>

			</div>

			<br/>

			<div>

				<h3>Step 3:</h3>
				<p>Add matching tool to the toolchain.</p>

				<div>

					<table align="center">

					<?php 

					$query = "SELECT * FROM programs WHERE is_match = 1";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$id = $row['id'];
						$name = $row['name'];
						$path = $row['path'];
						$description = $row['description'];
						$parameter_delimiter = $row['parameter_delimiter'];
						$is_match = $row['is_match'];

					?>

						<tr><td><input type="radio" name="matching_tool" value="<?php print $id; ?>"/></td><td><?php print $name; ?></td><td><?php print $description; ?></td></tr>

					<?php 

					} 

					?>

					</table>

				</div>

			</div>

			<br/>

			<div id="submit">

				<input type="submit" value="Create toolchain"/>

			</div>

		</form>

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
