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
 * Chapter edit form
 *
 * @package    mod_abook
 * @copyright  2004-2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->libdir}/formslib.php");
require_once("{$CFG->dirroot}/mod/abook/lib.php");

class abook_slide_edit_form extends moodleform {

    function definition() {
        global $CFG;

        $posoptions = array(
        		'l'=>get_string('left', 'mod_abook'),
        		'r'=>get_string('right', 'mod_abook'),
        		'c'=>get_string('center', 'mod_abook')
        );
        
        require_once('animate_list.php');

        $slide      = $this->_customdata['slide'];
        $options    = $this->_customdata['options'];
        $pixoptions = $this->_customdata['pixoptions'];

        $mform = $this->_form;

        if (!empty($slide->id)) {
            $mform->addElement('header', 'general', get_string('editingslide', 'mod_abook'));
        } else {
            $mform->addElement('header', 'general', get_string('addafter', 'mod_abook'));
        }

        $mform->addElement('text', 'title', get_string('slidetitle', 'mod_abook'), array('size'=>'30'));
        $mform->setType('title', PARAM_RAW);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('editor', 'content_editor', get_string('content', 'mod_abook'), null, $options);
        $mform->setType('content_editor', PARAM_RAW);
        $mform->addRule('content_editor', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'content1_editor', get_string('content1', 'mod_abook'), null, $options);
        $mform->setType('content1_editor', PARAM_RAW);

        $mform->addElement('editor', 'content2_editor', get_string('content2', 'mod_abook'), null, $options);
        $mform->setType('content2_editor', PARAM_RAW);
        
        $mform->addElement('editor', 'content3_editor', get_string('content3', 'mod_abook'), null, $options);
        $mform->setType('content3_editor', PARAM_RAW);
        
        $mform->addElement('select', 'contentanimation', get_string('contentanimation', 'mod_abook'), $anmtopts);
        $mform->setType('contentanimation', PARAM_RAW);
        $mform->addRule('contentanimation', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('select', 'slidetype', get_string('slidetype', 'mod_abook'), slide_types());
        $mform->setType('slidetype', PARAM_RAW);
        $mform->addRule('slidetype', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('text', 'frameheight', get_string('frameheight', 'mod_abook'), array('size'=>'10'));
        $mform->setType('frameheight', PARAM_INTEGER);
        $mform->addRule('frameheight', null, 'required', null, 'client');
                
        $mform->addElement('filemanager', 'wallpaper_filemanager', get_string('wallpaper', 'mod_abook'), null, $pixoptions);
		$mform->addHelpButton('wallpaper_filemanager', 'wallpaper', 'mod_abook');
		
        $mform->addElement('filemanager', 'boardpix_filemanager', get_string('boardpix', 'mod_abook'), null, $pixoptions);
		$mform->addHelpButton('boardpix_filemanager', 'boardpix', 'mod_abook');
		
        $mform->addElement('text', 'boardheight', get_string('boardheight', 'mod_abook'), array('size'=>'10'));
        $mform->setType('boardheight', PARAM_INTEGER);
        $mform->addRule('boardheight', null, 'required', null, 'client');
        
        $mform->addElement('select', 'boardanimation', get_string('boardanimation', 'mod_abook'), $anmtopts);
        $mform->setType('boardanimation', PARAM_RAW);
        $mform->addRule('boardanimation', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('filemanager', 'footerpix_filemanager', get_string('footerpix', 'mod_abook'), null, $pixoptions);
		$mform->addHelpButton('footerpix_filemanager', 'footerpix', 'mod_abook');

		$mform->addElement('select', 'footerpos', get_string('footerpos', 'mod_abook'), $posoptions);
		$mform->setType('footerpos', PARAM_RAW);
		$mform->addRule('footerpos', get_string('required'), 'required', null, 'client');

		$mform->addElement('select', 'footeranimation', get_string('footeranimation', 'mod_abook'), $anmtopts);
		$mform->setType('footeranimation', PARAM_RAW);
		$mform->addRule('footeranimation', get_string('required'), 'required', null, 'client');

		$mform->addElement('filemanager', 'teacherpix_filemanager', get_string('teacherpix', 'mod_abook'), null, $pixoptions);
		$mform->addHelpButton('teacherpix_filemanager', 'teacherpix', 'mod_abook');
		
		$mform->addElement('select', 'teacherpos', get_string('teacherpos', 'mod_abook'), $posoptions);
		$mform->setType('teacherpos', PARAM_RAW);
		$mform->addRule('teacherpos', get_string('required'), 'required', null, 'client');
		
		$mform->addElement('select', 'teacheranimation', get_string('teacheranimation', 'mod_abook'), $anmtopts);
		$mform->setType('teacheranimation', PARAM_RAW);
		$mform->addRule('teacheranimation', get_string('required'), 'required', null, 'client');
		
		
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'pagenum');
        $mform->setType('pagenum', PARAM_INT);

        $this->add_action_buttons(true);

        // set the defaults
        $this->set_data($slide);
    }
}


