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
 * Book module core interaction API
 *
 * @package    mod_abook
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Returns all other caps used in module
 * @return array
 */
function abook_get_extra_capabilities() {
    // used for group-members-only
    return array('moodle/site:accessallgroups');
}

/**
 * Add abook instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return int new abook instance id
 */
function abook_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    return $DB->insert_record('abook', $data);
}

/**
 * Update abook instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return bool true
 */
function abook_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('abook', $data);

    $abook = $DB->get_record('abook', array('id'=>$data->id));
    $DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));

    return true;
}

/**
 * Delete abook instance by activity id
 *
 * @param int $id
 * @return bool success
 */
function abook_delete_instance($id) {
    global $DB;

    if (!$abook = $DB->get_record('abook', array('id'=>$id))) {
        return false;
    }

    $DB->delete_records('abook_slide', array('abookid'=>$abook->id));
    $DB->delete_records('abook', array('id'=>$abook->id));

    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in abook activities and print it out.
 *
 * @param stdClass $course
 * @param bool $viewfullnames
 * @param int $timestart
 * @return bool true if there was output, or false is there was none
 */
function abook_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function abook_reset_userdata($data) {
    return array();
}

/**
 * No cron in abook.
 *
 * @return bool
 */
function abook_cron () {
    return true;
}

/**
 * No grading in abook.
 *
 * @param int $abookid
 * @return null
 */
function abook_grades($abookid) {
    return null;
}

/**
 * This function returns if a scale is being used by one abook
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See abook, glossary or journal modules
 * as reference.
 *
 * @param int $abookid
 * @param int $scaleid
 * @return boolean True if the scale is used by any journal
 */
function abook_scale_used($abookid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of abook
 *
 * This is used to find out if scale used anywhere
 *
 * @param int $scaleid
 * @return bool true if the scale is used by any abook
 */
function abook_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Return read actions.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function abook_get_view_actions() {
    global $CFG; // necessary for includes

    $return = array('view', 'view all');

    $plugins = core_component::get_plugin_list('abooktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'abooktool_'.$plugin.'_get_view_actions';
        if (function_exists($function)) {
            if ($actions = $function()) {
                $return = array_merge($return, $actions);
            }
        }
    }

    return $return;
}

/**
 * Return write actions.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function abook_get_post_actions() {
    global $CFG; // necessary for includes

    $return = array('update');

    $plugins = core_component::get_plugin_list('abooktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'abooktool_'.$plugin.'_get_post_actions';
        if (function_exists($function)) {
            if ($actions = $function()) {
                $return = array_merge($return, $actions);
            }
        }
    }

    return $return;
}

/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function abook_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $abooknode The node to add module settings to
 * @return void
 */
function abook_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $abooknode) {
    global $USER, $PAGE;

    $plugins = core_component::get_plugin_list('abooktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'abooktool_'.$plugin.'_extend_settings_navigation';
        if (function_exists($function)) {
            $function($settingsnav, $abooknode);
        }
    }

    $params = $PAGE->url->params();

    if (!empty($params['id']) and !empty($params['slideid']) and has_capability('mod/abook:edit', $PAGE->cm->context)) {
        if (!empty($USER->editing)) {
            $string = get_string("turneditingoff");
            $edit = '0';
        } else {
            $string = get_string("turneditingon");
            $edit = '1';
        }
        $url = new moodle_url('/mod/abook/view.php', array('id'=>$params['id'], 'slideid'=>$params['slideid'], 'edit'=>$edit, 'sesskey'=>sesskey()));
        $abooknode->add($string, $url, navigation_node::TYPE_SETTING);
    }
}


/**
 * Lists all browsable file areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function abook_get_file_areas($course=null, $cm=null, $context=null) {
    $areas = array(
    	'content'    => get_string('content'   , 'mod_abook'),
    	'content1'   => get_string('content1'  , 'mod_abook'),
    	'content2'   => get_string('content2'  , 'mod_abook'),
    	'content3'   => get_string('content3'  , 'mod_abook'),
    	'wallpaper'  => get_string('wallpaper' , 'mod_abook'),
    	'boardpix'   => get_string('boardpix'  , 'mod_abook'),
    	'boardpix1'  => get_string('boardpix1' , 'mod_abook'),
    	'boardpix2'  => get_string('boardpix2' , 'mod_abook'),
    	'boardpix3'  => get_string('boardpix3' , 'mod_abook'),
    	'footerpix'  => get_string('footerpix' , 'mod_abook'),
    	'teacherpix' => get_string('teacherpix', 'mod_abook')
    );
    return $areas;
}

/**
 * File browsing support for abook module slide area.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return object file_info instance or null if not found
 */
function abook_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    // note: 'intro' area is handled in file_browser automatically

    if (!has_capability('mod/abook:read', $context)) {
        return null;
    }

    if (!array_key_exists($filearea, abook_get_file_areas())) {
        return null;
    }

    require_once(dirname(__FILE__).'/locallib.php');

    if (is_null($itemid)) {
        return new abook_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$storedfile = $fs->get_file($context->id, 'mod_abook', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }

    // modifications may be tricky - may cause caching problems
    $canwrite = has_capability('mod/abook:edit', $context);

    $slidename = $DB->get_field('abook_slide', 'title', array('abookid'=>$cm->instance, 'id'=>$itemid));
    $slidename = format_string($slidename, true, array('context'=>$context));

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $slidename, true, true, $canwrite, false);
}

/**
 * Serves the abook attachments. Implements needed access control ;-)
 *
 * @param stdClass $course course object
 * @param cm_info $cm course module object
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function abook_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if (!array_key_exists($filearea, abook_get_file_areas())) {
        return false;
    }

    if (!has_capability('mod/abook:read', $context)) {
        return false;
    }

    $slid = (int)array_shift($args);

    if (!$abook = $DB->get_record('abook', array('id'=>$cm->instance))) {
        return false;
    }

    if (!$slide = $DB->get_record('abook_slide', array('id'=>$slid, 'abookid'=>$abook->id))) {
        return false;
    }

    if ($slide->hidden and !has_capability('mod/abook:viewhiddenslides', $context)) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/mod_abook/{$filearea}/{$slid}/{$relativepath}";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Nasty hack because we do not have file revisions in abook yet.
    $lifetime = $CFG->filelifetime;
    if ($lifetime > 60*10) {
        $lifetime = 60*10;
    }

    // finally send the file
    send_stored_file($file, $lifetime, 0, $forcedownload, $options);
}

/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function abook_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-abook-*'=>get_string('page-mod-abook-x', 'mod_abook'));
    return $module_pagetype;
}

/**
 * Return a list of slide types indexed and sorted by name
 *
 * @return array containing the certificate type
 */
function slide_types() {
	$types = array();
	$names = get_list_of_plugins('mod/abook/type');
	$sm = get_string_manager();
	foreach ($names as $name) {
		if ($sm->string_exists('type'.$name, 'certificate')) {
			$types[$name] = get_string('type'.$name, 'certificate');
		} else {
			$normalized_name = str_replace('-', ' ', $name);
			$normalized_name = str_replace('_', ' ', $normalized_name);
			$types[$name] = ucfirst($name);
		}
	}
	asort($types);
	return $types;
}
