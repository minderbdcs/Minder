<?php
if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
?>
<html>
<head>
<?php
include "viewport.php";

require_once 'DB.php';
require 'db_access.php';
include 'Printer.php';

/**
 * getPrinterOpts
 *
 * @param $Link
 */
function getPrinterOpts($Link) {
    $sql = 'SELECT DEVICE_ID FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' ORDER BY DEVICE_ID';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read printers!');
    }
    $printerOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $printerOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $printerOpts;
}

/**
 * getPrinterIp
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterIp($Link, $printerId) {
    $result = '';
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' AND DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}

// connect to database
if (!($Link = ibase_pconnect($DBName2, $User, $Password))) {
    echo("Unable to Connect!<BR>\n");
    exit();
}

// get list of printers
$sql = "SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE DEVICE_TYPE = 'PR'";
$result = ibase_query($Link, $sql);

/*
if system is using bdcsprint then use print_requests table for print jobs 
	just update the print request to NP to reprint
if system is using bartender have to search for print files in the folders
	then copy/rename to reprint
if system is creating flat files which are sent to the device on port 9100
	use Printer send command to reprint
	this is the current mode of operation
*/
$paths = array();
if (false == $result) {
    $paths = array('/data/asset.rf/');
} else {
    while( false != $row = ibase_fetch_row($result)) {
        $paths[] = $row[0];   
    }
}

//var_dump($paths);

$paths = str_replace('/', DIRECTORY_SEPARATOR, $paths);
$paths = str_replace('\\', DIRECTORY_SEPARATOR, $paths);

//var_dump($paths);

$folders = array();
foreach ($paths as $key => $val) {
    if (is_dir($val)) {
        $dh = @opendir($val);
        if (!$dh) {
            exit('Is not valid directory ' . htmlspecialchars($val, ENT_QUOTES) . '.');
        }
        while (false !== ($file = readdir($dh))) {
            if (substr($file, 0, 1) == '.') {
                continue; 
            } 
            $entry = rtrim($val, '\\/') . DIRECTORY_SEPARATOR . $file;
            if (is_dir($entry)) {
                $folders[$entry] = $file;
            }
        }
    }
}
arsort($folders);

//var_dump($folders);

$year = isset($_POST['year']) ? intval($_POST['year']) : null;
$month = isset($_POST['month']) ? intval($_POST['month']) : null;
$day = isset($_POST['day']) ? intval($_POST['day']) : null;

$list = array();
foreach ($folders as $dir => $date) {
    if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#is', $date, $p)) {
        list(, $year1, $month1, $day1) = $p;
        if ($year > 0) {
            if ($year != $year1) {
                continue; 
            } 
        }
        if ($month > 0) {
            if ($month != $month1) {
                continue; 
            } 
        }
        if ($day > 0) {
            if ($day != $day1) {
                continue; 
            } 
        }
    }
    $d = new FileList($dir);
    $list = array_merge($list, $d->getFileListRecursively());
}
?>
</head>
<body>
<form method="post" name="main">
<?php
$label   = "";
$printer = "";
if (isset($_POST['label'])) {
    $label = $_POST['label'];
}

if (isset($_POST['printer'])) {
    $printer = $_POST['printer'];
}

if (null != $label && null != $printer) {
    $printerIp = getPrinterIp($Link, $printer);
    if ($printerIp == '') {
        echo 'No IP address for printer ' . $printer;
    } else {
	/* have to find the file in the print requests
	   copy the file to the default location
	   then update the print request to CP.
	*/

        /* this does the old send direct to printer
           no send via ftp
           or send to print queue 
           or even use print_requests */
        require_once "Minder/Version.php";

        $myVersion = new Minder_Version;
        $thisVersion = $myVersion->getFull();
        $thisVersionPart = explode(".", $thisVersion);
        if ( $thisVersionPart[3]  < 3200 ) {
            $p = new Printer($printerIp);
            $value = file_get_contents($label);
            $p->send($value);
        } 
    }        
}
$printerOpts = getPrinterOpts($Link);
?>
<fieldset style=\"width:300px;\">
<legend>Files with ISSN labels</legend>
<div style="zoom: 1; overflow: hidden;">
<label for="year" style="float: left; width: 16em;">Year (YYYY, 4 digits):</label><input id="year" type="text" name="year" value="<?php echo empty($year) ? '' : $year ?>" size="4" /><br />
<label for="month" style="float: left; width: 16em;">Month (MM, 2 digits):</label><input id="month" type="text" name="month" value="<?php echo empty($month) ? '' : ($month < 10 ? '0' . $month : $month) ?>" size="2" /><br />
<label for="day" style="float: left; width: 16em;">Day (DD, 2 digits):</label><input id="day" type="text" name="day" value="<?php echo empty($day) ? '' : ($day < 10 ? '0' . $day : $day) ?>" size="2" /><br />
<input type="submit" name="filter" value="Filter" /><br />
</div>
<select name="label" id="label">
<?php   
foreach ($list as $key => $val) {
    //echo ("key:". $key . " val:" . $val );
    //if (preg_match('/^ISSN.*/', $val)) {
    if (preg_match('/.*ISSN.*/', $val)) {
        $wk_file_size = filesize($val);
        if ($wk_file_size > 0) {
            $option = '<option value="' . $val . '"';
            if ($val == $label) {
                $option .= 'selected="selected"';
            }
            $option .= '>' . $val . '</option>';
            echo $option;
        }        
    }        
    if (preg_match('/^PICK_LABEL.*/', $val)) {
        $wk_file_size = filesize($val);
        if ($wk_file_size > 0) {
            $option = '<option value="' . $val . '"';
            if ($val == $label) {
                $option .= 'selected="selected"';
            }
            $option .= '>' . $val . '</option>';
            echo $option;
        }        
    }        
}
?>
</select>
</fieldset>
<br />
Re-print label on:
<select name="printer" id="printer">
<?php    
foreach($printerOpts as $key => $val) {
        $option = '<option value="' . $val . '"';
        if ($val == $printer) {
            $option .= 'selected="selected"';
        }
        $option .= '>' . $val . '</option>';
        echo $option;
}
echo '</select><br />';    
?>
</form>
<input type="image" src="/icons/whm/REPRINT_50x100.gif" alt="ReprintLabels" onclick="document.forms.main.submit()">
<input type="image" src="/icons/whm/Back_50x100.gif" alt="Back" onclick="document.forms.back.submit()">
<form action="../receive/receive_menu.php" method="post" name="back">
</form>
<?php    
exit(0);
/**
 * FileList
 * 
 * Get file list into the array recursively 
 *
 * PHP 4.4.7
 * 
 * @package   FileUtils
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 */
class FileList 
{
    var $dirIterator;
    var $level;

    /**
     * Initialise an instance
     *
     * @param string $path
     * @return void
     */
    function FileList($path)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $this->level = rtrim($path, DIRECTORY_SEPARATOR);
        if (false == ($this->dirIterator = opendir($path))) {
            user_error("Can't initialize class FileList with path '" . $path . "';");
        } else {
            //echo $path . " success init " . $this->level . "<br/>";            
        }
    }

    /**
     * Read file list recursively into the array
     *
     * @return array
     */
    function getFileListRecursively()
    {
        $output = array();
        while (false !== ($file = readdir($this->dirIterator))) {
            if ('..' != $file && '.' != $file ) {
                if (is_dir($this->level . DIRECTORY_SEPARATOR . $file)) {
                    $children = new FileList(realpath($this->level . DIRECTORY_SEPARATOR . $file));
                    $output = array_merge($output, $children->getFileListRecursively());
                } else {
                    if ($file) {
                        $output[] = realpath($this->level . DIRECTORY_SEPARATOR .  $file);
                    }
                }
            }
        }
        return $output;
    }    
}
    
?>
</body>
</html>
