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

$slide     = $DB->get_record('abook_slide', array('id'=>$slideid, 'abookid'=>$abook->id), '*', MUST_EXIST);
$oldslides = $DB->get_records('abook_slide', array('abookid'=>$abook->id), 'pagenum', 'id, pagenum');

$nothing = 0;

$slides = array();
$sls = 0;
$sle = 0;
$ts = 0;
$te = 0;
// create new ordered array and find slides to be moved
$i = 1;
foreach ($oldslides as $sl) {
    $slides[$i] = $sl;
    if ($slide->id == $sl->id) {
        $sls = $i;
    }
    $i++;
}

// Find target slide(s).
if ($up) {
    if ($sls == 1) {
        $nothing = 1; // Already first.
    } else {
        $ts = $sls - 1;
        $te = $ts;
    }
} else { // Down.
    if ($sls == count($slides)) {
        $nothing = 1; // Already last.
    } else {
        $ts = $sls + 1;
        $te = $ts;
    }
}

// Recreated newly sorted list of slides.
if (!$nothing) {
    $newslides = array();

    if ($up) {
        if ($ts > 1) {
            for ($i=1; $i<$ts; $i++) {
                $newslides[] = $slides[$i];
            }
        }
        for ($i=$sls; $i<=$sle; $i++) {
            $newslides[$i] = $slides[$i];
        }
        for ($i=$ts; $i<=$te; $i++) {
            $newslides[$i] = $slides[$i];
        }
        if ($sle<count($slides)) {
            for ($i=$sle; $i<=count($slides); $i++) {
                $newslides[$i] = $slides[$i];
            }
        }
    } else {
        if ($sls > 1) {
            for ($i=1; $i<$sls; $i++) {
                $newslides[] = $slides[$i];
            }
        }
        for ($i=$ts; $i<=$te; $i++) {
            $newslides[$i] = $slides[$i];
        }
        for ($i=$sls; $i<=$sle; $i++) {
            $newslides[$i] = $slides[$i];
        }
        if ($te<count($slides)) {
            for ($i=$te; $i<=count($slides); $i++) {
                $newslides[$i] = $slides[$i];
            }
        }
    }

    // Store slides in the new order.
    $i = 1;
    foreach ($newslides as $sl) {
        $sl->pagenum = $i;
        $DB->update_record('abook_slide', $sl);
        $sl = $DB->get_record('abook_slide', array('id' => $sl->id));

        \mod_abook\event\slide_updated::create_from_slide($abook, $context, $sl)->trigger();

        $i++;
    }
}

abook_preload_slides($abook); // fix structure
$DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));

redirect('view.php?id='.$cm->id.'&slideid='.$slide->id);

