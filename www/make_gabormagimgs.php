<?php

$WARNING_ERROR_BOX = '<font style="background-color: yellow; border-color: black; border-width: 1px; border-style: solid;">WARNING</font>';
$CRITICAL_ERROR_BOX = '<font style="background-color: red; border-color: black; border-width: 1px; border-style: solid;">CRITICAL</font>';
$SUCCESS_BOX = '<font style="background-color: green; border-color: black; border-width: 1px; border-style: solid;">SUCCESS</font>';

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

include("connect.php"); // open database connection
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$i = $_POST['i'];
	$j = $_POST['j'];

	if (isset($_POST['changepage']) && $_POST['changepage'] == 1) 
	{	

		$patch_no = 0;

	} else {

		if (isset($_POST['process']) &&	$_POST['process'] == TRUE) 		// process the gabor magnitude image?
		{		

			$patch_no = $_POST['patch_no'];

			$targ_w = $_POST['w'];
			$targ_h = $_POST['h'];
			$targ_x = $_POST['x'];
			$targ_x2 = $_POST['x2'];
			$targ_y = $_POST['y'];
			$targ_y2 = $_POST['y2'];

			$species_id = $_POST['species_id'];
		
			$ref_file_path = $_POST['ref_file_path'];

			$last_json = json_decode(file_get_contents("http://www.eol.org/api/pages/".$j.".json?images=75&details=1"));
			$last_file = urldecode($last_json->dataObjects[$i]->mediaURL);
			$last_file_parts = pathinfo($last_file);
	
			$output_img = $GABORMAGIMG_DIR_PATH."gabormagimg_".$patch_no."_".$last_file_parts['filename'].".jpg";

			$jpeg_quality = 100;

			$src = $last_file;
			$img_r = imagecreatefromjpeg($src);
			$dst_r = ImageCreateTrueColor($targ_w, $targ_h);

			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
			imagejpeg($dst_r,$output_img,$jpeg_quality);

			for ($scale=0; $scale<$GABOR_MAG_SCALE_LIMIT; $scale++) {

				for ($orientation=0; $orientation<$GABOR_MAG_ORIENTATION_LIMIT; $orientation++) {

					$output_gabormag_img = $GABORMAGIMG_DIR_PATH."gabormagimg_".$patch_no."_".$orientation."_".$scale."_".$last_file_parts['filename'].".pgm";
					$gabor_cmd = $BIN_DIR_PATH."construct_gabor_mag_image ".$output_img." ".$orientation." ".$scale." ".$output_gabormag_img;

					// generate Gabor magnitude images	
					$output = array();
					$output = exec($gabor_cmd);

					$output_stats = explode(",", $output);	// 2D array, [0] = mean, [1] = stddev

					$query = "SELECT count(*) FROM gabormagimgs WHERE gabormagimg_path = '".$output_gabormag_img."'";
					$result = $dbh->query($query);

					$count = $result->fetchColumn();

					if ($count > 0) {

						$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;A gabor magnitude image already exists for this image/patch number.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;
						break;

					} else {

						$query = "INSERT INTO gabormagimgs (`gabormagimg_path`, `img_path`, `ref_path`, `number`, `species_id`, `orientation`, `scale`, `mean`, `stddev`) VALUES ('".$output_gabormag_img."', '".$output_img."', '".$ref_file_path."',".$patch_no.",".$species_id.",".$orientation.",".$scale.",".$output_stats[0].",".$output_stats[1].")";
				
						$dbh->exec($query);

						$success_msg = $SUCCESS_BOX."&nbsp;&nbsp;&nbsp;Gabor magnitude image added.&nbsp;&nbsp;&nbsp;".$SUCCESS_BOX;

					}

				}

			}

		}

		if (isset($_POST['continue']) && $_POST['continue'] == TRUE) 		 // advance to next image in EOL database?
		{	

			$patch_no = $_POST['patch_no'];

			$i++;

			$this_json = json_decode(file_get_contents("http://www.eol.org/api/pages/".$j.".json?images=75&details=1"));	// need this to establish if we've reached end of objects

			if ($i == count($this_json->dataObjects))
			{

				$i = 1;
				$j++;		

			}

			$patch_no = 0;
			unset($species_id);

		} else {			// or don't, take another gabor magnitude image

			$patch_no = $_POST['patch_no'];

			$patch_no++;

		}
	
	}

} else {			// initial setting of

	$i = 1;			// dataObject
	$j = 1;			// page
	$patch_no = 0;		// patch number

}

// get the image from the EOL API

$this_json = json_decode(file_get_contents("http://www.eol.org/api/pages/".$j.".json?images=75&details=1"));
$this_file = urldecode($this_json->dataObjects[$i]->mediaURL);
$file_parts = pathinfo($this_file);

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

			if ((document.process.w.value <= 0) || (document.process.h.value <= 0))				// image has not been cropped
			{
				alert("Must specify a window.");
				valid = false;
			} else if (document.process.w.value*document.process.h.value < <?php print $MIN_GABORMAGIMG_SIZE; ?>)	// image must have a minimum number of pixels, $MIN_GABORMAGIMG_SIZE
			{
				alert("Minimum texture size is <?php print $MIN_GABORMAGIMG_SIZE ?>");
				valid = false;
			}

			return valid;

		}

		</script>

	</head>

	<body style="background-color: #F0F3ED;">

	<br/>

	<center><div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white;">

		<form name="change_page" id="change_page" action="make_gabormagimgs.php" method="post">

			<div>

				<h1>Make Gabor Magnitude Image</h1>
				<h3>Step 1:</h3>
				<p>Select an EOL page and hit Go! (<a href="http://www.eol.org/api/docs/pages" target="_new">See EOL Doc<a/>)</p>

				<input type="text" name="j" value="<?php if (isset($j)) { print $j; } ?>"/>
				<input type="hidden" name="i" value="1"/>
				<input type="hidden" name="changepage" value="0"/>
				<input type="submit" value="Go!" onclick="this.form.changepage.value=1"/>

			</div>

			<br/>

		</form>

		<form name="process" id="process" action="make_gabormagimgs.php" method="post" onsubmit="return checkCoords();">

			<div>

				<h3>Step 2:</h3>
				<p>What species do you think this?</p>

				<select name="species_id">

				<?php 

				$query = "SELECT * FROM species";

				$result = $dbh->query($query);

				foreach($result as $row) {
	
					$this_species_id = $row['id'];
					$this_species_name = $row['name'];

				?>

					<option <?php if(isset($species_id) && ($this_species_id == $species_id)) print "SELECTED" ?> value="<?php print $this_species_id; ?>"><?php print $this_species_name; ?></option>

				<?php 

				} 

				?>

				</select>

			</div>

			<br/>

			<div>

				<h3>Step 3:</h3>
				<p>Mark a region of interest.</p>

				<!-- This is the image we're attaching Jcrop to -->
				<center><img src="<?php print $this_file; ?>" id="cropbox" style="border-color: black; border-width: 1px; border-style: solid;"/></center
				<br/><br/>

				<label style="font-size: 14" >X1 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="x" name="x" /></label>
				<label style="font-size: 14" >Y1 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="y" name="y" /></label>
				<label style="font-size: 14" >X2 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="x2" name="x2" /></label>
				<label style="font-size: 14" >Y2 <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="y2" name="y2" /></label>
				<label style="font-size: 14" >W <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="w" name="w" /></label>
				<label style="font-size: 14" >H <input style="border-width: 1px; border-style: solid; border-color: black;" type="text" size="4" id="h" name="h" /></label>
				<br/><br/>
				<input type="hidden" name="process" value="0"/>
				<input type="hidden" name="continue" value="0"/>
				<input type="hidden" name="patch_no" value="<?php print $patch_no; ?>"/>
				<input type="hidden" name="i" value="<?php print $i; ?>"/>
				<input type="hidden" name="j" value="<?php print $j; ?>"/>
				<input type="hidden" name="ref_file_path" value="<?php print $this_file; ?>"/>
				<input type="submit" value="Crop image and add another patch" onClick="validate_form(); this.form.process.value=1; this.form.continue.value=0; return valid;"/>
				<input type="submit" value="Crop image and continue" onClick="validate_form(); this.form.process.value=1; this.form.continue.value=1; return valid;"/>
				<input type="submit" value="Skip image" onClick="this.form.process.value=0; this.form.continue.value=1;"/>

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

	</div></center>

	</body>

</html>

