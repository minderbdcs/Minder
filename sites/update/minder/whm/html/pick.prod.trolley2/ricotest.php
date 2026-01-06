<?php
header("Content-Type:text/xml");
header("Cache-Control:no-cache");
header("Pragma:no-cache");
echo "<ajax-response>
<response type=\"element\" id=\"display\">
<p>".$_SERVER['SERVER_SIGNATURE']."</p>
</response>
<response type=\"element\" id=\"heading\">
<h3>Some Information about the Server</h3>
</response>
</ajax-response>";
?>
