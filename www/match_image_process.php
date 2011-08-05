<?php

$WARNING_ERROR_BOX = '<font style="background-color: yellow; border-color: black; border-width: 1px; border-style: solid;">WARNING</font>';
$CRITICAL_ERROR_BOX = '<font style="background-color: red; border-color: black; border-width: 1px; border-style: solid;">CRITICAL</font>';
$SUCCESS_BOX = '<font style="background-color: green; border-color: black; border-width: 1px; border-style: solid;">SUCCESS</font>';

ini_set('display_errors',1);
error_reporting(E_ALL);

include("connect.php");	// open database connection
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$species_id = $_POST['species_id'];
	$crop_path = $_POST['crop_path'];

	// get binary

	$query = "SELECT p.binary FROM species s LEFT JOIN programs p on s.program_id = p.id WHERE s.id = ".$species_id;
	$result = $dbh->query($query);

	foreach($result as $row) {
	
		$binary = $row['binary'];

	}

	if ($binary == "histogram_backproject") {

		// get textures

		$texture_paths = "";

		$query = "SELECT * FROM textures WHERE species_id = ".$species_id;
		$result = $dbh->query($query);

		$counter = 0;

		foreach($result as $row) {
	
			$texture_paths = $texture_paths.$row['texture_path']." ";

			$texture_files[$counter] = $row['texture_path'];
			$counter++;

		}

		$match_cmd = $BIN_DIR_PATH.$binary." ".$crop_path." ".$texture_paths." ".$HISTOGRAM_COMPARISON_TYPE;

		// SYNCHRONOUS MODEL
			
		$output = array();
		$output = exec($match_cmd);

		$match_coeffs = explode(",", $output);

		$maxvalue = max($match_coeffs);

		while(list($key,$value)=each($match_coeffs)){

			if($value==$maxvalue) $maxindex=$key;

		} 

		// ASYNCHRONOUS MODEL
		
		// TODO: Need to add job id to database and push this variable into jobs/queue as a UID.

		//$match_cmd = $match_cmd." > jobs/test & echo $! >> jobs/queue & php process_job.php $!";
		//exec($match_cmd);


	}

} else {

	header("Location: match_image.php");

}

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/stylesheet.css" />
</head>
<body>
<br/>
<center><div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 400px; background-color: white;">

		<h1>Image Matching</h1>
		<br/>
		<div>
			<h3>Result:</h3>
			<br/>

			<?php if ($binary == "histogram_backproject") { ?>

				<img src="<?php print $crop_path; ?>"/><br/><br/><br/>

				<?php
					
				asort($match_coeffs, SORT_NUMERIC);	// arsort for INTERSECT, asort for EMD and DD

				foreach ($match_coeffs as $key => $val) { ?>

					<?php if ($match_coeffs[$key] != "") { ?>

				   		<img style="width: 30px; height: 30px;" src="<?php print $texture_files[$key]; ?>"/><?php print $match_coeffs[$key]; ?><br/> 

					<?php } ?>

				<?php }

			} ?>

		</div>

	</div>
</center>
</body>
</html>


		
