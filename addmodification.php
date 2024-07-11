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
  * Add Modifications
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 require_once(dirname(__FILE__).'/../../config.php');
 require_once($CFG->libdir.'/formslib.php');
 require_once($CFG->dirroot.'/mod/attendance/locallib.php');

 $id = required_param('id', PARAM_INT);

 $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
 $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

 $att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

 $att = new mod_attendance_structure($att, $cm, $course);
 $PAGE->set_url($att->url_modificationadd());

 require_login($course, true, $cm);
 $context = context_module::instance($cm->id);
 require_capability('mod/attendance:modifications', $context);

 $PAGE->set_title('Modifications');
 $PAGE->set_heading($course->fullname);
 $PAGE->force_settings_menu(true);
 $PAGE->set_cacheable(true);
 $PAGE->navbar->add('Modifications');

 $output = $PAGE->get_renderer('mod_attendance');
 $tabs = new attendance_tabs($att, attendance_tabs::TAB_MODIFICATIONADD);

 $formdata = (object)array(
     'id' => $cm->id,
 );

 //call modification form

 $mform = new mod_attendance\form\modification();
 $mform->set_data($formdata);


 if ($data = $mform->get_data()) {
     // Create modification element
     $modification = new stdClass();
     $modification->userid = $data->studentid;
     $modification->starttime=$data->startdate;
     if($data->startdate<$data->enddate){
        $endtime=$data->enddate+86399;
        $modification->endtime=$endtime;
     }else{
        $modification->endtime=$data->enddate+63072000;
     }
     $modification->modification=$data->detail;
     $modification->active=1;
     //insert new data to database
     if($modification->userid!=0){
        $modid=$DB->insert_record('attendance_modifications', $modification);
        redirect($att->url_modificationadd());
    }
 }

 // Output starts here.
 echo $output->header();
 echo $output->heading('Modifications: Add new modification');
 echo $output->render($tabs);
 if ($data = $mform->get_data()) {
    if($modification->userid==0){
        echo "<p style='color:red;'>Please select student from the list</p>";
        echo "<br/>";
    }

}
 $mform->display();
//add javascript to do validation
 echo "<script type=\"text/javascript\">

      window.onload=function(){
         let form=document.querySelector('form');
         let button=document.getElementsByName('submitbutton');
         button[0].onclick=function(e){
             e.preventDefault();
             let select=document.getElementsByName('studentid');
             let studentid=select[0].options[select[0].selectedIndex].value;
             if(studentid==0){
                 alert('You must choose a student from the list');
                 return false;
             }else{
                 let details=document.getElementsByName('detail');
                 if(details[0].value==''){
                     alert('Please enter modification details');
                     return false;
                 }else{
                     button[0].onclick=window.onbeforeunload = null;
                     form.submit();
                     return true;
                 }
             }                                                                                     
             }
         }
                                                             
                                                      
                                                                     
      </script>";


 
  //get all existing modifications from database by descending list
  $modifications=$DB->get_records_sql("
  SELECT m.id, u.firstname,u.lastname,u.email,m.starttime,m.endtime,m.modification
  FROM {attendance_modifications} m
  JOIN {user} u ON m.userid = u.id
  where m.active=1 ORDER BY m.id DESC");
 
  echo "<br/>";
  echo "<hr>";
  echo '<div>';
  echo '<h2 style="margin-left:5%; font-weight:bold;">Active Modifications</h2>';
 //echo all modifications in a table
 if ($modifications) {
     attendance_print_modification($modifications, $att);
 }
 echo '</div>';
 echo $output->footer($course);

 /**
  * Print list of users.
  *
  * @param stdClass $tempusers
  * @param mod_attendance_structure $att
  */
  function attendance_print_modification($modifications, mod_attendance_structure $att) {
     echo '<p></p>';
      //add search field
      echo '<label style="margin-left:5%; font-weight:bold;" for="search">Search by first name:  </label>';
      echo '<input type="text" id="search" name="search">';
      echo '<br/>';
      echo '<p></p>';

      echo '<table border="1" bordercolor="#eeEEEE" style="background-color:#fff" cellpadding="2" align="center"'.
           'width="90%" summary="Active Modifications"><tr id="row">';
     echo '<th class="header">First Name</th>';
     echo '<th class="header">Last Name</th>';
     echo '<th class="header">Email</th>';
     echo '<th class="header">Start Date</th>';
     echo '<th class="header">End Date</th>';
     echo '<th class="header">Details</th>';
     echo '<th class="header">'.get_string('tactions', 'attendance').'</th>';
     echo '</tr>';
      //add javascript function to filter the search result
      echo "<script type=\"text/javascript\">
      window.onload=function(){
         let textbox=document.querySelector('#search');
         let fns=document.querySelectorAll('#fn');
         textbox.addEventListener('input', function(e){  
             if(e.target.value.length>=2){
                let keyword=e.target.value.toLowerCase();
                for (let i = 0; i < fns.length; i++) {
                  let firstname=fns[i].textContent.toLowerCase();
                  let parent=fns[i].parentElement;
                  if(firstname.includes(keyword)){
                      //display parent element
                      parent.style.display='table-row';
                  }else{
                      //hide parent element
                      parent.style.display='none';
                  }
                }
             }else{
              for (let i = 0; i < fns.length; i++) {
                  let parent=fns[i].parentElement;
                  if(i<10){
                      //display latest 10 parent element
                      parent.style.display='table-row';
                  }else{
                      //hide the rest of row
                      parent.style.display='none';
                  }
                }
             }
         });
      }                                                                                                                                                                  
      </script>";
 
 

     $even = false; // Used to colour rows.
     
     $count=1;
     foreach ($modifications as $modification) {
         //hide rows if the results are over 10 rows
         if($count>10){
             if ($even) {
                 echo '<tr style="background-color: #FCFCFC; display: none;" id="row">';
             } else {
                 echo '<tr style="display: none;" id="row">';
             }
             $even = !$even;
             echo '<td id="fn">'.format_string($modification->firstname).'</td>';
             echo '<td>'.format_string($modification->lastname).'</td>';
             echo '<td>'.format_string($modification->email).'</td>';
             echo '<td>'.userdate($modification->starttime, get_string('strftimedate')).'</td>';
             echo '<td>'.userdate($modification->endtime, get_string('strftimedate')).'</td>';
             echo '<td>'.format_string($modification->modification).'</td>';
             $params = array('modid' => $modification->id);
             $editlink = html_writer::link($att->url_modificationedit($params), "Edit");
             $inactivelink = html_writer::link($att->url_modificationinactive($params), "Inactive");
             echo '<td>'.$editlink.' | '.$inactivelink.'</td>';
             echo '</tr>';
         }else{
             if ($even) {
                 echo '<tr style="background-color: #FCFCFC" id="row">';
             } else {
                 echo '<tr id="row">';
             }
             $even = !$even;
             echo '<td id="fn">'.format_string($modification->firstname).'</td>';
             echo '<td>'.format_string($modification->lastname).'</td>';
             echo '<td>'.format_string($modification->email).'</td>';
             echo '<td>'.userdate($modification->starttime, get_string('strftimedate')).'</td>';
             echo '<td>'.userdate($modification->endtime, get_string('strftimedate')).'</td>';
             echo '<td>'.format_string($modification->modification).'</td>';
             $params = array('modid' => $modification->id);
             $editlink = html_writer::link($att->url_modificationedit($params), "Edit");
             $inactivelink = html_writer::link($att->url_modificationinactive($params), "Inactive");
             echo '<td>'.$editlink.' | '.$inactivelink.'</td>';
             echo '</tr>';
            }
             $count++;
         
        }
        
     
     echo '</table>';
    }
 