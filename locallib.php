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
 * Book module local lib functions
 *
 * @package    mod_abook
 * @copyright  2010-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * Preload abook slides and fix toc structure if necessary.
 *
 * Returns array of slides with standard 'pagenum', 'id, pagenum, title, hidden'
 * and extra 'parent, number, prev, next'.
 * Please note the content/text of slides is not included.
 *
 * @param  stdClass $abook
 * @return array of id=>slide
 */
function abook_preload_slides($abook) {
    global $DB;
    $slides = $DB->get_records('abook_slide', array('abookid'=>$abook->id), 'pagenum', 'id, pagenum, title, hidden');
    if (!$slides) {
        return array();
    }

    $prev = null;
    $prevsub = null;

    $hidesub = true;
    $parent = null;
    $pagenum = 0; // slide sort
    $i = 0;       // main slide num
    foreach ($slides as $id => $sl) {
        $oldsl = clone($sl);
        $pagenum++;
        $sl->pagenum = $pagenum;

        if ($oldsl->pagenum != $sl->pagenum) {
            // update only if something changed
            $DB->update_record('abook_slide', $sl);
        }
        $slides[$id] = $sl;
    }

    return $slides;
}

/**
 * Returns the title for a given slide
 *
 * @param int $slid
 * @param array $slides
 * @param stdClass $abook
 * @param context_module $context
 * @return string
 */
function abook_get_slide_title($slid, $slides, $abook, $context) {
    $sl = $slides[$slid];
    $title = trim(format_string($sl->title, true, array('context'=>$context)));
    return $title;
}

/**
 * Add the abook TOC sticky block to the default region
 *
 * @param array $slides
 * @param stdClass $slide
 * @param stdClass $abook
 * @param stdClass $cm
 * @param bool $edit
 */
function abook_add_fake_block($slides, $slide, $abook, $cm, $edit) {
    global $OUTPUT, $PAGE;

    $toc = abook_get_toc($slides, $slide, $abook, $cm, $edit, 0);

    $bc = new block_contents();
    $bc->title = get_string('toc', 'mod_abook');
    $bc->attributes['class'] = 'block block_abook_toc';
    $bc->content = $toc;

    $defaultregion = $PAGE->blocks->get_default_region();
    $PAGE->blocks->add_fake_block($bc, $defaultregion);
}

/**
 * Generate toc structure
 *
 * @param array $slides
 * @param stdClass $slide
 * @param stdClass $abook
 * @param stdClass $cm
 * @param bool $edit
 * @return string
 */
function abook_get_toc($slides, $slide, $abook, $cm, $edit) {
    global $USER, $OUTPUT;

    $toc = '';
    $first = 1;

    $context = context_module::instance($cm->id);
    $toc .= html_writer::start_tag('div', array('class' => 'abook_toc_none clearfix'));

    if ($edit) { // Teacher's TOC
        $toc .= html_writer::start_tag('ul');
        $i = 0;
        foreach ($slides as $sl) {
            $i++;
            $title = trim(format_string($sl->title, true, array('context'=>$context)));

            if ($first) {
                $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
            } else {
                $toc .= html_writer::end_tag('ul');
                $toc .= html_writer::end_tag('li');
                $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
            }

            if ($sl->hidden) {
                $title = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
            }

            if ($sl->id == $slide->id) {
                $toc .= html_writer::tag('strong', $title);
            } else {
                $toc .= html_writer::link(new moodle_url('view.php', array('id' => $cm->id, 'slideid' => $sl->id)), $title, array('title' => s($title)));
            }

            $toc .= html_writer::start_tag('div', array('class' => 'action-list'));
            if ($i != 1) {
                $toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'slideid' => $sl->id, 'up' => '1', 'sesskey' => $USER->sesskey)),
                                            $OUTPUT->pix_icon('t/up', get_string('up')), array('title' => get_string('up')));
            }
            if ($i != count($slides)) {
                $toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'slideid' => $sl->id, 'up' => '0', 'sesskey' => $USER->sesskey)),
                                            $OUTPUT->pix_icon('t/down', get_string('down')), array('title' => get_string('down')));
            }
            $toc .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'id' => $sl->id)),
                                        $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
            $toc .= html_writer::link(new moodle_url('delete.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
                                        $OUTPUT->pix_icon('t/delete', get_string('delete')), array('title' => get_string('delete')));
            if ($sl->hidden) {
                $toc .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
                                            $OUTPUT->pix_icon('t/show', get_string('show')), array('title' => get_string('show')));
            } else {
                $toc .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
                                            $OUTPUT->pix_icon('t/hide', get_string('hide')), array('title' => get_string('hide')));
            }
            $toc .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'pagenum' => $sl->pagenum )),
                                            $OUTPUT->pix_icon('add', get_string('addafter', 'mod_abook'), 'mod_abook'), array('title' => get_string('addafter', 'mod_abook')));
            $toc .= html_writer::end_tag('div');

            $toc .= html_writer::start_tag('ul');
            $first = 0;
        }

        $toc .= html_writer::end_tag('ul');
        $toc .= html_writer::end_tag('li');
        $toc .= html_writer::end_tag('ul');

    } else { // Normal students view
        $toc .= html_writer::start_tag('ul');
        foreach ($slides as $sl) {
            $title = trim(format_string($sl->title, true, array('context'=>$context)));
            if (!$sl->hidden) {
                if ($first) {
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                } else {
                    $toc .= html_writer::end_tag('ul');
                    $toc .= html_writer::end_tag('li');
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                }
                
                if ($sl->id == $slide->id) {
                    $toc .= html_writer::tag('strong', $title);
                } else {
                    $toc .= html_writer::link(new moodle_url('view.php', array('id' => $cm->id, 'slideid' => $sl->id)), $title, array('title' => s($title)));
                }

                $toc .= html_writer::start_tag('ul');

                $first = 0;
            }
        }

        $toc .= html_writer::end_tag('ul');
        $toc .= html_writer::end_tag('li');
        $toc .= html_writer::end_tag('ul');

    }

    $toc .= html_writer::end_tag('div');

    $toc = str_replace('<ul></ul>', '', $toc); // Cleanup of invalid structures.

    return $toc;
}

/**
 * Get pix urls to slide areas
 * 
 * @param stdClass $context : The filearea context
 * @param stdClass $slide   : The slide 
 * @param string $area      : The slide pix area (wallpaper, boardpix, footerpix, teacherpix)
 * 
 */

function get_pix_url($context, $slide, $area) {
	
	$fs = get_file_storage();
	
	if ($files = $fs->get_area_files($context->id, 'mod_abook', $area, $slide->id, "timemodified", false)) {
		foreach ($files as $file) {
			$url = moodle_url::make_pluginfile_url($context->id, 'mod_abook', $area, $slide->id, $file->get_filepath(), $file->get_filename());
			return $url;
		}
	}
	
	if ($area == 'boardpix') {
		return ''; # No default for boardpix
	}
	return "{$CFG->wwwroot}/mod/abook/pix/$area.png";
}

/**
 * File browsing support class
 *
 * @copyright  2010-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class abook_file_info extends file_info {
    /** @var stdClass Course object */
    protected $course;
    /** @var stdClass Course module object */
    protected $cm;
    /** @var array Available file areas */
    protected $areas;
    /** @var string File area to browse */
    protected $filearea;

    /**
     * Constructor
     *
     * @param file_browser $browser file_browser instance
     * @param stdClass $course course object
     * @param stdClass $cm course module object
     * @param stdClass $context module context
     * @param array $areas available file areas
     * @param string $filearea file area to browse
     */
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course   = $course;
        $this->cm       = $cm;
        $this->areas    = $areas;
        $this->filearea = $filearea;
    }

    /**
     * Returns list of standard virtual file/directory identification.
     * The difference from stored_file parameters is that null values
     * are allowed in all fields
     * @return array with keys contextid, filearea, itemid, filepath and filename
     */
    public function get_params() {
        return array('contextid'=>$this->context->id,
                     'component'=>'mod_abook',
                     'filearea' =>$this->filearea,
                     'itemid'   =>null,
                     'filepath' =>null,
                     'filename' =>null);
    }

    /**
     * Returns localised visible name.
     * @return string
     */
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    /**
     * Can I add new files or directories?
     * @return bool
     */
    public function is_writable() {
        return false;
    }

    /**
     * Is directory?
     * @return bool
     */
    public function is_directory() {
        return true;
    }

    /**
     * Returns list of children.
     * @return array of file_info instances
     */
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    /**
     * Help function to return files matching extensions or their count
     *
     * @param string|array $extensions, either '*' or array of lowercase extensions, i.e. array('.gif','.jpg')
     * @param bool|int $countonly if false returns the children, if an int returns just the
     *    count of children but stops counting when $countonly number of children is reached
     * @param bool $returnemptyfolders if true returns items that don't have matching files inside
     * @return array|int array of file_info instances or the count
     */
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        $params = array('contextid' => $this->context->id,
            'component' => 'mod_abook',
            'filearea' => $this->filearea,
            'abookid' => $this->cm->instance);
        $sql = 'SELECT DISTINCT bc.id, bc.pagenum
                    FROM {files} f, {abook_slide} bc
                    WHERE f.contextid = :contextid
                    AND f.component = :component
                    AND f.filearea = :filearea
                    AND bc.abookid = :abookid
                    AND bc.id = f.itemid';
        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions, 'f');
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly === false) {
            $sql .= ' ORDER BY bc.pagenum';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_abook', $this->filearea, $record->id)) {
                if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                    $children[] = $child;
                }
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    /**
     * Returns list of children which are either files matching the specified extensions
     * or folders that contain at least one such file.
     *
     * @param string|array $extensions, either '*' or array of lowercase extensions, i.e. array('.gif','.jpg')
     * @return array of file_info instances
     */
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    /**
     * Returns the number of children which are either files matching the specified extensions
     * or folders containing at least one such file.
     *
     * @param string|array $extensions, for example '*' or array('.gif','.jpg')
     * @param int $limit stop counting after at least $limit non-empty children are found
     * @return int
     */
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    /**
     * Returns parent file_info instance
     * @return file_info or null for root
     */
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
