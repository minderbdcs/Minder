<?php

/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Merges the elements of one or more arrays together so that the values
 * of one are appended to the end of the previous one.
 * It returns the resulting array
 *
 * If the input arrays have the same keys, then the later
 * value for that key will overwrite the previous one
 *
 * DIFFERENCE from original array_merge:
 * integer keys not reindexed.
 *
 * @author Sergey Boroday <sergey.boroday@binary-studio.com>
 *
 * @return array|false Merged array or FALSE if one of args not array
 */
function minder_array_merge()
{
    $output = array();
    if (func_num_args() > 0) {
        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $val) {
                    $output[$key] = $val;
                }
            } else {
                return false;
            }

        }
    }
    return $output;
}

/**
 * These function not in MVC ideology and should be rewrited as Helper
 * Using for return result even if no condition specified for field
 *
 * @todo: These function not in MVC ideology and should be rewrited as Helper
 *
 * @param string $name       key of condition value
 * @param array  $conditions array of conditions
 */
function selectFilterValue($name, $conditions)
{   
    if (array_key_exists($name, $conditions) || in_array($name, $conditions)) {
        return $conditions[$name];
    }
    return '';
}

/**
 * @param  string $propertyName
 * @return mixed|string
 */
function transformToObjectProp($propertyName){
    
    $propName   =   explode('_', strtolower($propertyName));
    $validName  =   array_shift($propName);
    
    for($i=0; $i < count($propName); $i++){
        $validName .=   ucwords($propName[$i]);        
    }
    
    return $validName;
}

function buildEmptyRow($rowsCollection, $emptyValue = '') {
    $sampleRow = current($rowsCollection);
    if (!is_array($sampleRow) || empty($sampleRow))
        return array();

    return array_fill_keys(array_keys($sampleRow), $emptyValue);
}