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

	if (isset($_POST['program_name']) && !empty($_POST['program_name'])) { 

		$program_name = $_POST['program_name'];

		if (isset($_POST['binary']) && !empty($_POST['binary'])) {

			$binary = $_POST['binary'];

			$query = "SELECT count(*) FROM programs WHERE name = '".$program_name."'";
			$result = $dbh->query($query);

			$count = $result->fetchColumn();

			if ($count > 0) {

				$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Program name must be unique.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

			} else {

				$query = "INSERT INTO programs (`name`, `binary`) VALUES ('".$program_name."','".$binary."')";
				$dbh->exec($query);

				$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Program added.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

			}

		} else {

			$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;A binary must be assigned.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

		}

	} else {

		$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;Program name must be defined.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;		

	}

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="add_program" id="add_program" action="add_program.php" method="post">

			<div>

				<h1>Add Program</h1>
				<h3>Step 1:</h3>
				<p>What is the program name?</p>

				<input type="text" name="program_name"></input>

			</div>

			<br/>

			<div>

				<h3>Step 2:</h3>
				<p>Which program binary?</p>

				<div>	

					<table align="center">
			
					<?php

					if ($handle = opendir($BIN_DIR_PATH)) {

						while (false !== ($file = readdir($handle))) {

							if ($file != "." && $file != ".." && $file != "README") {

							?>

								<tr><td><input type="radio" name="binary" value="<?php print $file; ?>"/></td><td><?php print $file; ?></td></tr>
							
							<?php	
			
							}

						}
					}

					closedir($handle);

					?>

					</table>

				</div>

			</div>

			<br/>

			<div id="submit">

				<input type="submit" value="Add program"/>

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
