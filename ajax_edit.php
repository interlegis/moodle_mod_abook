<?php
// This file is part of Moodle - http://moodle.org/
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
 * Ajax Edit abook slide
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit_form.php');

$cmid     = required_param('cmid', PARAM_INT);  // Book Course Module ID
$slideid  = required_param('id', PARAM_INT); // Slide ID
$pagenum  = required_param('pagenum', PARAM_INT);

$cm = get_coursemodule_from_id('abook', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/abook:edit', $context);

$slide = $DB->get_record('abook_slide', array('id'=>$slideid, 'abookid'=>$abook->id), '*', MUST_EXIST);
$slide->cmid = $cm->id;

$editoroptions = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$pixoptions = array('maxfiles'=>1, 'subdirs'=>0, 'accepted_types'=>array('.jpg','.gif','.png'));

$mform = prepare_slide_form($slide, $context, $editoroptions, $pixoptions);

// If data submitted, then process and store.
if ($mform->is_cancelled()) {
	echo "";
	return;
} else {
	if (process_slide_form($mform, $slide, $abook, $context, $editoroptions, $pixoptions)) {
		$data = slide_to_array($abook, $slide, $context, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context), $cm, 1);
		// =====================================================
		// Animated book capture HTML fragment code
		// =====================================================
		ob_start();
		require_once("./type/{$data['slidetype']}/render.php");
		$html = ob_get_contents();
		ob_end_clean();
		// =====================================================
		// Animated book end of display HTML code
		// =====================================================
		$data['html'] = $html;
		header('Content-Type: application/json');
		echo json_encode($data);
		return;
	}
}

// Otherwise fill and print the form.
header('Content-Type: text/html');
$mform->display();