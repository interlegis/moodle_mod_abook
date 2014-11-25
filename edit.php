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

$cmid       = required_param('cmid', PARAM_INT);  // Book Course Module ID
$slideid  = optional_param('id', 0, PARAM_INT); // Chapter ID
$pagenum    = optional_param('pagenum', 0, PARAM_INT);

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

$options = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$slide = file_prepare_standard_editor($slide, 'content' , $options, $context, 'mod_abook', 'content' , $slide->id);
$slide = file_prepare_standard_editor($slide, 'content1', $options, $context, 'mod_abook', 'content1', $slide->id);
$slide = file_prepare_standard_editor($slide, 'content2', $options, $context, 'mod_abook', 'content2', $slide->id);
$slide = file_prepare_standard_editor($slide, 'content3', $options, $context, 'mod_abook', 'content3', $slide->id);

$pixoptions = array('maxfiles'=>1, 'subdirs'=>0, 'accepted_types'=>array('.jpg','.gif','.png'));
$slide = file_prepare_standard_filemanager($slide, 'wallpaper' , $pixoptions, $context, 'mod_abook', 'wallpaper' , $slide->id);
$slide = file_prepare_standard_filemanager($slide, 'boardpix'  , $pixoptions, $context, 'mod_abook', 'boardpix'  , $slide->id);
$slide = file_prepare_standard_filemanager($slide, 'footerpix' , $pixoptions, $context, 'mod_abook', 'footerpix' , $slide->id);
$slide = file_prepare_standard_filemanager($slide, 'teacherpix', $pixoptions, $context, 'mod_abook', 'teacherpix', $slide->id);

$mform = new abook_slide_edit_form(null, array('slide'=>$slide, 'options'=>$options, 'pixoptions'=>$pixoptions));

// If data submitted, then process and store.
if ($mform->is_cancelled()) {
    if (empty($slide->id)) {
        redirect("view.php?id=$cm->id");
    } else {
        redirect("view.php?id=$cm->id&slideid=$slide->id");
    }

} else if ($data = $mform->get_data()) {

    if (!$data->id) {
        // adding new slide
        $data->abookid        = $abook->id;
        $data->hidden        = 0;
        $data->timecreated   = time();
        $data->timemodified  = time();
        $data->importsrc     = '';
        $data->content       = '';          // updated later
        $data->content1      = '';          // updated later
        $data->content2      = '';          // updated later
        $data->content3      = '';          // updated later
        $data->contentformat = FORMAT_HTML; // updated later

        // make room for new page
        $sql = "UPDATE {abook_slide}
                   SET pagenum = pagenum + 1
                 WHERE abookid = ? AND pagenum >= ?";
        $DB->execute($sql, array($abook->id, $data->pagenum));

        $data->id = $DB->insert_record('abook_slide', $data);
    }

    // store the files
    $data->timemodified = time();
    $data = file_postupdate_standard_editor($data, 'content' , $options, $context, 'mod_abook', 'content' , $data->id);
    $data = file_postupdate_standard_editor($data, 'content1', $options, $context, 'mod_abook', 'content1', $data->id);
    $data = file_postupdate_standard_editor($data, 'content2', $options, $context, 'mod_abook', 'content2', $data->id);
    $data = file_postupdate_standard_editor($data, 'content3', $options, $context, 'mod_abook', 'content3', $data->id);
    $data = file_postupdate_standard_filemanager($data, 'wallpaper' , $pixoptions, $context, 'mod_abook', 'wallpaper' , $data->id);
    $data = file_postupdate_standard_filemanager($data, 'boardpix'  , $pixoptions, $context, 'mod_abook', 'boardpix'  , $data->id);
    $data = file_postupdate_standard_filemanager($data, 'footerpix' , $pixoptions, $context, 'mod_abook', 'footerpix' , $data->id);
    $data = file_postupdate_standard_filemanager($data, 'teacherpix', $pixoptions, $context, 'mod_abook', 'teacherpix', $data->id);
    $DB->update_record('abook_slide', $data);
    $DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));
    $slide = $DB->get_record('abook_slide', array('id' => $data->id));
    
    \mod_abook\event\slide_updated::create_from_slide($abook, $context, $slide)->trigger();
    
    abook_preload_slides($abook); // fix structure
    redirect("view.php?id=$cm->id&slideid=$data->id");
}

// Otherwise fill and print the form.
$PAGE->set_title($abook->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($abook->name);

$mform->display();

echo $OUTPUT->footer();
