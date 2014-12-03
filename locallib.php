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
require_once(dirname(__FILE__).'/edit_form.php');
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

    $pagenum = 0; // slide sort
    
    foreach ($slides as $id => $sl) {
        $pagenum++;
        if ($pagenum != $sl->pagenum) {
        	// update only if pagenum changed
        	$sl->pagenum = $pagenum;
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

    $context = context_module::instance($cm->id);
    $toc .= html_writer::start_tag('div', array('class' => 'abook_toc_none clearfix'));
    $toc .= html_writer::start_tag('ul');
    
    $i = 0;
    
    foreach ($slides as $sl) {
    	$i++;
    	$title = trim(format_string($sl->title, true, array('context'=>$context)));
    	$liclass = 'clearfix';
    	if ($sl->id == $slide->id) {
    		$liclass .= ' abook_toc_selected';
    	}    	 
    	$title = html_writer::link(new moodle_url('view.php', array('id' => $cm->id, 'slideid' => $sl->id)), $title, array('title' => s($title)));
    	
    	if ($edit) { // Teacher's TOC specific items
    		$actions = html_writer::start_tag('div', array('class' => 'action-list'));
    		if ($i != 1) {
    			$actions .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'slideid' => $sl->id, 'up' => '1', 'sesskey' => $USER->sesskey)),
    					$OUTPUT->pix_icon('t/up', get_string('up')), array('title' => get_string('up')));
    		}
    		if ($i != count($slides)) {
    			$actions .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'slideid' => $sl->id, 'up' => '0', 'sesskey' => $USER->sesskey)),
    					$OUTPUT->pix_icon('t/down', get_string('down')), array('title' => get_string('down')));
    		}
    		$actions .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'id' => $sl->id)),
    				$OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
    		$actions .= html_writer::link(new moodle_url('delete.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
    				$OUTPUT->pix_icon('t/delete', get_string('delete')), array('title' => get_string('delete')));
    		if ($sl->hidden) {
    			$actions .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
    					$OUTPUT->pix_icon('t/show', get_string('show')), array('title' => get_string('show')));
    		} else {
    			$actions .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'slideid' => $sl->id, 'sesskey' => $USER->sesskey)),
    					$OUTPUT->pix_icon('t/hide', get_string('hide')), array('title' => get_string('hide')));
    		}
    		$actions .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'pagenum' => $sl->pagenum )),
    				$OUTPUT->pix_icon('add', get_string('addafter', 'mod_abook'), 'mod_abook'), array('title' => get_string('addafter', 'mod_abook')));
    		$actions .= html_writer::end_tag('div');
    		
    		$toc .= html_writer::tag('li', $title.$actions, array('class'=>$liclass));
    	} else {
    		if (!$sl->hidden) { // Show only if visible
    			$toc .= html_writer::tag('li', $title, array('class'=>$liclass));
    		}
    	}
    }
    
	$toc .= html_writer::end_tag('ul');
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

function get_pix_url($context, $slide, $filearea) {
	global $OUTPUT;
	
	if (!array_key_exists($filearea, abook_get_file_areas())) {
		return ''; # No default
	}
	
	$fs = get_file_storage();
	
	if ($files = $fs->get_area_files($context->id, 'mod_abook', $filearea, $slide->id, "timemodified", false)) {
		foreach ($files as $file) {
			$url = moodle_url::make_pluginfile_url($context->id, 'mod_abook', $filearea, $slide->id, $file->get_filepath(), $file->get_filename());
			return $url->out();
		}
	}
	
	if (in_array($filearea, array('wallpaper', 'footerpix', 'teacherpix'))) {
		return $OUTPUT->pix_url($filearea, 'mod_abook')->out();
	}
	return ''; # No default
}

/**
 * Prepare slide form
 * 
 * @param stdClass $slide - by reference
 * @param stdClass $context
 * @param array $editoroptions
 * @param array $pixoptions
 * @return abook_slide_edit_form $mform
 */

function prepare_slide_form(&$slide, $context, $editoroptions, $pixoptions, $action=null) {
	$slide = file_prepare_standard_editor($slide, 'content' , $editoroptions, $context, 'mod_abook', 'content' , $slide->id);
	$slide = file_prepare_standard_editor($slide, 'content1', $editoroptions, $context, 'mod_abook', 'content1', $slide->id);
	$slide = file_prepare_standard_editor($slide, 'content2', $editoroptions, $context, 'mod_abook', 'content2', $slide->id);
	$slide = file_prepare_standard_editor($slide, 'content3', $editoroptions, $context, 'mod_abook', 'content3', $slide->id);
	
	$slide = file_prepare_standard_filemanager($slide, 'wallpaper' , $pixoptions, $context, 'mod_abook', 'wallpaper' , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'boardpix'  , $pixoptions, $context, 'mod_abook', 'boardpix'  , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'boardpix1' , $pixoptions, $context, 'mod_abook', 'boardpix1' , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'boardpix2' , $pixoptions, $context, 'mod_abook', 'boardpix2' , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'boardpix3' , $pixoptions, $context, 'mod_abook', 'boardpix3' , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'footerpix' , $pixoptions, $context, 'mod_abook', 'footerpix' , $slide->id);
	$slide = file_prepare_standard_filemanager($slide, 'teacherpix', $pixoptions, $context, 'mod_abook', 'teacherpix', $slide->id);
	
	return new abook_slide_edit_form($action, array('slide'=>$slide, 'options'=>$editoroptions, 'pixoptions'=>$pixoptions));
}

/**
 * Process slide form
 * 
 * @param abook_slide_edit_form $mform
 * @param stdClass $slide - by reference
 * @param stdClass $abook
 * @param stdClass $context
 * @param array $editoroptions
 * @param array $pixoptions
 * @return bool indicating if slide has been correctly saved
 */

function process_slide_form($mform, &$slide, $abook, $context, $editoroptions, $pixoptions) {
	global $DB;
	// If data submitted, then process and store.
	if ($mform->is_cancelled()) {
		return false;
	} else if ($data = $mform->get_data()) {
		if (!$data->id) {
			// adding new slide
			$data->abookid        = $abook->id;
			$data->hidden         = 0;
			$data->timecreated    = time();
			$data->timemodified   = time();
			$data->importsrc      = '';
			$data->content        = '';          // updated later
			$data->content1       = '';          // updated later
			$data->content2       = '';          // updated later
			$data->content3       = '';          // updated later
			$data->contentformat  = FORMAT_HTML; // updated later
			$data->content1format = FORMAT_HTML; // updated later
			$data->content2format = FORMAT_HTML; // updated later
			$data->content3format = FORMAT_HTML; // updated later
	
			// make room for new page
			$sql = "UPDATE {abook_slide}
                   SET pagenum = pagenum + 1
                 WHERE abookid = ? AND pagenum >= ?";
			$DB->execute($sql, array($abook->id, $data->pagenum));
	
			$data->id = $DB->insert_record('abook_slide', $data);
		}
	
		// store the files
		$data->timemodified = time();
		$data = file_postupdate_standard_editor($data, 'content' , $editoroptions, $context, 'mod_abook', 'content' , $data->id);
		$data = file_postupdate_standard_editor($data, 'content1', $editoroptions, $context, 'mod_abook', 'content1', $data->id);
		$data = file_postupdate_standard_editor($data, 'content2', $editoroptions, $context, 'mod_abook', 'content2', $data->id);
		$data = file_postupdate_standard_editor($data, 'content3', $editoroptions, $context, 'mod_abook', 'content3', $data->id);
		$data = file_postupdate_standard_filemanager($data, 'wallpaper' , $pixoptions, $context, 'mod_abook', 'wallpaper' , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'boardpix'  , $pixoptions, $context, 'mod_abook', 'boardpix'  , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'boardpix1' , $pixoptions, $context, 'mod_abook', 'boardpix1' , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'boardpix2' , $pixoptions, $context, 'mod_abook', 'boardpix2' , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'boardpix3' , $pixoptions, $context, 'mod_abook', 'boardpix3' , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'footerpix' , $pixoptions, $context, 'mod_abook', 'footerpix' , $data->id);
		$data = file_postupdate_standard_filemanager($data, 'teacherpix', $pixoptions, $context, 'mod_abook', 'teacherpix', $data->id);
	
		$DB->update_record('abook_slide', $data);
		$DB->set_field('abook', 'revision', $abook->revision+1, array('id'=>$abook->id));
		$slide = $DB->get_record('abook_slide', array('id' => $data->id));
	
		\mod_abook\event\slide_updated::create_from_slide($abook, $context, $slide)->trigger();
	
		abook_preload_slides($abook); // fix structure
		return true;
	}
	
	return false;
}

/**
 * Convert slide to array of data
 * @param stdClass $abook
 * @param stdClass $slide
 * @param stdClass $context
 * @param stdClass $cm
 * @param array $fmtoptions
 * @return array $data
 */

function slide_to_array($abook, $slide, $context, $fmtoptions, $cm, $edit) {
	global $OUTPUT;
	// prepare slide navigation icons
	$slides = abook_preload_slides($abook);
	$previd = null;
	$nextid = null;
	$last = null;
	foreach ($slides as $sl) {
		if (!$edit and $sl->hidden) {
			continue;
		}
		if ($last == $slide->id) {
			$nextid = $sl->id;
			break;
		}
		if ($sl->id != $slide->id) {
			$previd = $sl->id;
		}
		$last = $sl->id;
	}
	
	$navprevicon = right_to_left() ? 'right' : 'left';
	$navnexticon = right_to_left() ? 'left' : 'right';
	
	$slnavigation = '';
	if ($previd) {
		$slnavigation .= '<a id="prevbutton" title="'.get_string('navprev', 'abook').'" href="view.php?id='.$cm->id.
		'&amp;slideid='.$previd.'" class="btn btn-default"><img src="'.$OUTPUT->pix_url($navprevicon, 'mod_abook').'" class="icon" alt="" /></a>';
	} else {
		$slnavigation .= '<button class="btn btn-default" disabled="disabled"><img src="'.$OUTPUT->pix_url($navprevicon, 'mod_abook').'" class="icon" alt="" /></button>';
	}
	
	if ($nextid) {
		$slnavigation .= '<a id="nextbutton" title="'.get_string('navnext', 'abook').'" href="view.php?id='.$cm->id.
		'&amp;slideid='.$nextid.'" class="btn btn-default"><img src="'.$OUTPUT->pix_url($navnexticon, 'mod_abook').'" class="icon" alt="" /></a>';
	} else {
		$slnavigation .= '<button class="btn btn-default" disabled="disabled"><img src="'.$OUTPUT->pix_url($navnexticon, 'mod_abook').'" class="icon" alt="" /></button>';
		// we are cheating a bit here, viewing the last page means user has viewed the whole abook
		$completion = new completion_info($course);
		$completion->set_module_viewed($cm);
	}
	
	$data = array(
			'pagetitle'         => $abook->name . ": " . $slide->title,
			'navigation'        => $slnavigation,
			'slidetype'         => $slide->slidetype,
			'title'             => abook_get_slide_title($slide->id, $slides, $abook, $context),
			'wallpaper'         => get_pix_url($context, $slide, 'wallpaper'),
			'frameheight'       => $slide->frameheight,
			'content'           => format_text(file_rewrite_pluginfile_urls($slide->content , 'pluginfile.php', $context->id, 'mod_abook', 'content' , $slide->id), $slide->contentformat, $fmtoptions),
			'content1'          => format_text(file_rewrite_pluginfile_urls($slide->content1, 'pluginfile.php', $context->id, 'mod_abook', 'content1', $slide->id), $slide->contentformat, $fmtoptions),
			'content2'          => format_text(file_rewrite_pluginfile_urls($slide->content2, 'pluginfile.php', $context->id, 'mod_abook', 'content2', $slide->id), $slide->contentformat, $fmtoptions),
			'content3'          => format_text(file_rewrite_pluginfile_urls($slide->content3, 'pluginfile.php', $context->id, 'mod_abook', 'content3', $slide->id), $slide->contentformat, $fmtoptions),
			'contentanimation'  => "{$slide->contentanimation}" .(($slide->contentanimation ) ? " animated" : ""),
			'contentanimation1' => "{$slide->contentanimation1}".(($slide->contentanimation1) ? " animated" : ""),
			'contentanimation2' => "{$slide->contentanimation2}".(($slide->contentanimation2) ? " animated" : ""),
			'contentanimation3' => "{$slide->contentanimation3}".(($slide->contentanimation3) ? " animated" : ""),
			'boardpix'          => get_pix_url($context, $slide, 'boardpix'),
			'boardpix1'         => get_pix_url($context, $slide, 'boardpix1'),
			'boardpix2'         => get_pix_url($context, $slide, 'boardpix2'),
			'boardpix3'         => get_pix_url($context, $slide, 'boardpix3'),
			'boardheight'       => $slide->boardheight,
			'boardheight1'      => $slide->boardheight1,
			'boardheight2'      => $slide->boardheight2,
			'boardheight3'      => $slide->boardheight3,
			'footerpix'         => get_pix_url($context, $slide, 'footerpix'),
			'footerpos'         => "abpos_{$slide->footerpos}",
			'footeranimation'   => "{$slide->footeranimation}".(($slide->footeranimation) ? " animated" : ""),
			'teacherpix'        => get_pix_url($context, $slide, 'teacherpix'),
			'teacherpos'        => "abpos_{$slide->teacherpos}",
			'teacheranimation'  => "{$slide->teacheranimation}".(($slide->teacheranimation) ? " animated" : "")
	);
	return $data;
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
