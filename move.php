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
 * Move abook slide
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id      = required_param('id', PARAM_INT);      // Course Module ID
$slideid = required_param('slideid', PARAM_INT); // Slide ID
$up      = optional_param('up', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('abook', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('mod/abook:edit', $context);

abook_preload_slides($abook); // fix structure

$slide = $DB->get_record('abook_slide', array('abookid'=>$abook->id, 'id'=>$slideid));

if ($up) {
	$swapslide = $DB->get_record('abook_slide', array('abookid'=>$abook->id, 'pagenum'=>$slide->pagenum - 1));
} else {
	$swapslide = $DB->get_record('abook_slide', array('abookid'=>$abook->id, 'pagenum'=>$slide->pagenum + 1));
}
if ($swapslide) {
	$buffer = $swapslide->pagenum;
	$swapslide->pagenum = $slide->pagenum;
	$slide->pagenum = $buffer;
	$DB->update_record('abook_slide', $slide);
	$DB->update_record('abook_slide', $swapslide);
}

abook_preload_slides($abook); // fix structure again
$DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));

redirect('view.php?id='.$cm->id.'&slideid='.$slide->id);

