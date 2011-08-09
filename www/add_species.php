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

			if (isset($_POST['weighting']) && !empty($_POST['weighting'])) {

				$weighting = $_POST['weighting'];

				$query = "SELECT count(*) FROM species WHERE name = '".$species_name."'";
				$result = $dbh->query($query);

				$count = $result->fetchColumn();

				if ($count > 0) {

					$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Species name must be unique.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

				} else {

					$query = "INSERT INTO species (`name`) VALUES ('".$species_name."')";
					$dbh->exec($query);

					$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Species added.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

					for ($i=0; $i<sizeof($_POST['program_id'])-1; $i++) {	// -1 because of additional element added in case only one program_id/weighting exists 

						$query = "SELECT id FROM species WHERE name = '".$species_name."'";
						$result = $dbh->query($query);

						foreach($result as $row) {

							$species_id = $row['id'];

						}

						$this_program_id = $_POST['program_id'][$i];
						$this_weighting = $_POST['weighting'][$i];

						$query = "INSERT INTO process (`species_id`, `program_id`, `weighting`) VALUES (".$species_id.", ".$this_program_id.", ".$this_weighting.")";

						$dbh->exec($query);
				
					}

				}

			} else {

				$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;At least one weighting must be assigned.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

			}

		} else {

			$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;At least one program must be assigned.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

		}

	} else {

		$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Species name must be defined.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;		

	}

}

?>

<html>
	<head>

		<link rel="stylesheet" type="text/css" href="css/stylesheet.css" />

		<script type="text/javascript">

			function check_weightings() {

				var total_weight = 0.0;
				var num_checked = 0;

				for(i=0; i<document.add_species["program_id[]"].length-1; i++) {	// -1 because of additional element added in case only one program_id/weighting exists 

					if (document.add_species["program_id[]"][i].checked) {

						total_weight += parseFloat(document.add_species["weighting[]"][i].value);
						num_checked++;	

					}	

				}

				if (num_checked < 1) {

					alert("At least one checkbox should be selected.");	
					return false;

				} else if (total_weight != 1.0) {

					alert("Total weight must be equal to 1.");
					return false;

				} 

			}

		</script>

	</head>

	<body style="text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="add_species" id="add_species" action="add_species.php" method="post" onsubmit="return check_weightings();">

			<div>

				<h1>Add Species</h1>
				<h3>Step 1:</h3>
				<p>What is the species name?</p>

				<input class="textbox" type="text" name="species_name"></input>

			</div>

			<br/>

			<div>

				<h3>Step 2:</h3>
				<p>Assign programs and weightings:</p>

				<div>

					<table align="center">

					<tr><td>&nbsp;</td><td>Weight</td><td>Name</td></tr>
					<tr><td colspan="3">&nbsp;</td></tr>

					<?php 

					$query = "SELECT * FROM programs";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$program_id = $row['id'];
						$program_name = $row['name'];
						$program_binary = $row['binary'];

					?>

						<tr><td><input type="checkbox" name="program_id[]" value="<?php print $program_id; ?>"/></td><td><input class="textbox" style="width: 60px;" type="text" name="weighting[]"/></td><td><?php print $program_name; ?></td></tr>

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

			<!-- Need these because number of array elements must be > 1 -->
			<input type="hidden" name="program_id[]"/>	
			<input type="hidden" name="weighting[]"/>

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
