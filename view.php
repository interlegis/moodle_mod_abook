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
 * Book view page
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id        = optional_param('id', 0, PARAM_INT);        // Course Module ID
$bid       = optional_param('b', 0, PARAM_INT);         // Book id
$slideid   = optional_param('slideid', 0, PARAM_INT);   // Chapter ID
$edit      = optional_param('edit', -1, PARAM_BOOL);    // Edit mode
$json      = optional_param('json', 0, PARAM_INT);      // Response as json

// =========================================================================
// security checks START - teachers edit; students view
// =========================================================================
if ($id) {
    $cm = get_coursemodule_from_id('abook', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);
} else {
    $abook = $DB->get_record('abook', array('id'=>$bid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('abook', $abook->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $id = $cm->id;
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/abook:read', $context);

$allowedit  = has_capability('mod/abook:edit', $context);
$viewhidden = has_capability('mod/abook:viewhiddenslides', $context);

if ($allowedit) {
    if ($edit != -1 and confirm_sesskey()) {
        $USER->editing = $edit;
    } else {
        if (isset($USER->editing)) {
            $edit = $USER->editing;
        } else {
            $edit = 0;
        }
    }
} else {
    $edit = 0;
}

// read slides
$slides = abook_preload_slides($abook);

if ($allowedit and !$slides) {
	#TODO: Create a new slide on the fly
    redirect('edit.php?cmid='.$cm->id); // No slides - add new one.
}
// Check slideid and read slide data
if ($slideid == '0') { // Go to first slide if no given.
    \mod_abook\event\course_module_viewed::create_from_abook($abook, $context)->trigger();

    foreach ($slides as $sl) {
        if ($edit) {
            $slideid = $sl->id;
            break;
        }
        if (!$sl->hidden) {
            $slideid = $sl->id;
            break;
        }
    }
}

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

// No content in the abook.
if (!$slideid) {
    $PAGE->set_url('/mod/abook/view.php', array('id' => $id));
    notice(get_string('nocontent', 'mod_abook'), $courseurl->out(false));
}
// Slide doesnt exist or it is hidden for students
if ((!$slide = $DB->get_record('abook_slide', array('id' => $slideid, 'abookid' => $abook->id))) or ($slide->hidden and !$viewhidden)) {
    print_error('errorslide', 'mod_abook', $courseurl);
}

$PAGE->requires->jquery();

$PAGE->set_url('/mod/abook/view.php', array('id'=>$id, 'slideid'=>$slideid));

// Unset all page parameters.
unset($id);
unset($bid);
unset($slideid);

// Security checks END.

\mod_abook\event\slide_viewed::create_from_slide($abook, $context, $slide)->trigger();

// Read standard strings.
$strabooks = get_string('modulenameplural', 'mod_abook');
$strabook  = get_string('modulename', 'mod_abook');
$strtoc   = get_string('toc', 'mod_abook');

// prepare header
$pagetitle = $abook->name . ": " . $slide->title;
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

abook_add_fake_block($slides, $slide, $abook, $cm, $edit);

$fmtoptions = array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context);
$data = slide_to_array($abook, $slide, $context, $fmtoptions, $cm, $edit);

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

if ($json !== 0) {
	// Mount a json response
	header('Content-Type: application/json');
	echo json_encode($data);
	return;
}

// Output HTML page
$PAGE->requires->yui_module('moodle-mod_abook-animate', 'M.mod_abook.animate.init', array($data['slidetype'], $edit, $data));

echo $OUTPUT->header();
echo $OUTPUT->heading($abook->name);
echo "<div id='slidepanel' class='panel panel-default abframe'>";
echo $html;
echo "</div>";
if ($edit) {
	$slide->cmid = $cm->id;
	$editoroptions = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
	$pixoptions = array('maxfiles'=>1, 'subdirs'=>0, 'accepted_types'=>array('.jpg','.gif','.png'));
	
	$mform = prepare_slide_form($slide, $context, $editoroptions, $pixoptions, "{$CFG->wwwroow}/mod/abook/ajax_edit.php");
	echo '<div id="formpanel" class="panel panel-default" style="display: none;">';
	echo '<div class="panel-body">';
	echo $mform->display();
	echo '</div></div>';
}
echo $OUTPUT->footer();