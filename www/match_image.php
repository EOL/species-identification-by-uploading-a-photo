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

	if (isset($_FILES['image']['name']) && (!empty($_FILES['image']['name']))) {		// upload image

		$image_name = $_FILES['image']['name'];

		$dest = $IMAGE_DIR_PATH.$_FILES['image']['name'];

		copy($_FILES['image']['tmp_name'], $dest);

		$file_upload_success = TRUE;
		$img_path = $dest;

	}

	if (isset($_POST['crop'])) 		// crop the image
	{		

		$file_upload_success = TRUE;
		$image_name = $_POST['image_name'];
		$img_path = $_POST['img_path'];

		$targ_w = $_POST['w'];
		$targ_h = $_POST['h'];
		$targ_x = $_POST['x'];
		$targ_x2 = $_POST['x2'];
		$targ_y = $_POST['y'];
		$targ_y2 = $_POST['y2'];

		$path = $_POST['img_path'];

		$img_file_parts = pathinfo($path);

		$output_img = $IMAGE_DIR_PATH."CROP_".$img_file_parts['filename'].".jpg";

		$jpeg_quality = 100;

		$src = $path;
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		imagejpeg($dst_r,$output_img,$jpeg_quality);

		$file_crop_success = TRUE;
		$crop_path = $output_img;

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

		<script type="text/javascript">

			function validate_form()
			{

				valid = true;

				if ((document.crop.w.value <= 0) || (document.crop.h.value <= 0))				// image has not been cropped
				{
					alert("Must specify a window.");
					valid = false;
				}

				return valid;

			}

		</script>

	</head>

	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<h1>Image Matching</h1>

		<div>

			<h3>Step 1:</h3>
			<p>Upload an image.</p>

		</div>

		<?php if(isset($file_upload_success) && $file_upload_success == TRUE) { ?>

			<font color="red">Image uploaded (<?php print $image_name; ?>).</font>

			<br/>

			<div>

					<h3>Step 2:</h3>
					<p>Mark a region of interest.</p>

			</div>

			<?php if(isset($file_crop_success) && $file_crop_success == TRUE) { ?>

				<div>

					<img src="<?php print $crop_path; ?>"/>			

				</div>

				<form name="process" id="process" method="POST" action="match_image_process.php">

					<div>

						<h3>Step 3:</h3>
						<p>Provide us with some information about what you're submitting.</p>

						<select name="species_id">

						<?php 

						$query = "SELECT * FROM species";

						$result = $dbh->query($query);

						foreach($result as $row) {
	
							$this_species_id = $row['id'];
							$this_species_name = $row['name'];

						?>

							<option <?php if(isset($species_id) && ($this_species_id == $species_id)) print "SELECTED" ?> value="<?php print $this_species_id; ?>"><?php print $this_species_id; ?></option>

						<?php 

						} 

						?>

						</select>

					</div>

					<br/>

					<div>

						<input type="hidden" name="crop_path" value="<?php print $crop_path; ?>"/>
						<input type="submit" value="Show me the results!"/>

					</div>

				</form>

			<?php } else { ?>

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
						<input type="hidden" name="image_name" value="<?php print $image_name; ?>"/>
						<input type="submit" value="Crop image" onClick="validate_form(); this.form.crop.value=1; return valid;"/>

					</div>

				</form>

			<?php } ?>

		<?php } else { ?>

			<form name="upload" id="upload" action="match_image.php" enctype="multipart/form-data" method="post">
	
				<div>

					<input type="file" name="image"/>

				</div>

				<br/>

				<div id="submit">

					<input type="submit" value="Upload" />

				</div>

			</form>

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

	</div></center>

	</body>

</html>
