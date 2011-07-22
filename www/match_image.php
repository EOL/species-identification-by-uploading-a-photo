<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

// open database connection

include("connect.php");

// match image

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	if (isset($_FILES['image']['name']) && (!empty($_FILES['image']['name']))) {

		$image_name = $_FILES['image']['name'];

		$dest = "/var/www/html/gsoc/images/".$_FILES['image']['name'];

		copy($_FILES['image']['tmp_name'], $dest);

		$file_upload_success = TRUE;
		$img_path = "images/".$_FILES['image']['name'];

	}

	if (isset($_POST['crop'])) 		// crop the image
	{		

		$targ_w = $_POST['w'];
		$targ_h = $_POST['h'];
		$targ_x = $_POST['x'];
		$targ_x2 = $_POST['x2'];
		$targ_y = $_POST['y'];
		$targ_y2 = $_POST['y2'];

		$path = $_POST['img_path'];

		$img_file_parts = pathinfo($path);

		$output_img = "images/CROP_".$img_file_parts['filename'].".jpg";
		//$output_log = "patch.log";

		$jpeg_quality = 100;

		$src = $path;
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		//header('Content-type: image/jpeg');
		//imagejpeg($dst_r,NULL,$jpeg_quality);

		imagejpeg($dst_r,$output_img,$jpeg_quality);

		/*$output_fh = fopen($output_log, 'a') or die("can't open file");
		$output_txt = $output_img."\t".$species."\t".$targ_x."\t".$targ_x2."\t".$targ_y."\t".$targ_y2."\t".$targ_w."\t".$targ_h."\n";
		fwrite($output_fh , $output_txt);
		fclose($output_fh );*/

		$file_crop_success = TRUE;
		$crop_path = $output_img;

	}


	if (isset($_POST['process'])) 		// crop the image
	{		

		$species = $_POST['species'];
		$crop_path = $_POST['crop_path'];

		$query = "SELECT * from species s LEFT JOIN process p on s.id = p.species_id LEFT JOIN programs pr on p.program_id = pr.id where s.id = ".$species." and is_match = 1 order by `order`";

		$result = $dbh->query($query);

		foreach($result as $row) {
	
			$path = $row['path'];

		}

		$handle = opendir('/var/www/html/gsoc/textures');

		$counter = 0;

		$texture_paths = "";

		while (false !== ($file = readdir($handle))) {

			$file_parts = pathinfo($file);

			if ($file_parts['extension'] == "jpg") {

				$texture_paths = $texture_paths.'"/var/www/html/gsoc/textures/'.$file.'" ';

				$files[$counter] = "textures/".$file;
				$counter++;

			}

		}


		$match_cmd = $path." ".$crop_path." ".$texture_paths." EMDL1";		// METHOD COULD BE EMDL1 or INTERSECT

		$output = array();
		$output = exec($match_cmd);

		$match_coeffs = explode(",", $output);

		$maxvalue = max($match_coeffs);

		while(list($key,$value)=each($match_coeffs)){

			if($value==$maxvalue)$maxindex=$key;

		} 

		//print $files[$maxindex]." ".$maxvalue;

		$found = 1;


	}

}

?>

<html>
	<head>

		<script src="Jcrop/js/jquery.min.js"></script>
		<script src="Jcrop/js/jquery.Jcrop.js"></script>
		<link rel="stylesheet" href="Jcrop/css/jquery.Jcrop.css" type="text/css" />

		<script language="Javascript">

			// Remember to invoke within jQuery(window).load(...)
			// If you don't, Jcrop may not initialize properly
			jQuery(document).ready(function(){

				jQuery('#cropbox').Jcrop({
					onChange: showCoords,
					onSelect: showCoords
				});

			});

			// Our simple event handler, called from onChange and onSelect
			// event handlers, as per the Jcrop invocation above
			function showCoords(c)
			{
				jQuery('#x').val(c.x);
				jQuery('#y').val(c.y);
				jQuery('#x2').val(c.x2);
				jQuery('#y2').val(c.y2);
				jQuery('#w').val(c.w);
				jQuery('#h').val(c.h);
			};

		</script>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="upload" id="upload" action="match_image.php" enctype="multipart/form-data" method="post">

			<h1>Image Matching</h1>

			<div>

				<h3>Step 1:</h3>
				<p>Upload an image.</p>

			</div>

			<?php if(isset($file_upload_success) && $file_upload_success = TRUE) { ?>

				<font color="red">Image uploaded (<?php print $image_name; ?>).</font>

			<?php } else { ?>

			<div>

				<input type="file" name="image"/>

			</div>

			<br/>

			<div id="submit">

				<input type="submit" value="Upload" />

			</div>

			<?php } ?>

			<br/>

			<div>

					<h3>Step 2:</h3>
					<p>Mark a region of interest.</p>

			</div>

			<?php if(isset($file_crop_success) && $file_crop_success = TRUE) { ?>

			<div>

				<img src="<?php print str_replace('/var/www/html/gsoc/', '', $crop_path); ?>"/>				

			</div>

			<?php } else if(isset($file_upload_success) && $file_upload_success = TRUE) { ?>
	
			<form name="crop" id="crop" method="POST">

				<div>

					<!-- This is the image we're attaching Jcrop to -->
					<center><img src="<?php print $img_path; ?>" id="cropbox" style="border-color: black; border-width: 1px; border-style: solid;"/></center
					<br/><br/>

					<label style="font-size: 14" >X1 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="x" name="x" /></label>
					<label style="font-size: 14" >Y1 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="y" name="y" /></label>
					<label style="font-size: 14" >X2 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="x2" name="x2" /></label>
					<label style="font-size: 14" >Y2 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="y2" name="y2" /></label>
					<label style="font-size: 14" >W <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="w" name="w" /></label>
					<label style="font-size: 14" >H <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="h" name="h" /></label>
					<br/><br/>
					<input type="hidden" name="crop" value="0"/>
					<input type="hidden" name="img_path" value="<?php print $img_path; ?>"/>
					<input type="submit" value="Crop image" onClick="this.form.crop.value=1;"/>

				</div>

			</form>

			<?php } ?>

			<?php if(isset($file_crop_success) && $file_crop_success = TRUE) { ?>

			<form name="process" id="process" method="POST">

				<div>

					<h3>Step 3:</h3>
					<p>Provide us with some information about what you're submitting.</p>

					<select name="species">

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

				<div>

					<input type="hidden" name="process" value="0"/>
					<input type="hidden" name="crop_path" value="<?php print $crop_path; ?>"/>
					<input type="submit" value="Show me the results!" onClick="this.form.process.value=1;"/>

				</div>

			</form>

			<?php } ?>

			<?php if (isset($found)) { ?>

			<div>

				<h3>Result:</h3>
				<br/>

					<!--<img src="<?php print str_replace('/var/www/html/gsoc/', '', $crop_path); ?>"/>
					<img src="<?php print str_replace('/var/www/html/gsoc/', '', $files[$maxindex]); ?>"/>
					<?php print $maxvalue; ?>-->

					<img src="<?php print str_replace('/var/www/html/gsoc/', '', $crop_path); ?>"/><br/><br/><br/>

					<?php
					
					asort($match_coeffs, SORT_NUMERIC);	// arsort for intersect, asort for EMD

					foreach ($match_coeffs as $key => $val) { ?>

					   	<img style="width: 30px; height: 30px;" src="<?php print $files[$key]; ?>"/><?php print $match_coeffs[$key]; ?><br/> 

					<?php } ?>

			</div>

			<?php } ?>

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

				<div style="float: left;">
	
					<a href="index.php">Back</a>

				</div>

				<div style="float: right;">
	
					<a href="match_image.php">Reset</a>

				</div>

			</div>	

		</form>

	</div></center>

	</body>

</html>
