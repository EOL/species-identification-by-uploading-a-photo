<?php

$WARNING_ERROR_BOX = '<font style="background-color: yellow; border-color: black; border-width: 1px; border-style: solid;">WARNING</font>';
$CRITICAL_ERROR_BOX = '<font style="background-color: red; border-color: black; border-width: 1px; border-style: solid;">CRITICAL</font>';
$SUCCESS_BOX = '<font style="background-color: green; border-color: black; border-width: 1px; border-style: solid;">SUCCESS</font>';

ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

// open database connection

include("connect.php");

//var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$species = $_POST['species'];

} 

?>

<html>

	<head>

		<script type="text/javascript" src="Jquery/js/jquery-1.5.1.min.js"></script> 
		<script type="text/javascript" src="Jquery/js/jquery-ui-1.8.13.custom.min.js"></script> 

		<style> 
			#test-list { list-style: none; }
			#test-list li { margin-left:-40px; display: block; padding: 10px 10px; margin-bottom: 3px; background-color: #efefef; }
			#test-list li img.handle { margin-right: 0px; cursor: move; }
			#info { display: block; padding: 10px; margin-bottom: 20px; border: 1px solid #333; background-color: #efefef; }
		</style>

		<script type="text/javascript">
			$(document).ready(function() {
				$("#test-list").sortable({
					handle : '.handle',
					update : function () {
						var order = $('#test-list').sortable('serialize');
						$("#info").load("order_toolchain_p.php?species=<?php print $species; ?>&"+order);
					}
				});
			});
		</script>

	</head>
	<body style="background-color: #F0F3ED; text-align: center;">

	<br/>

	<div style="border-width: 1px; border-style: solid; border-color: black; padding: 10px; text-align: center; width: 1200px; background-color: white; margin: 0 auto; overflow: auto;">

		<form name="select_toolchain" id="select_toolchain" action="order_toolchain.php" method="post">

			<div>

				<h1>Order toolchain</h1>
				<h3>Step 1:</h3>
				<p>Select an existing toolchain.</p>

				<select name="species" onChange="this.form.submit();">

				<option value=""></option>

				<?php

				$query = "SELECT * FROM species WHERE has_toolchain = 1";

				$result = $dbh->query($query);

				foreach($result as $row) {
	
					$id = $row['id'];
					$process_id = $row['process_id'];
					$name = $row['name'];

				?>

					<option <?php if(isset($species) && ($species == $id)) print SELECTED; ?> value="<?php print $id; ?>"><?php print $name; ?></option>

				<?php 

				}

				?>

				</select>
	
			</div>

			<br/>

			<div>

				<h3>Step 2:</h3>
				<p>Reorder toolchain.</p>

				<pre><div id="info">Waiting for update</div></pre>

				<div>

					<ul id="test-list">

						<?php if(!empty($species)) {

						$query = "SELECT p.id as process_id, program_id as program_id, `order` as 'order', name as name, is_match as is_match FROM process p LEFT JOIN programs pr ON p.program_id = pr.id WHERE species_id = ".$species." ORDER BY `order` ASC";

						$result = $dbh->query($query);

							foreach($result as $row) {
	
								$process_id = $row['process_id'];
								$program_id = $row['program_id'];
								$order = $row['order'];
								$name = $row['name'];
								$is_match = $row['is_match'];

							?>

								<?php if($is_match == FALSE) { ?>

									<li id="listItem_<?php print $program_id ?>"><img align="left" src="res/arrow.png" alt="move" width="16" height="16" class="handle" /><strong><?php print $name; ?></strong></li>

								<?php 

								}
		
							}

						} else { 

							$error_msg = $CRITICAL_ERROR_BOX."&nbsp;&nbsp;&nbsp;No species selected.&nbsp;&nbsp;&nbsp;".$CRITICAL_ERROR_BOX;

						}

						?>

					</ul>

				</div>

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
