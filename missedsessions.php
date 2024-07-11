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
  * Display a list of attendance sessions need attention for SALT admin
  *
  * @package   mod_attendance
  * @copyright  2021 Wanlun Xue, MakamiCollege
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 require_once(dirname(__FILE__).'/../../config.php');
 require_once(dirname(__FILE__).'/locallib.php');
 require_once($CFG->libdir.'/formslib.php');

 $pageparams = new mod_attendance_sessions_page_params();
 $id = required_param('id', PARAM_INT);
 $sort = optional_param('sort', '', PARAM_RAW);
 $attconfig = get_config('attendance');

 $cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
 $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
 $attrecord = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

 require_login($course, true, $cm);

 $context = context_module::instance($cm->id);
 require_capability('mod/attendance:changeattendancesafterdeadline', $context);


 $att = new mod_attendance_structure($attrecord, $cm, $course, $context, $pageparams);

 $PAGE->set_url($att->url_missedsessions());

 $PAGE->force_settings_menu(true);

 $tabs = new attendance_tabs($att, attendance_tabs::TAB_MISSEDSESSIONS);

 $currenttime=time();

 $module=$DB->get_record_sql("
 SELECT b.*
 FROM {modules} b
 where b.name='attendance'");
 $moduleid=$module->id;

 $range=$DB->get_record_sql("
 SELECT c.*
 FROM {config_plugins} c
 where c.name='attendanceedit'
 ");
 $seconds=$range->value*60*60;



 //sort method
 if(!$sort){
     $sessions=$DB->get_records_sql("
     SELECT DISTINCT m.id, m.sessdate, m.duration, m.description,c.shortname, m.groupid, cm.id as 'cm'
     FROM {attendance_sessions} m
     JOIN {attendance} a ON m.attendanceid=a.id
     JOIN {course} c ON a.course=c.id
     JOIN {course_modules} cm ON cm.module=$moduleid AND cm.course=a.course AND cm.instance=m.attendanceid
     where m.lasttakenby=0 AND m.sessdate+m.duration<=$currenttime ORDER BY m.sessdate DESC");
 }else{
     switch($sort){      
         case 'date':{
             $sessions=$DB->get_records_sql("
             SELECT DISTINCT m.id, m.sessdate, m.duration, m.description,c.shortname, m.groupid, cm.id as 'cm'
             FROM {attendance_sessions} m
             JOIN {attendance} a ON m.attendanceid=a.id
             JOIN {course} c ON a.course=c.id
             JOIN {course_modules} cm ON cm.module=$moduleid AND cm.course=a.course AND cm.instance=m.attendanceid
             where m.lasttakenby=0 AND m.sessdate+m.duration<=$currenttime ORDER BY m.sessdate DESC");
             break;
         }
         case 'class':{
             $sessions=$DB->get_records_sql("
             SELECT DISTINCT m.id, m.sessdate, m.duration, m.description,c.shortname, m.groupid, cm.id as 'cm'
             FROM {attendance_sessions} m
             JOIN {attendance} a ON m.attendanceid=a.id
             JOIN {course} c ON a.course=c.id
             JOIN {course_modules} cm ON cm.module=$moduleid AND cm.course=a.course AND cm.instance=m.attendanceid
             where m.lasttakenby=0 AND m.sessdate+m.duration<=$currenttime ORDER BY c.shortname");
             break;
         }
         // case 'group':{
         //     $sessions=$DB->get_records_sql("
         //     SELECT DISTINCT m.id, m.sessdate, m.duration, m.description,c.shortname, m.groupid, cm.id as 'cm'
         //     FROM {attendance_sessions} m
         //     JOIN {attendance} a ON m.attendanceid=a.id
         //     JOIN {course} c ON a.course=c.id
         //     JOIN {course_modules} cm ON cm.module=$moduleid AND cm.course=a.course AND cm.instance=m.attendanceid
         //     where m.lasttakenby=0 AND m.sessdate+m.duration<=$currenttime ORDER BY m.groupid");
         //     break;
         // }
         case 'description':{
             $sessions=$DB->get_records_sql("
             SELECT DISTINCT m.id, m.sessdate, m.duration, m.description,c.shortname, m.groupid, cm.id as 'cm'
             FROM {attendance_sessions} m
             JOIN {attendance} a ON m.attendanceid=a.id
             JOIN {course} c ON a.course=c.id
             JOIN {course_modules} cm ON cm.module=$moduleid AND cm.course=a.course AND cm.instance=m.attendanceid
             where m.lasttakenby=0 AND m.sessdate+m.duration<=$currenttime ORDER BY m.description");
             break;
         }

     }
 }


 $PAGE->set_title("Missed Attendance Session List");
 $PAGE->set_heading($course->fullname);
 $PAGE->set_cacheable(true);
 $PAGE->navbar->add("Missed Session List");

 $output = $PAGE->get_renderer('mod_attendance');
 echo $output->header();
 echo $output->render($tabs);

 echo '<div>';
 echo '<h2 style="font-weight:bold;">Missed Attendance Sessions</h2>';
 //echo all the attention needed sessions in a table
 if ($sessions) {
     attendance_print_sessions($sessions,$DB,$att,$id,$seconds);  
 }else{
     echo "<h1>Nothing to Display</h1>";
 }
 echo '</div>';

 echo $output->footer();

 /**
  * Print list of sessions need attentions
  *
  * @param stdClass $sessions
  * @param mod_attendance_structure $att
  */
  function attendance_print_sessions($sessions,$DB,$att,$id,$seconds) {
    echo '<p></p>';
    //add search field
    echo '<label style="font-weight:bold;" for="search" class="text-right">Search:  </label>';
    echo '<input type="text" id="search" name="search">';
    echo '<br/>';
    echo '<p></p>';
    echo '<div class="table-responsive">';
    echo '<table border="1" bordercolor="#eeEEEE" style="background-color:#fff" cellpadding="2" align="center"'.
          'width="90%" summary="Missed Attendance Sessions" class="table table-hover"><thead class="thead-light"><tr id="row">';
    $dateurl="missedsessions.php?id=".$id."&sort=date";
    $classurl="missedsessions.php?id=".$id."&sort=class";
    $groupurl="missedsessions.php?id=".$id."&sort=group";
    $despurl="missedsessions.php?id=".$id."&sort=description";
    echo '<th class="header text-center" scope="col"><a href="'.$dateurl.'">Date</a></th>';
    echo '<th class="header text-center" scope="col">Time</th>';
    echo '<th class="header text-center" scope="col"><a href="'.$classurl.'">Course</a></th>';
    //echo '<th class="header text-center" scope="col"><a href="'.$groupurl.'">Group</a></th>';
    echo '<th class="header text-center" scope="col"><a href="'.$despurl.'">Description</a></th>';
    echo '<th class="header text-center" scope="col">Actions</th>';

    echo '</tr></thead>';
    //add javascript function to filter the search result
    echo "<script type=\"text/javascript\">
    window.onload=function(){
       let textbox=document.querySelector('#search');
       let rows=document.querySelectorAll('.table-hover tbody tr');
       textbox.addEventListener('input', function(e){  
           if(e.target.value.length>=2){
              let keyword=e.target.value.toLowerCase();
              for (let i = 0; i < rows.length; i++) {
                  let datas=rows[i].querySelectorAll('td');
                  let found=false;
                  for(let a=0;a<datas.length;a++){
                      let value=datas[a].textContent.toLowerCase();
                      if(value.includes(keyword)){
                        found=true;
                      }
                  }
                  if(found==true){
                        rows[i].style.display='table-row';
                  }else{
                        rows[i].style.display='none';
                  }
                
              }
           }else{
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display='table-row';
            }
           
           }
       });
    }                                                                                                                                                                  
    </script>";
    $currenttime=time();
    foreach ($sessions as $session) { 
            if ($session->sessdate+$session->duration+$seconds<=$currenttime) {
                //echo '<tr style="background-color: #f0f2f2" id="row" scope="row">';
                echo '<tr class="table-warning" id="row" scope="row" style="background-color: #f0f2f2">';
            } else {
                echo '<tr id="row" scope="row" class="table-light">';
                //echo '<tr style="background-color: #eafad9" id="row" scope="row">';
            }
            echo '<td class="text-center">'.userdate($session->sessdate,'%B %d, %Y').'</td>';
            $endtime=$session->sessdate+$session->duration;
            echo '<td class="text-center">'.userdate($session->sessdate,' %I:%M %p').' - '.userdate($endtime,' %I:%M %p').'</td>';
            echo '<td class="text-center">'.format_string($session->shortname).'</td>';
            // if($session->groupid==0){
            //     echo '<td class="text-center">All Students</td>';
            // }else{
            //     $group=$DB->get_record_sql("
            //     SELECT g.*
            //     FROM {groups} g
            //     where g.id=$session->groupid");
            //     $groupname=$group->name;
            //     echo '<td class="text-center">'.$groupname.'</td>';
            // }
            if($session->description==''){
                echo '<td class="text-center">-</td>';
            }else{
                echo '<td class="text-center">'.format_string($session->description).'</td>';
            }
            $takeparams = array('id' => $session->cm, 'sessionid'=>$session->id, 'grouptype'=>$session->groupid);
            $editparams=array('id' => $session->cm, 'sessionid'=>$session->id, 'action'=>2);
            $deleteparams=array('id' => $session->cm, 'sessionid'=>$session->id, 'action'=>3); 
            $takelink = html_writer::link($att->url_takebysalt($takeparams), "Take");
            $editlink= html_writer::link($att->url_editbysalt($editparams), "Edit");
            $deletelink= html_writer::link($att->url_deletebysalt($deleteparams), "Delete");
             echo '<td class="text-center">'.$takelink.' | '.$editlink.' | '.$deletelink.'</td>';
            echo '</tr>';
        } 
    echo '</table>';
    echo '</div>';
}
