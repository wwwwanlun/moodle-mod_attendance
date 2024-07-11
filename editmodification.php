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
  * Attendance tempedit
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami college
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 require_once(dirname(__FILE__).'/../../config.php');
 require_once($CFG->libdir.'/formslib.php');
 require_once($CFG->dirroot.'/mod/attendance/locallib.php');

 $modid = required_param('modid', PARAM_INT);
 $id = required_param('id', PARAM_INT);
 $action = optional_param('action', null, PARAM_ALPHA);

 $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
 $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
 $att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
 $modification = $DB->get_record('attendance_modifications', array('id' => $modid), '*', MUST_EXIST);

 $att = new mod_attendance_structure($att, $cm, $course);

 $params = array('modid' => $modification->id,'id'=>$id);
 if ($action) {
     $params['action'] = $action;
 }
 $PAGE->set_url($att->url_modificationedit($params));

 require_login($course, true, $cm);
 $context = context_module::instance($cm->id);
 require_capability('mod/attendance:modifications', $context);

 $PAGE->set_title('Edit Modification');
 $PAGE->set_heading('Edit Modification');
 $PAGE->set_cacheable(true);
 $PAGE->navbar->add('Modifications');

 /** @var mod_attendance_renderer $output */
 $output = $PAGE->get_renderer('mod_attendance');

 if ($action == 'inactive') {
     if (optional_param('confirm', false, PARAM_BOOL)) {
         require_sesskey();

         // Set the modification to be inactive
         $tempmod=$DB->get_record_sql("
         SELECT m.*
         FROM {attendance_modifications} m
         where m.id=$modid");

         $tempmod->active=0;

         $DB->update_record('attendance_modifications', $tempmod);

         redirect($att->url_modificationadd());
     } else {

         $msg ='By clicking continue, you will inactive this modification and this action is irreversible. ';
         $continue = new moodle_url($PAGE->url, array('confirm' => 1, 'sesskey' => sesskey()));

         echo $output->header();
         echo $output->confirm($msg, $continue, $att->url_modificationadd());
         echo $output->footer();

         die();
     }
 }
         $formdata = new stdClass();
         $tempmod=$DB->get_record_sql("
         SELECT m.*
         FROM {attendance_modifications} m
         where m.id=$modid");

         $formdata->id = $cm->id;
         $formdata->modid = $tempmod->id;
         $formdata->startdate = $tempmod->starttime;
         $formdata->enddate = $tempmod->endtime;
         $formdata->detail= $tempmod->modification;

         //new modification edit table populate with existing data
         $mform = new \mod_attendance\form\modificationedit();
         $mform->set_data($formdata);

         if ($mform->is_cancelled()) {
             redirect($att->url_modificationadd());
         } else if ($tempmodification = $mform->get_data()) {
             global $DB;
             $updatemod = new stdClass();
             $updatemod->id = $modid;
             $updatemod->userid = $tempmod->userid;
             $updatemod->starttime = $tempmodification->startdate;
             $updatemod->endtime = $tempmodification->enddate;
             $updatemod->modification = $tempmodification->detail;
             //update existing record
             $DB->update_record('attendance_modifications', $updatemod);
             redirect($att->url_modificationadd());
         }

         $tabs = new attendance_tabs($att, attendance_tabs::TAB_MODIFICATIONADD);

         echo $output->header();
         echo $output->heading('Edit Modifications');
         echo $output->render($tabs);
         $mform->display();
         echo $output->footer($course);

