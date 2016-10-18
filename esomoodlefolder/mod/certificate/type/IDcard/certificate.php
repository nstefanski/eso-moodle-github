<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * letter_non_embedded certificate type
 *
 * @package    mod
 * @subpackage certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

$pdf = new PDF($certificate->orientation, 'pt', 'Letter', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 28;
    $y = 125;
    $sealx = 590;
    $sealy = 425;
    $sigx = 130;
    $sigy = 440;
    $custx = 133;
    $custy = 440;
    $wmarkx = 100;
    $wmarky = 90;
    $wmarkw = 600;
    $wmarkh = 420;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 792;
    $brdrh = 612;
    $codey = 505;
} else { // Portrait
    $x = 28;
    $y = 40;
    $sealx = 440;
    $sealy = 590;
    $sigx = 85;
    $sigy = 580;
    $custx = 88;
    $custy = 580;
    $wmarkx = 78;
    $wmarky = 130;
    $wmarkw = 450;
    $wmarkh = 480;
    $brdrx = 0;
    $brdry = 10;
    $brdrw = 295;
    $brdrh = 190;
    $codey = 660;
	$upx = 100;
	$upy = 100;
	$upw = 100;
	$uph = 100;
}

//User picture
$picx = 8;  // Picture horizontal position.
$picy = 36;  // Picture vertical position.
$picw = 140; // Picture width.
$pich = 140; // Picture height.
$context = context_user::instance($USER->id, IGNORE_MISSING);
if ($context) {
	global $DB, $CFG;
    $fs = get_file_storage();
     
	$select = "contextid = ? AND filesize > 0 AND component = 'user' AND filearea = 'icon' AND filename LIKE 'f3%'";
	$filename = $DB->get_record_select('files', $select, array($context->id));
	
	if ($filename) {
		// Prepare file record object
		$fileinfo = array(
			'component' => 'user', 
			'filearea' => 'icon', 
			'itemid' => 0, 
			'contextid' => $context->id, // ID of context
			'filepath' => '/',
			'filename' => $filename->filename);
		// Get file.
		$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
							  $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
		 
		// Print image.
		if ($file) {
			$contenthash = $file->get_contenthash();
			$l1 = $contenthash[0] . $contenthash[1];
			$l2 = $contenthash[2] . $contenthash[3];
			$location = $CFG->dataroot . '/filedir' . '/' . $l1 . '/' . $l2 . '/' . $contenthash;
			$pdf->Image($location, $picx, $picy, $picw, $pich);
		}
    }
}
// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame_letter($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 0);

$namelen = strlen(fullname($USER));
if ($namelen <= 20) {
	$fontsize = 22;
} elseif ($namelen <= 30) {
	$fontsize = 20;
} elseif ($namelen <= 40) {
	$fontsize = 18;
} else {
	$fontsize = 16;
}
//certificate_print_text($pdf, $x + 124, $y + 10, 'L', 'Times', '', 14, fullname($USER));
	$pdf->setFont('Times', '', $fontsize);
    $pdf->SetXY($x + 126, $y + 10);
    $pdf->writeHTMLCell(132, 0, '', '', fullname($USER), 0, 0, 0, true, 'C');
//certificate_print_text($pdf, $x + 124, $y + 100, 'L', 'Times', '', 14, 'Student');
	$pdf->setFont('Times', '', 10);
    $pdf->SetXY($x + 126, $y + 82);
    $pdf->writeHTMLCell(132, 0, '', '', 'Student #: ' . $USER->idnumber, 0, 0, 0, true, 'C');
if ($USER->profile['startdate'] > 0) {
	$pdf->setFont('Times', '', 10);
    $pdf->SetXY($x + 126, $y + 95);
    $pdf->writeHTMLCell(132, 0, '', '', 'Started ' . date('M jS, Y', $USER->profile['startdate']), 0, 0, 0, true, 'C');
}

if ($certificate->printhours) {
    certificate_print_text($pdf, $x, $y + 211, 'C', 'Times', '', 16, 'and awards ' . $certificate->printhours . ' Continuing Education Hours');
}
//certificate_print_text($pdf, $x, $y + 250, 'C', 'Times', '', 16, 'Given on '.$path);
//certificate_print_text($pdf, $x, $y + 155, 'C', 'Helvetica', '', 20, get_string('statement', 'certificate'));
//certificate_print_text($pdf, $x, $y + 205, 'C', 'Helvetica', '', 20, $course->fullname);
//certificate_print_text($pdf, $x, $y + 255, 'C', 'Helvetica', '', 14, certificate_get_date($certificate, $certrecord, $course));
//certificate_print_text($pdf, $x, $y + 283, 'C', 'Times', '', 10, certificate_get_grade($certificate, $course));
//certificate_print_text($pdf, $x, $y + 311, 'C', 'Times', '', 10, certificate_get_outcome($certificate, $course));
//if ($certificate->printhours) {
//    certificate_print_text($pdf, $x, $y + 339, 'C', 'Times', '', 10, get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
//}
certificate_print_text($pdf, $x, $codey, 'C', 'Times', '', 10, certificate_get_code($certificate, $certrecord));
$i = 0;
if ($certificate->printteacher) {
    $context = context_module::instance($cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            certificate_print_text($pdf, $sigx, $sigy + ($i * 12), 'L', 'Times', '', 12, fullname($teacher));
        }
    }
}

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);
?>