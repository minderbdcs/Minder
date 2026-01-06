<?php
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		print("have http_x_forwarded_for " . $_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	else
	{
		print("have remote_addr " . $_SERVER['REMOTE_ADDR']);
	}
?>
