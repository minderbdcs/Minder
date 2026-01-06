<?php

$svnVersion    = (int)exec('svnlook youngest /var/svn/minder');
//$svnVersion   = (int)exec('svnversion /var/sites/nightly.barcoding.com.au/minder');
$fileLocations = array('/var/sites/nightly.barcoding.com.au/minder/includes/Minder.php',
                       '/var/sites/als.barcoding.com.au/minder/includes/Minder.php',
                       '/var/sites/bss.barcoding.com.au/minder/includes/Minder.php',
                       '/var/sites/fpg.barcoding.com.au/minder/includes/Minder.php',
                       '/var/sites/mus.barcoding.com.au/minder/includes/Minder.php'
                      );
                      
foreach($fileLocations as $fileLocation) {
    // read data
    $data      = file_get_contents($fileLocation);
    // set new version
    $newData = getSetVersion($data, $svnVersion);
    // set data
                 file_put_contents($fileLocation, $newData);
}


function getSetVersion($data, $newVersion) {
    $version = 0;
    eregi("('[\.0-9]+')", $data, $regs);
    $version    = explode('.', $regs[0]);
    $oldVersion = (int)$version[3];
   
    $data = str_replace($oldVersion, $newVersion, $data);
    
    return $data;
}
  
?>
