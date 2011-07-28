<?php 

	function isRunning($pid) 
	{
		$cmd = "ps ".$pid." | wc -l";	
		$result = shell_exec($cmd);
		
		if ($result != 1) return true;
	}

	$timer = 5;	

	// TODO: add a timeout and status flag to F if failed. Also kill process!

	while(isRunning($argv[1])) {

		// TODO: append to queue file and update time

		sleep($timer);

	}	

	// TODO: change status of queue file to S

	return 0;

?>
