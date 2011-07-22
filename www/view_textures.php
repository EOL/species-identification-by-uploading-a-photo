<?php

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

// open database connection

include("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$species = $_POST['species'];

}

?>

<html>
	<head>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="process" id="process" action="view_textures.php" method="post">

			<h1>View Textures</h1>
			<p>What species?</p>

			<div>

				<select name="species" onChange="this.form.submit();">

				<option value=""></option>

					<?php 

					$query = "SELECT * FROM species";

					$result = $dbh->query($query);

					foreach($result as $row) {
	
						$id = $row['id'];
						$name = $row['name'];

					?>

						<option <?php if(isset($species) && ($species == $id)) print "SELECTED" ?> value="<?php print $id; ?>"><?php print $name; ?></option>

					<?php 

					} 

					?>

				</select>

			</div>

			<br/>

			<div style="text-align: left;">
	
				<table width="80%" align="center">

				<?php

				if(!empty($species)) {

					$query = "SELECT * FROM textures WHERE species_id =".$species." ORDER BY path";

					$result = $dbh->query($query);

					$cell_counter = 0;

					foreach($result as $row) {
	
						$path = $row['path'];
						$number = $row['number'];

						$cell_counter++;


					?>
					
						<?php if ($cell_counter % 25== 0) { $cell_counter = 1; ?><tr><?php } ?>
							<td style="padding:5px;"><img style="width: 30px; height: 30px;" src="<?php print $path; ?>"/></td>
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
