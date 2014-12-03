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
 * Animated book printing
 *
 * @package    abooktool_print
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id        = required_param('id', PARAM_INT);           // Course Module ID
$slideid = optional_param('slideid', 0, PARAM_INT); // slide ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================

$cm = get_coursemodule_from_id('abook', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$abook = $DB->get_record('abook', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/abook:read', $context);
require_capability('abooktool/print:print', $context);

// Check all variables.
if ($slideid) {
    // Single slide printing - only visible!
    $slide = $DB->get_record('abook_slide', array('id'=>$slideid, 'abookid'=>$abook->id), '*', MUST_EXIST);
} else {
    // Complete abook.
    $slide = false;
}

$PAGE->set_url('/mod/abook/print.php', array('id'=>$id, 'slideid'=>$slideid));

unset($id);
unset($slideid);

// Security checks END.

// read slides
$slides = abook_preload_slides($abook);

$strabooks = get_string('modulenameplural', 'mod_abook');
$strabook  = get_string('modulename', 'mod_abook');
$strtop   = get_string('top', 'mod_abook');

@header('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
@header('Pragma: no-cache');
@header('Expires: ');
@header('Accept-Ranges: none');
@header('Content-type: text/html; charset=utf-8');

if ($slide) {

    if ($slide->hidden) {
        require_capability('mod/abook:viewhiddenslides', $context);
    }
    \abooktool_print\event\slide_printed::create_from_slide($abook, $context, $slide)->trigger();

    // page header
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
      <title><?php echo format_string($abook->name, true, array('context'=>$context)) ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="description" content="<?php echo s(format_string($abook->name, true, array('context'=>$context))) ?>" />
      <link rel="stylesheet" type="text/css" href="print.css" />
    </head>
    <body>
    <?php
    // Print dialog link.
    $printtext = get_string('printslide', 'abooktool_print');
    $printicon = $OUTPUT->pix_icon('slide', $printtext, 'abooktool_print', array('class' => 'abook_print_icon'));
    $printlinkatt = array('onclick' => 'window.print();return false;', 'class' => 'abook_no_print');
    echo html_writer::link('#', $printicon.$printtext, $printlinkatt);
    ?>
    <a name="top"></a>
    <?php
    echo $OUTPUT->heading(format_string($abook->name, true, array('context'=>$context)), 1);
    ?>
    <div class="slide">
    <?php


    $currtitle = abook_get_slide_title($slide->id, $slides, $abook, $context);
    echo $OUTPUT->heading($currtitle);

    $slidetext = file_rewrite_pluginfile_urls($slide->content, 'pluginfile.php', $context->id, 'mod_abook', 'slide', $slide->id);
    echo format_text($slidetext, $slide->contentformat, array('noclean'=>true, 'context'=>$context));
    echo '</div>';
    echo '</body> </html>';

} else {
    \abooktool_print\event\abook_printed::create_from_abook($abook, $context)->trigger();

    $allslides = $DB->get_records('abook_slide', array('abookid'=>$abook->id), 'pagenum');
    $abook->intro = file_rewrite_pluginfile_urls($abook->intro, 'pluginfile.php', $context->id, 'mod_abook', 'intro', null);

    // page header
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
      <title><?php echo format_string($abook->name, true, array('context'=>$context)) ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="description" content="<?php echo s(format_string($abook->name, true, array('noclean'=>true, 'context'=>$context))) ?>" />
      <link rel="stylesheet" type="text/css" href="print.css" />
    </head>
    <body>
    <?php
    // Print dialog link.
    $printtext = get_string('printabook', 'abooktool_print');
    $printicon = $OUTPUT->pix_icon('abook', $printtext, 'abooktool_print', array('class' => 'abook_print_icon'));
    $printlinkatt = array('onclick' => 'window.print();return false;', 'class' => 'abook_no_print');
    echo html_writer::link('#', $printicon.$printtext, $printlinkatt);
    ?>
    <a name="top"></a>
    <?php
    echo $OUTPUT->heading(format_string($abook->name, true, array('context'=>$context)), 1);
    ?>
    <p class="abook_summary"><?php echo format_text($abook->intro, $abook->introformat, array('noclean'=>true, 'context'=>$context)) ?></p>
    <div class="abook_info"><table>
    <tr>
    <td><?php echo get_string('site') ?>:</td>
    <td><a href="<?php echo $CFG->wwwroot ?>"><?php echo format_string($SITE->fullname, true, array('context'=>$context)) ?></a></td>
    </tr><tr>
    <td><?php echo get_string('course') ?>:</td>
    <td><?php echo format_string($course->fullname, true, array('context'=>$context)) ?></td>
    </tr><tr>
    <td><?php echo get_string('modulename', 'mod_abook') ?>:</td>
    <td><?php echo format_string($abook->name, true, array('context'=>$context)) ?></td>
    </tr><tr>
    <td><?php echo get_string('printedby', 'abooktool_print') ?>:</td>
    <td><?php echo fullname($USER, true) ?></td>
    </tr><tr>
    <td><?php echo get_string('printdate', 'abooktool_print') ?>:</td>
    <td><?php echo userdate(time()) ?></td>
    </tr>
    </table></div>

    <?php
    list($toc, $titles) = abooktool_print_get_tocslideers, $abook, $cm);
    echo $toc;
    // slides
    $link1 = $CFG->wwwroot.'/mod/abook/view.php?id='.$course->id.'&slideid=';
    $link2 = $CFG->wwwroot.'/mod/abook/view.php?id='.$course->id;
    foreach ($slides as $sl) {
        $slide = $allslides[$sl->id];
        if ($slide->hidden) {
            continue;
        }
        echo '<div class="abook_slide"><a name="ch'.$sl->id.'"></a>';
        echo $OUTPUT->heading($titles[$sl->id]);
        $content = str_replace($link1, '#ch', $slide->content);
        $content = str_replace($link2, '#top', $content);
        $content = file_rewrite_pluginfile_urls($content, 'pluginfile.php', $context->id, 'mod_abook', 'slide', $sl->id);
        echo format_text($content, $slide->contentformat, array('noclean'=>true, 'context'=>$context));
        echo '</div>';
        // echo '<a href="#toc">'.$strtop.'</a>';
    }
    echo '</body> </html>';
}

