<?php

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

// open database connection

include("connect.php");

//var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$i = $_POST['i'];
	$j = $_POST['j'];
	$process = $_POST['process'];
	$continue = $_POST['continue'];
	$patch_no = $_POST['patch_no'];

	if ($process == TRUE) 		// process the texture
	{		

		$targ_w = $_POST['w'];
		$targ_h = $_POST['h'];
		$targ_x = $_POST['x'];
		$targ_x2 = $_POST['x2'];
		$targ_y = $_POST['y'];
		$targ_y2 = $_POST['y2'];

		$species = $_POST['species'];

		$last_json = json_decode(file_get_contents("http://www.eol.org/api/pages/".$j.".json?images=75&details=1"));
		$last_file = urldecode($last_json->dataObjects[$i]->mediaURL);
		$last_file_parts = pathinfo($last_file);
	
		$output_img = "textures/patch_".$patch_no."_".$last_file_parts['filename'].".jpg";
		//$output_log = "patch.log";

		$jpeg_quality = 100;

		$src = $last_file;
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

		$query = "INSERT INTO textures (`path`, `number`, `species_id`) VALUES ('".$output_img."',".$patch_no.",".$species.")";

		$dbh->exec($query);

	}

	if ($continue == TRUE)		 // advance to next image in EOL database?
	{	

		$i++;

		$this_json = json_decode(file_get_contents("http://www.eol.org/api/pages/".$j.".json?images=75&details=1"));	// need this to establish if we've reached end of objects

		if ($i == count($this_json->dataObjects))
		{

			$i = 1;
			$j++;		

		}

		$patch_no = 0;
		unset($species);

	} else {	// or don't

		$patch_no++;

	}


} else {			// initial setting of

	$i = 1;			// dataObject
	$j = 747;		// page
	$patch_no = 0;		// patch number

}

// get the image from the EOL database

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

			if ((document.process.w.value <= 0) ||(document.process.h.value <= 0))
			{
				alert("Must specify a window.");
				valid = false;
			}

			return valid;

		}

		</script>


	</head>

	<body style="background-color: #F0F3ED;">

	<br/>

	<center><div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white;">

		<form name="process" id="process" action="make_textures.php" method="post" onsubmit="return checkCoords();">

			<div>

				<h1>Make textures</h1>
				<h3>Step 1:</h3>
				<p>What is this?</p>

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

				<h3>Step 2:</h3>
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

