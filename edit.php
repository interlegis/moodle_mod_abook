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
 * Edit abook slide
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit_form.php');

$cmid     = required_param('cmid', PARAM_INT);  // Book Course Module ID
$slideid  = optional_param('id', 0, PARAM_INT); // Slide ID
$pagenum  = optional_param('pagenum', 0, PARAM_INT);

$cm = get_coursemodule_from_id('abook', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/abook:edit', $context);

$PAGE->set_url('/mod/abook/edit.php', array('cmid'=>$cmid, 'id'=>$slideid, 'pagenum'=>$pagenum));
$PAGE->set_pagelayout('admin'); // TODO: Something. This is a bloody hack!

if ($slideid) {
    $slide = $DB->get_record('abook_slide', array('id'=>$slideid, 'abookid'=>$abook->id), '*', MUST_EXIST);
} else {
    $slide = new stdClass();
    $slide->id         = null;
    $slide->pagenum    = $pagenum + 1;
}
$slide->cmid = $cm->id;

$editoroptions = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$pixoptions = array('maxfiles'=>1, 'subdirs'=>0, 'accepted_types'=>array('.jpg','.gif','.png'));

$mform = prepare_slide_form($slide, $context, $editoroptions, $pixoptions);

// If data submitted, then process and store.
if ($mform->is_cancelled()) {
    if (empty($slide->id)) {
        redirect("view.php?id=$cm->id");
    } else {
        redirect("view.php?id=$cm->id&slideid=$slide->id");
    }
} else {
	if (process_slide_form($mform, $slide, $abook, $context, $editoroptions, $pixoptions)) {
		redirect("view.php?id=$cm->id&slideid=$data->id");
	}
}

// Otherwise fill and print the form.
$PAGE->set_title($abook->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($abook->name);

$mform->display();

echo $OUTPUT->footer();