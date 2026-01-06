<?php
/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Utility
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

if ($argc > 1) {
    $path = realpath($argv[1]);

}

$curdir = getcwd();
chdir($path);
echo 'start from ' . $path;
processdir($path);

chdir($curdir);
die();

function processdir ($path)
{
        echo 'Proceed with ' . $path . PHP_EOL;
        $d = new DirectoryIterator($path);
        foreach ($d as $file) {
            if ($d->isDir() && !$d->isDot() && substr($file, 0, 1) !== '.' && $file != 'includes' && $file != 'library') {
                processdir(trim($path, "\\/") . DIRECTORY_SEPARATOR . $file);
            } elseif ($d->isFile()) {
                formatter(trim($path, "\\/") . DIRECTORY_SEPARATOR . $file);
            } else {
                echo 'SKIPED ' . trim($path, "\\/") . DIRECTORY_SEPARATOR . $file . PHP_EOL;
            }
        }
    return;
}

function formatter($fn)
{
    $matches = array();
    if (preg_match('~^.*phtml$~', $fn, $matches) || preg_match('~^.*php$~', $fn, $matches)) {
        echo 'GO ' . $fn . PHP_EOL;
    } else {
        echo 'SKIPED ' . $fn . PHP_EOL;
    }
    $content = file_get_contents($fn);
    $from    = array("\r\n", chr(9), " \n");
    $to      = array("\n", '    ', "\n");
    for ($i = 0; $i < 30; $i++) {
        $content = str_replace($from, $to, $content);
    }
    file_put_contents($fn, $content);
}
?>