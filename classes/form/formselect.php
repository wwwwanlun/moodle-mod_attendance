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
  * Form for selecting template for different sql reports
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 namespace mod_attendance\form;

 defined('MOODLE_INTERNAL') || die();

 /**
  * Class formselect
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
 class formselect extends \moodleform {
     /**
      * Define form.
      */
     public function definition() {
         $mform = $this->_form;

         $mform->addElement('hidden', 'id', 0);
         $mform->setType('id', PARAM_INT);

         $id = required_param('id', PARAM_INT);

         $mform->addElement('header', 'attheader', "Choose Your Report Type");


         $reports[0]="Please Select...";

         $reports['masterattendance']='Master Attendance Reports';

         $reports['lastaccess']='Last Access Reports';

         $mform->addElement('select', 'reportid', 'Select Report Type', $reports);

         $mform->setDefault('reportid', '0');

         $mform->addElement('text', 'lastaccessday', 'Search by last access day (leave blank will return all results)');

         $mform->setType('lastaccessday', PARAM_INT);

         $mform->addElement('text', 'firstname', 'Search by student first name (leave blank will return all results)');

         $mform->setType('firstname', PARAM_TEXT);

         $mform->addElement('text', 'classname', 'Search by class name(leave blank will return all results)');

         $mform->setType('classname', PARAM_TEXT);

         $mform->addElement('date_selector', 'fdate', 'From');
         $mform->addElement('date_selector', 'tdate', 'To (Inclusive)');

         // $mform->setDefault('studentid', 0);

         // $mform->addElement('header', 'attheader', get_string('tempaddform', 'attendance'));
         // $mform->addElement('text', 'tname', get_string('tusername', 'attendance'));
         // $mform->addRule('tname', 'Required', 'required', null, 'client');
         // $mform->setType('tname', PARAM_TEXT);

         // $mform->addElement('text', 'temail', get_string('tuseremail', 'attendance'));
         // $mform->addRule('temail', 'Email', 'email', null, 'client');
         // $mform->addRule('temail', '', 'callback', null, 'server');
         // $mform->setType('temail', PARAM_EMAIL);

          $mform->addElement('submit', 'submitbutton', 'Generate Report');
     }

     /**
      * Do stuff to form after creation.
      */
     public function definition_after_data() {
         $mform = $this->_form;
     }

     /**
      * Form validation.
      *
      * @param array $data
      * @param array $files
      * @return array
      */
     public function validation($data, $files) {
         $errors = parent::validation($data, $files);
         return $errors;
     }
 }