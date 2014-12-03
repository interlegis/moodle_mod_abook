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
 * Delete abook slide
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id        = required_param('id', PARAM_INT);        // Course Module ID
$slideid = required_param('slideid', PARAM_INT); // Chapter ID
$confirm   = optional_param('confirm', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('abook', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('mod/abook:edit', $context);

$PAGE->set_url('/mod/abook/delete.php', array('id'=>$id, 'slideid'=>$slideid));

$slide = $DB->get_record('abook_slide', array('id'=>$slideid, 'abookid'=>$abook->id), '*', MUST_EXIST);


// Header and strings.
$PAGE->set_title($abook->name);
$PAGE->set_heading($course->fullname);

// Form processing.
if ($confirm) {  // the operation was confirmed.
    $fs = get_file_storage();
    foreach(abook_get_file_areas() as $filearea=>$fileareaname) {
    	$fs->delete_area_files($context->id, 'mod_abook', $filearea, $slide->id);
    }
    $DB->delete_records('abook_slide', array('id'=>$slide->id));

    \mod_abook\event\slide_deleted::create_from_slide($abook, $context, $slide)->trigger();

    abook_preload_slides($abook); // Fix structure.
    $DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));

    redirect('view.php?id='.$cm->id);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($abook->name);

// The operation has not been confirmed yet so ask the user to do so.
$strconfirm = get_string('confslidedelete', 'mod_abook');
echo '<br />';
$continue = new moodle_url('/mod/abook/delete.php', array('id'=>$cm->id, 'slideid'=>$slide->id, 'confirm'=>1));
$cancel = new moodle_url('/mod/abook/view.php', array('id'=>$cm->id, 'slideid'=>$slide->id));
echo $OUTPUT->confirm("<strong>$slide->title</strong><p>$strconfirm</p>", $continue, $cancel);

echo $OUTPUT->footer();
