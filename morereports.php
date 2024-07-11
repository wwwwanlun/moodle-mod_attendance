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
  * More report function
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 require_once(dirname(__FILE__).'/../../config.php');
 require_once(dirname(__FILE__).'/locallib.php');
 require_once($CFG->libdir.'/formslib.php');
 require_once($CFG->libdir.'/tablelib.php');

 $pageparams = new mod_attendance_report_page_params();

 $id                     = required_param('id', PARAM_INT);
 $from                   = optional_param('from', null, PARAM_ACTION);
 $pageparams->view       = optional_param('view', null, PARAM_INT);
 $pageparams->curdate    = optional_param('curdate', null, PARAM_INT);
 $pageparams->group      = optional_param('group', null, PARAM_INT);
 $pageparams->sort       = optional_param('sort', ATT_SORT_DEFAULT, PARAM_INT);
 $pageparams->page       = optional_param('page', 1, PARAM_INT);
 $pageparams->perpage    = get_config('attendance', 'resultsperpage');

 $cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
 $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
 $attrecord = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

 require_login($course, true, $cm);

 $context = context_module::instance($cm->id);
 require_capability('mod/attendance:morereports', $context);

 $pageparams->init($cm);


 $att = new mod_attendance_structure($attrecord, $cm, $course, $context, $pageparams);

 $PAGE->set_url($att->url_morereports());
 $PAGE->set_pagelayout('report');
 $PAGE->set_title('SALT Reports');
 $PAGE->set_heading($course->fullname);
 $PAGE->force_settings_menu(true);
 $PAGE->set_cacheable(true);
 $PAGE->navbar->add('SALT Reports');

 $output = $PAGE->get_renderer('mod_attendance');
 $tabs = new attendance_tabs($att, attendance_tabs::TAB_MOREREPORTS);

 $formdata = (object)array(
     'id' => $cm->id,
 );
 $mform = new mod_attendance\form\formselect();
 $mform->set_data($formdata);



 $title = 'SALT Reports';
 $header = new mod_attendance_header($att, $title);

 // Output starts here.
 echo $output->header();
 echo $output->render($header);
 echo $output->render($tabs);
 $mform->display();
 echo "
 <script type=\"text/javascript\">

      window.onload=function(){
         let reportDropdown=document.querySelector('#id_reportid');
         let access=document.querySelector('#fitem_id_lastaccessday');
         let fn=document.querySelector('#fitem_id_firstname');
         let cn=document.querySelector('#fitem_id_classname');
         let from=document.querySelector('#fitem_id_fdate');
         let to=document.querySelector('#fitem_id_tdate');
         access.style.display='none';
         fn.style.display='none';
         cn.style.display='none';
         from.style.display='none';
         to.style.display='none';
         reportDropdown.addEventListener('change',(e)=>{
             let value=reportDropdown.value;
             switch(value){
                 case '0':{
                     access.style.display='none';
                     fn.style.display='none';
                     cn.style.display='none';
                     from.style.display='none';
                     to.style.display='none';
                     break;
                 }
                 case 'masterattendance':{
                     access.style.display='none';
                     fn.style.display='flex';
                     cn.style.display='flex';
                     from.style.display='flex';
                     to.style.display='flex';
                     break;
                 }
                 case 'lastaccess':{
                     access.style.display='flex';
                     fn.style.display='flex';
                     cn.style.display='none';
                     from.style.display='none';
                     to.style.display='none';
                     break;
                 }
             }



         })
         
         }                                                                                                                                             
      </script>

 ";
 if ($data = $mform->get_data()) {
     // get report type
     $reportType=$data->reportid;
     switch($reportType){
         case '0':{
             redirect($att->url_morereports(array('id' =>$id))); 
             break;
         }
         case 'masterattendance':{
            redirect($att->url_displayreports(array('type' =>'masterattendance','fn'=>$data->firstname,'cn'=>$data->classname,'f'=>$data->fdate,'t'=>$data->tdate)));
            break;
         }
         case 'lastaccess':{
            redirect($att->url_displayreports(array('type' =>'lastaccess', 'interval' => $data->lastaccessday,'fn'=>$data->firstname)));  
            break;
         }
     }
 }

 echo $output->footer();