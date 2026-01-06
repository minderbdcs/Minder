  <?php
/*
*/
  $docself = $_SERVER['PHP_SELF'];
  $ver = "1.0 - 2013.07.18";
  
  $iniFile = "../default.ini";
  $minderConfig = "../../minder.ini";
  $default = parse_ini_file($iniFile);
  //$hostnameStr = $default['hostname'];
  //$dbAliasStr = $default['databaseAlias'];
  $hostnames = explode("\n", $default['hostname']);
  $dbAliases = explode("\n", $default['databaseAlias']);
  $licensees = explode("\n", $default['licensee']);
//var_dump($hostnames);
//var_dump($dbAliases);
  $mdrConfig = "/etc/Minder/Minder.ini";
  // expect in document root something like "/var/sites/sitename/html"
  // so the 4th entry is the sitename
  $mdrSitename = explode("/", $_SERVER['DOCUMENT_ROOT'])[3];
  $mdrConfig = "/etc/Minder/" . $mdrSitename . "/Minder.ini";
  $mdrConfig = strtolower($mdrConfig);
   //$minder =  parse_ini_file($minderConfig);
   $minder =  parse_ini_file($mdrConfig);
   $minderDB = explode(":", $minder['dsn.main']);
   $minderLicensee = explode(":", $minder['licensee']);
   $currentHost = $minderDB[0];
   $currentDBAlias = $minderDB[1];
   $currentLicensee = $minderLicensee[0];
   $hostnamesOptions = "";
   $dbAliasesOptions = "";
   $licenseesOptions = "";
   foreach ($hostnames as $hostnameIdx => $hostnameValue ) {
	if ($hostnameValue != "")
	{
		if ($hostnameValue == $currentHost)
		{
			$hostnamesOptions .=  "<option value=\"$hostnameValue\" selected >$hostnameValue";
		}
		else
		{
			$hostnamesOptions .=  "<option value=\"$hostnameValue\">$hostnameValue\n";
		}
	}
   }
   foreach  ($dbAliases as $dbAliasIdx => $dbAliasValue) {
	if ($dbAliasValue != "")
	{
		if ($dbAliasValue == $currentDBAlias)
		{
			$dbAliasesOptions .=  "<option value=\"$dbAliasValue\" selected >$dbAliasValue";
		}
		else
		{
			$dbAliasesOptions .=  "<option value=\"$dbAliasValue\">$dbAliasValue\n";
		}
	}
   }
   foreach  ($licensees as $licenseeIdx => $licenseeValue) {
	if ($licenseeValue != "")
	{
		if ($licenseeValue == $currentLicensee)
		{
			$licenseesOptions .=  "<option value=\"$licenseeValue\" selected >$licenseeValue";
		}
		else
		{
			$licenseesOptions .=  "<option value=\"$licenseeValue\">$licenseeValue\n";
		}
	}
   }
//var_dump($minder);
  //$_GET['filename']  = $minderConfig;
  $_GET['filename']  = $mdrConfig ;
  echo <<<HTMLCODE
  <!doctype html>
  <html>
<!DOCTYPE html>  
 <html>      
    <head>
    
      <style type="text/css">    
        body {
             font-size        : 12 pt;
             font-family      : arial;
             color            : #fa6;   /* white font color */
             background-color : #123;
             background-image : url(../media/bdcs_wm.gif);          
             line-height      : 12pt;
             margin           : 75px;  
          }
        a         {text-decoration: none;}
        a:link    {color:#9ef;} 
        a:visited {color:#9ef;} 
        a:active  {color:#9ef;} 
        a:hover   {color:#fb3; text-decoration: underline overline;}     
        h1 { text-align: center; color: #9cf;   /* white font color */ }  
        h2 { text-align: left; color: #afa; font-size: 100%  /* white font color */ }
        fieldset, textarea { text-decoration: none; -moz-border-radius:10px; border-radius: 10px; -webkit-border-radius: 10px; }
        input { text-decoration: none; font-size: 120%; -moz-border-radius:5px; border-radius: 5px; -webkit-border-radius: 5px; }
x
        </style>
      
      <script type="text/javascript">
      
        window.onload = function () {
           help.style.visibility = 'hidden';
        }
        
        function togglevisibility(id) {
          var e = document.getElementById(id);
          if(e.style.visibility == 'visible')
            e.style.visibility = 'hidden';
          else
            e.style.visibility = 'visible';
        } 
        
      </script>
      
    </head>    
    <body>

        <h1>Minder Config</h1> 
        <h2 style="text-align: center;">Version: $ver</h2>
        
HTMLCODE;

  // get file name or use default
  if (isset($_GET['filename'])) { 
    $filename = $_GET['filename']; 
  } else {
    $filename = 'noname.txt'; 
  }  
    
  // file contents or use default
  if (isset($_GET['content'])) { 
    $content = $_GET['content']; 
  } else {
    $content = "No file name was given or found.\nEnter text here.";
  }

  // get action or use default
  if (isset($_GET['action'])) { 
    $action = $_GET['action']; 
  } else {
    $action = 'default'; 
  }  
  
  // save file
  if( $action == "Save") {
    //$content = stripslashes($content);
    if(file_exists($filename) ) {
      $fp = @fopen($filename, "r");
      $content = fread($fp, filesize($filename));
      // now replace the dsn.main line
      $fromStr = "dsn.main = " . $currentHost . ":" . $currentDBAlias;
      $toStr = "dsn.main = " . $_GET['dbhost'] . ":" . $_GET['dbalias'];
      $newContent = str_replace($fromStr, $toStr, $content);
      //$content = htmlspecialchars($content);
      $content = $newContent;
      // now replace the licensee line
      $fromStr = "licensee = " . $currentLicensee  ;
      $toStr = "licensee = " . $_GET['licensee'] ;
      $newContent = str_replace($fromStr, $toStr, $content);
      //$content = htmlspecialchars($content);
      $content = $newContent;
      fclose($fp);
    }
    $fp = @fopen($filename, "w");
    if ($fp) {
      fwrite($fp, $content);
      fclose($fp);
      $currentHost = $_GET['dbhost'];
      $currentDBAlias = $_GET['dbalias'];
      $currentLicensee = $_GET['licensee'];
      $hostnamesOptions = "";
      $dbAliasesOptions = "";
      $licenseesOptions = "";
      foreach ($hostnames as $hostnameIdx => $hostnameValue ) {
	if ($hostnameValue != "")
	{
		if ($hostnameValue == $currentHost)
		{
			$hostnamesOptions .=  "<option value=\"$hostnameValue\" selected >$hostnameValue";
		}
		else
		{
			$hostnamesOptions .=  "<option value=\"$hostnameValue\">$hostnameValue\n";
		}
	}
      }
      foreach  ($dbAliases as $dbAliasIdx => $dbAliasValue) {
	if ($dbAliasValue != "")
	{
		if ($dbAliasValue == $currentDBAlias)
		{
			$dbAliasesOptions .=  "<option value=\"$dbAliasValue\" selected >$dbAliasValue";
		}
		else
		{
			$dbAliasesOptions .=  "<option value=\"$dbAliasValue\">$dbAliasValue\n";
		}
	}
      }
      foreach  ($licensees as $licenseeIdx => $licenseeValue) {
	if ($licenseeValue != "")
	{
		if ($licenseeValue == $currentLicensee)
		{
			$licenseesOptions .=  "<option value=\"$licenseeValue\" selected >$licenseeValue";
		}
		else
		{
			$licenseesOptions .=  "<option value=\"$licenseeValue\">$licenseeValue\n";
		}
	}
      }
    }
  }
  
  if(file_exists($filename) ) {
    $fp = @fopen($filename, "r");
    $content = fread($fp, filesize($filename));
    $content = htmlspecialchars($content);
    fclose($fp);
  }
  
    echo <<<HTMLCODE
    <table align=center ><tr><td>
      <form action="$docself" method=get >
        <fieldset width=500px>
          <table align=center >
          <tr><td width=100> </td> <td width=100> </td> <td width=100> </td> <td width=100> </td> <td width=100> </td></tr>
          <tr><td colspan=5>
          DB Host:<select name="dbhost" >
          $hostnamesOptions
          </select>
          DB Alias:<select name="dbalias" >
          $dbAliasesOptions
          </select> <br>
          </td></tr>
          <tr><td colspan=5>
          Licensee:<select name="licensee" >
          $licenseesOptions
          </select>
          </td></tr>
          <tr>
          <td><input type="hidden" name="filename" value="$filename"></td><td align=center><input type="submit" name="action" value="Save"></td><td></td><td align=center><input type=button value="Help" onclick="togglevisibility('help');"></td><td></td><td align=center><input type=button value="Back" onclick="location.href='..';"></td></tr>
          </table>
        </fieldset>
      </form>
    </td></tr>
    <tr><td>
    <div id="help">
      <br>
      <input type=button value="Hide Help" style = "font-size: 70%;" onclick="togglevisibility('help');"><br>
      <h2>Minder Config</h2>

      Version: $ver<br>
      By: Frank Leih  - MinderSeries.com<br>
      <br>
      This light weight editor is designed to allow editing of the Main database used for Minder.<br>
      <br>
    </div>
    </td></tr>    
    </table>
    
    

  </body>
</html>
HTMLCODE;

?>

