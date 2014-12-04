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
 * Define all the backup steps that will be used by the backup_abook_activity_task
 *
 * @package    mod_abook
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to backup one abook activity
 */
class backup_abook_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated
        $abook     = new backup_nested_element('abook', array('id'), array('name', 'intro', 'introformat', 'numbering', 'customtitles', 'timecreated', 'timemodified'));
        $slides = new backup_nested_element('slides');
        $slide  = new backup_nested_element('slide', array('id'), array('pagenum', 'subchapter', 'title', 'content', 'contentformat', 'hidden', 'timemcreated', 'timemodified', 'importsrc',));

        $abook->add_child($slides);
        $slides->add_child($slide);

        // Define sources
        $abook->set_source_table('abook', array('id' => backup::VAR_ACTIVITYID));
        $slide->set_source_table('abook_slide', array('abookid' => backup::VAR_PARENTID));

        // Define file annotations
        $abook->annotate_files('mod_abook', 'intro', null); // This file area hasn't itemid
        $slide->annotate_files('mod_abook', 'slide', 'id');

        // Return the root element (abook), wrapped into standard activity structure
        return $this->prepare_activity_structure($abook);
    }
}
