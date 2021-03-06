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

	$species_id = $_POST['species_id'];

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="select_species" id="select_species" action="view_textures.php" method="post">

			<h1>View Textures</h1>
			<p>Which species?</p>

			<div>

				<select name="species_id" onChange="this.form.submit();">

				<option value=""></option>

					<?php 

					$query = "SELECT * FROM species";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$this_species_id = $row['id'];
						$this_species_name = $row['name'];

					?>

						<option <?php if(isset($species_id) && ($species_id == $this_species_id)) print "SELECTED" ?> value="<?php print $this_species_id; ?>"><?php print $this_species_name; ?></option>

					<?php 

					} 

					?>

				</select>

			</div>

			<br/>

			<div style="text-align: left;">
	
				<table width="80%" align="center">

				<?php

				if(!empty($species_id)) {

					$query = "SELECT * FROM textures WHERE species_id =".$species_id." ORDER BY texture_path";

					$result = $dbh->query($query);

					$cell_counter = 0;

					foreach($result as $row) {
	
						$texture_path = $row['texture_path'];
						$texture_number = $row['number'];

						$cell_counter++;


					?>
					
						<?php if ($cell_counter % 25== 0) { $cell_counter = 1; ?><tr><?php } ?>
							<td style="padding:5px;"><img style="width: 30px; height: 30px;" src="<?php print $texture_path; ?>"/></td>
						<?php if ($cell_counter % 25 == 0) { $cell_counter = 1; ?></tr><?php } ?>

					<?php

					}

				}

				?>

				</table>

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
