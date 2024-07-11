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
  * Form for creating new modification
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 namespace mod_attendance\form;

 defined('MOODLE_INTERNAL') || die();

 /**
  * Class modification
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
 class modification extends \moodleform {
     /**
      * Define form.
      */
     public function definition() {
         global $DB;
         $mform = $this->_form;

         $mform->addElement('hidden', 'id');
         $mform->setType('id', PARAM_INT);

         $id = required_param('id', PARAM_INT);

         $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
         $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
         $courseid=$course->id;

         $mform->addElement('header', 'attheader', "Add a modification");


         $students[0]="Please Select";
         $students=$students+$DB->get_records_sql_menu("
             SELECT u.id, CONCAT_WS(' ', u.firstname,u.lastname)
             FROM {course} c
             JOIN {context} ct ON c.id = ct.instanceid
             JOIN {role_assignments} ra ON ra.contextid = ct.id
             JOIN {user} u ON u.id = ra.userid
             JOIN {role} r ON r.id = ra.roleid
             where c.id = $courseid AND r.id=5");

         $mform->addElement('select', 'studentid', 'Select Student', $students);

         $mform->setDefault('studentid', 0);

         $mform->addRule('studentid', 'Required', 'required', null, 'client', false, false);

         $mform->addElement('date_selector', 'startdate', 'Modification start date');

         $mform->addElement('date_selector', 'enddate', 'Modification end date');
         $mform->addElement('textarea', 'detail', 'Modification Details', 'wrap="virtual" rows="4" cols="30"');

         $mform->addRule('detail', 'Required', 'required', null, 'client', false, false);
         $mform->setType('detail',PARAM_TEXT);

         $this->add_action_buttons();
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