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
 * Define all the restore steps that will be used by the restore_abook_activity_task
 *
 * @package    mod_abook
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one abook activity
 */
class restore_abook_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('abook', '/activity/abook');
        $paths[] = new restore_path_element('abook_slide', '/activity/abook/slides/slide');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process abook tag information
     * @param array $data information
     */
    protected function process_abook($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('abook', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process slide tag information
     * @param array $data information
     */
    protected function process_abook_slide($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->abookid = $this->get_new_parentid('abook');

        $newitemid = $DB->insert_record('abook_slides', $data);
        $this->set_mapping('abook_slide', $oldid, $newitemid, true);
    }

    protected function after_execute() {
        global $DB;

        // Add abook related files
        $this->add_related_files('mod_abook', 'intro', null);
        $this->add_related_files('mod_abook', 'slide', 'abook_slide');
    }
}
