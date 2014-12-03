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
 * Animated book module language strings
 *
 * @package    mod_abook
 * @copyright  2004-2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['modulename'] = 'Animated book';
$string['modulename_help'] = 'The animated book module enables a teacher to create a multi-page resource in a slide-like format, with slides. Animated books can contain media files as well as text and are useful for displaying lengthy passages of information which can be broken down into sections.

A animated book may be used

* To display reading material for individual modules of study
* As a staff departmental handbook
* As a showcase portfolio of student work';
$string['modulename_link'] = 'mod/abook/view';
$string['modulenameplural'] = 'Animated books';
$string['pluginname'] = 'Animated book';
$string['pluginadministration'] = 'Animated book administration';

$string['toc'] = 'Table of contents';
$string['slides'] = 'Slides';
$string['slidetitle'] = 'Slide title';
$string['content'] = 'Content';
$string['editingslide'] = 'Editing slide';
$string['eventslidecreated'] = 'Slide created';
$string['eventslidedeleted'] = 'Slide deleted';
$string['eventslideupdated'] = 'Slide updated';
$string['eventslideviewed'] = 'Slide viewed';
$string['nocontent'] = 'No content has been added to this animated book yet.';
$string['addafter'] = 'Add new slide';
$string['confslidedelete'] = 'Do you really want to delete this slide?';
$string['top'] = 'top';
$string['navprev'] = 'Previous';
$string['navnext'] = 'Next';
$string['navexit'] = 'Exit animated book';
$string['abook:addinstance'] = 'Add a new animated book';
$string['abook:read'] = 'Read animated book';
$string['abook:edit'] = 'Edit animated book slides';
$string['abook:viewhiddenslides'] = 'View hidden animated book slides';
$string['errorslide'] = 'Error reading slide of animated book.';

$string['page-mod-abook-x'] = 'Any animated book module page';
$string['subplugintype_abooktool'] = 'Animated book tool';
$string['subplugintype_abooktool_plural'] = 'Animated book tools';

// --------------------
$_height_help = '<br/>Any valid <a href="http://www.w3.org/TR/css3-values/#lengths">CSS distance</a>. Examples: 600px 20em 30cm 20in';
// --------------------

$string['slidetype'] = 'Slide type';
$string['frameheight'] = 'Frame height';
$string['frameheight_help'] = 'The total slide height.'.$_height_help;
$string['wallpaper'] = 'Wallpaper';
$string['wallpaper_help'] = 'The background image for this slide';
$string['contentanimation'] = 'Content animation';
$string['boardpix'] = 'Blackboard image';
$string['boardpix_help'] = 'Background image for content box';
$string['boardheight'] = 'Board height';
$string['boardheight_help'] = 'The total height of content box'.$_height_help;
$string['content1'] = '2nd content';
$string['contentanimation1'] = '2nd content animation';
$string['boardpix1'] = '2nd blackboard image';
$string['boardpix1_help'] = 'Background image for 2nd content box';
$string['boardheight1'] = '2nd board height';
$string['boardheight1_help'] = 'The total height of 2nd content box'.$_height_help;
$string['content2'] = '3rd content';
$string['contentanimation2'] = '3rd content animation';
$string['boardpix2'] = '3rd blackboard image';
$string['boardpix2_help'] = 'Background image for 3rd content box';
$string['boardheight2'] = '3rd board height';
$string['boardheight2_help'] = 'The total height of 3rd content box'.$_height_help;
$string['content3'] = '4th content';
$string['contentanimation3'] = '4th content animation';
$string['boardpix3'] = '4th blackboard image';
$string['boardpix3_help'] = 'Background image for 4th content box';
$string['boardheight3'] = '4th board height';
$string['boardheight3_help'] = 'The total height of 4th content box'.$_height_help;
$string['footerpix'] = 'Footer image';
$string['footerpix_help'] = 'The picture that compound the slide floor';
$string['footerpos'] = 'Footer position';
$string['footeranimation'] = 'Footer animation';
$string['teacherpix'] = 'Teacher image';
$string['teacherpix_help'] = 'The picture that represents the teacher character';
$string['teacherpos'] = 'Teacher position';
$string['teacheranimation'] = 'teacher animation';
