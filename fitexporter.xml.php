<?php
/**
 * Pos-Tracker2
 *
 * Starbase Module XML export page
 *
 * PHP version 5
 *
 * LICENSE: This file is part of POS-Tracker2.
 * POS-Tracker2 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * POS-Tracker2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with POS-Tracker2.  If not, see <http://www.gnu.org/licenses/>.
 *

 * @author     Stephen Gulickk <stephenmg12@gmail.com>
 * @author     DeTox MinRohim <eve@onewayweb.com>
 * @author      Andy Snowden <forumadmin@eve-razor.com>
 * @copyright  2007-2009 (C)  Stephen Gulick, DeTox MinRohim, and Andy Snowden
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package    POS-Tracker2
 * @version    SVN: $Id$
 * @link       https://sourceforge.net/projects/pos-tracker2/
 * @link       http://www.eve-online.com/
 */

include_once 'eveconfig/config.php';
include_once 'includes/dbfunctions.php';

EveDBInit();

include_once 'includes/eveclass.php';
include_once 'includes/class.pos.php';
include_once 'includes/eveRender.class.php';
include_once 'eveconfig/config.php';
$eveRender = New eveRender($config, $mod, false);
$colors    = $eveRender->themeconfig;
//echo '<pre>';print_r($_SESSION); echo '</pre>';exit;
$eve     = New Eve();
$posmgmt = New POSMGMT();
$eve->SessionSetVar('userlogged', 1);

$userinfo = $posmgmt->GetUserInfo();

$access = $eve->SessionGetVar('access');

$eveRender->Assign('access', $access);
$eveRender->Assign('config', $config);

if (empty($pos_id)) {
    $pos_id = $eve->VarCleanFromInput('pos_id');
}

$xmlstyle = $eve->VarCleanFromInput('xmlstyle');

$mods = $posmgmt->GetAllPosMods($pos_id);

if ($mods) { //if (mysql_num_rows($result) != 0) {
    //Set Header to XML
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-Type: text/xml');

    // create a new XML document
    if ($xmlstyle == 'tracker') {
        $doc = new DOMDocument('1.0', 'iso-8859-1');
    }
    if ($xmlstyle == 'mypos') {
        $doc = new DOMDocument('1.0');
        // Creates an instance of the DOMImplementation class
        $imp = new DOMImplementation;

        // Creates a DOMDocumentType instance
        $dtd = $imp->createDocumentType('TowerFittingXML');

        // Creates a DOMDocument instance
        $doc = $imp->createDocument("", "", $dtd);
    }
    //Sets some document properties
    $doc->formatOutput = true;
    if($xmlstyle=='mypos') {
        $doc->standalone = false;
    }

    if ($xmlstyle == 'tracker') {
        // add root node for tracker
        $root = $doc->createElement('pos-tracker');
        $root = $doc->appendChild($root);
        $root->setAttribute('version', '0.1');
        //Add comment for tracker
        $comment = $doc->createComment('POS-Tracker Fitting Export');
        $comment = $root->appendChild($comment);
    }
    if ($xmlstyle == 'mypos') {
        //Add a comment line for MyPOS
        $comment = $doc->createComment('This file contains tower fitting information for My POS generated by POS-Tracker 2.1.0');
        $comment = $doc->appendChild($comment);

        // add root node for MyPOS
        $root = $doc->createElement('Fitting');
        $root = $doc->appendChild($root);
    }
    if ($xmlstyle == 'tracker') {
        // Add Current Time Elelement
        //Get and format time
        $time        = time();
        $currentTime = gmdate("Y-m-d H:i:s", $time);
        //create xml for element
        $cur_time = $doc->createElement(currentTime);
        $cur_time = $root->appendChild($cur_time);
        $value = $doc->createTextNode($currentTime);
        $value = $cur_time->appendChild($value);
    }

    // Add Structures element
    if ($xmlstyle == 'tracker') {
        $structures = $doc->createElement(structures);
    }
    if ($xmlstyle == 'mypos') {
        $structures = $doc->createElement(Structures);
    }
    $structures = $root->appendChild($structures);


    foreach($mods as $mod) {

        $stypeID   = $mod['type_id'];
        $stypeName = $mod['name'];
        $online    = $mod['online'];
        if($xmlstyle == 'tracker') {
            //add structure element and attributes child of structures
            $structure = $doc->createElement(structure);
            $structure = $structures->appendChild($structure);
            $structure->setAttribute('typeID', $stypeID);
            $structure->setAttribute('typeName', $stypeName);
            $structure->setAttribute('online', $online);
        }
        if($xmlstyle == 'mypos') {
            //add ItemID element and attributes child of structures
            $itemID = $doc->createElement(ItemID);
            $itemID = $structures->appendChild($itemID);
            $value  = $doc->createTextNode($stypeID);
            $value  = $itemID->appendChild($value);
        }
    }
    $xml_string = $doc->saveXML();

    header("Content-Disposition: attachment; filename=" . urlencode('POS_'.$pos_id.(($xmlstyle=='mypos') ? '.pft' : '.xml')));
    header("Content-Type: application/force-download");

    echo $xml_string;
} //if
// get completed xml document

?>