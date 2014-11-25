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
 * Book module log events definition
 *
 * @package    mod_book
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'abook', 'action' => 'add'           , 'mtable' => 'abook'      , 'field' => 'name' ),
    array('module' => 'abook', 'action' => 'update'        , 'mtable' => 'abook'      , 'field' => 'name' ),
    array('module' => 'abook', 'action' => 'view'          , 'mtable' => 'abook'      , 'field' => 'name' ),
    array('module' => 'abook', 'action' => 'add chapter'   , 'mtable' => 'abook_slide', 'field' => 'title'),
    array('module' => 'abook', 'action' => 'update chapter', 'mtable' => 'abook_slide', 'field' => 'title'),
    array('module' => 'abook', 'action' => 'view chapter'  , 'mtable' => 'abook_slide', 'field' => 'title')
);
