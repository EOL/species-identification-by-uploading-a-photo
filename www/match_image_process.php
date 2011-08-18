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

	// get binaries

	$query = "SELECT * FROM process proc LEFT JOIN programs prog on proc.program_id = prog.id WHERE proc.species_id = ".$species_id;
	$result = $dbh->query($query);

	$binary_iteration = 0;

	foreach($result as $row) {

		// TODO: Weightings aren't taken into account, results are shown separately. Aggregrate results.
	
		$binary[$binary_iteration] = $row['binary'];

		if ($binary[$binary_iteration] == "histogram_backproject") {

			// get textures

			$texture_paths = "";

			$query = "SELECT * FROM textures WHERE species_id = ".$species_id;
			$result = $dbh->query($query);

			$counter = 0;

			foreach($result as $row) {
	
				$texture_paths = $texture_paths.$row['texture_path']." ";

				$texture_files[$binary_iteration][$counter] = $row['texture_path'];	// 2D array, texture paths described by [number of binaries][number of textures]
				$counter++;

			}

			$match_cmd = $BIN_DIR_PATH.$binary[$binary_iteration]." ".$crop_path." ".$texture_paths." ".$HISTOGRAM_COMPARISON_TYPE;

			// SYNCHRONOUS MODEL
			
			$output = array();
			$output = exec($match_cmd);

			$match_coeffs[$binary_iteration] = explode(",", $output);	// 2D array, coefficients described by [number of binaries][number of textures]

			// ASYNCHRONOUS MODEL
		
			// TODO: Need to add job id to database and push this variable into jobs/queue as a UID.

			//$match_cmd = $match_cmd." > jobs/test & echo $! >> jobs/queue & php process_job.php $!";
			//exec($match_cmd);


		}

		if ($binary[$binary_iteration] == "compare_gabor_mag_images") {

			// create gabor feature vector for cropped image
			$cropped_img_gabor_feature_vector = "";

			for ($scale=0; $scale<$GABOR_MAG_SCALE_LIMIT; $scale++) {

				for ($orientation=0; $orientation<$GABOR_MAG_ORIENTATION_LIMIT; $orientation++) {

					$gabor_cmd = $BIN_DIR_PATH."construct_gabor_mag_image ".$crop_path." ".$orientation." ".$scale;

					// generate Gabor magnitude feature vector elements	
					$output = array();
					$output = exec($gabor_cmd);

					$output_stats = explode(",", $output);	// 2D array, [0] = mean, [1] = stddev

					$cropped_img_gabor_feature_vector = $cropped_img_gabor_feature_vector.$output.",";

				}

			}
	
			$cropped_img_gabor_feature_vector = rtrim($cropped_img_gabor_feature_vector, ",");

			// find number of sets that need to be calculated

			$set_query = "SELECT * FROM gabormagimgs WHERE species_id = ".$species_id." GROUP BY img_path";
			$set_result = $dbh->query($set_query);

			$counter = 0;

			foreach($set_result as $set_row) {

				$query_img_gabor_feature_vector = "";
	
				$this_img_path = $set_row['img_path'];
				
				$img_query = 'SELECT * FROM gabormagimgs WHERE img_path = "'.$this_img_path.'" ORDER BY scale ASC, orientation ASC';
				$img_result = $dbh->query($img_query);

				foreach($img_result as $img_row) {
	
					$this_img_mean = $img_row['mean'];
					$this_img_stddev = $img_row['stddev'];

					$query_img_gabor_feature_vector = $query_img_gabor_feature_vector.$this_img_mean.",".$this_img_stddev.",";

				}

				$query_img_gabor_feature_vector = rtrim($query_img_gabor_feature_vector, ",");

				// compare feature vectors

				$compare_cmd = $BIN_DIR_PATH.$binary[$binary_iteration]." ".$GABOR_MAG_SCALE_LIMIT." ".$GABOR_MAG_ORIENTATION_LIMIT." ".$cropped_img_gabor_feature_vector." ".$query_img_gabor_feature_vector;

				$output = array();
				$output = exec($compare_cmd);

				$gabor_match_coeffs[$counter] = $output;
				$gabor_img_paths[$counter] = $this_img_path;

				$counter++;

				// TODO: ASYNCHRONOUS MODEL

			}

		}

		$binary_iteration++;

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
		<img src="<?php print $crop_path; ?>"/><br/><br/><br/>

		<?php for ($i=0; $i<sizeof($binary); $i++) { ?>

			<div> 			

					<h3>Result (<?php print $binary[$i]; ?>):</h3>
					<br/>

					<?php if ($binary[$i] == "histogram_backproject") { 
					
						asort($match_coeffs[$i], SORT_NUMERIC);	// arsort for INTERSECT, asort for EMD and DD

						foreach ($match_coeffs[$i] as $key => $val) { ?>

							<?php if ($match_coeffs[$i][$key] != "") { ?>

						   		<img style="width: 50px; height: 50px;" src="<?php print $texture_files[$i][$key]; ?>"/><?php print $match_coeffs[$i][$key]; ?><br/> 

							<?php } ?>

						<?php }

					} else if ($binary[$i] == "compare_gabor_mag_images") { 

						asort($gabor_match_coeffs, SORT_NUMERIC);

						foreach ($gabor_match_coeffs as $key => $val) { ?>

							<?php if ($gabor_match_coeffs[$key] != "") { ?>

						   		<img style="width: 100px; height: 100px;" src="<?php print $gabor_img_paths[$key]; ?>"/><?php print $gabor_match_coeffs[$key]; ?><br/> 

							<?php } ?>

						<?php }
	

					} ?>

					<br/>

			</div>

		<?php } ?>

	</div>
</center>
</body>
</html>


		
