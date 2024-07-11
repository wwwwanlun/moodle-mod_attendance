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
  * Form for editing modifications
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
 namespace mod_attendance\form;

 defined('MOODLE_INTERNAL') || die();

 /**
  * class for displaying modificationedit form.
  *
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
 class modificationedit extends \moodleform {

     /**
      * Called to define this moodle form
      *
      * @return void
      */
     public function definition() {

         $mform = $this->_form;

         $mform->addElement('hidden', 'modid', 0);
         $mform->setType('modid', PARAM_INT);
         $mform->addElement('hidden', 'id', 0);
         $mform->setType('id', PARAM_INT);

         $mform->addElement('header', 'Edit Modification', 'Edit Modification');

         $mform->addElement('date_selector', 'startdate', 'Modification start date');

         $mform->addElement('date_selector', 'enddate', 'Modification end date');
         $mform->addElement('textarea', 'detail', 'Modification Details', 'wrap="virtual" rows="4" cols="30"');

         $mform->addRule('detail', 'Required', 'required', null, 'client', false, false);
         $mform->setType('detail',PARAM_TEXT);

         $buttonarray = array(
             $mform->createElement('submit', 'submitbutton', 'Edit Modification'),
             $mform->createElement('cancel'),
         );
         $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
         $mform->closeHeaderBefore('submit');
     }

     /**
      * Apply filter to form
      *
      */
     public function definition_after_data() {
         $mform = $this->_form;
     }

     /**
      * Perform validation on the form
      * @param array $data
      * @param array $files
      */
     public function validation($data, $files) {
         $errors = parent::validation($data, $files);

         return $errors;
     }
 } 