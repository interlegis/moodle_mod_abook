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
 * The mod_abook slide viewed event.
 *
 * @package    mod_abook
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_abook\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_abook slide viewed event class.
 *
 * @package    mod_abook
 * @since      Moodle 2.6
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slide_viewed extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param \stdClass $abook
     * @param \context_module $context
     * @param \stdClass $slide
     * @return slide_viewed
     */
    public static function create_from_slide(\stdClass $abook, \context_module $context, \stdClass $slide) {
        $data = array(
            'context' => $context,
            'objectid' => $slide->id,
        );
        /** @var slide_viewed $event */
        $event = self::create($data);
        $event->add_record_snapshot('abook', $abook);
        $event->add_record_snapshot('abook_slide', $slide);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the slide with id '$this->objectid' for the abook with " .
            "course module id '$this->contextinstanceid'.";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'abook', 'view slide', 'view.php?id=' . $this->contextinstanceid .
            '&amp;slideid=' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventslideviewed', 'mod_abook');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/abook/view.php', array('id' => $this->contextinstanceid, 'slideid' => $this->objectid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'abook_slide';
    }
}
