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
  * Attendance more reports display page
  *
  * @package    mod_attendance
  * @copyright  2021 Wanlun Xue, Makami College
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

  global $DB;
  require('../../config.php');
  require_once($CFG->libdir.'/adminlib.php');
  require_once($CFG->dirroot.'/mod/attendance/lib.php');
  require_once($CFG->dirroot.'/mod/attendance/locallib.php');

 $id = required_param('id', PARAM_INT);
 $download = optional_param('download', '', PARAM_ALPHA);
 $type= required_param('type', PARAM_ALPHA);
 $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
 $course= $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
 require_login($course, true, $cm);
 $context = context_module::instance($cm->id);
 require_capability('mod/attendance:morereports', $context);
 $PAGE->set_url('/mod/attendance/displayreports.php');
 $PAGE->set_context($context);

 switch($type){      
     case 'masterattendance':{
        $fn=$_GET['fn'];
        $cn=$_GET['cn'];
        $from=$_GET['f'];
        $to=$_GET['t'];
        master_attendance_report($id,$PAGE,$download,$fn,$cn,$from,$to);
        break;
     }
     case 'lastaccess':{
         $interval=$_GET['interval'];
         $fn=$_GET['fn'];
         render_lastedit_table($PAGE,$download,$interval,$id,$type,$fn);
         break;
     }

 }

 function master_attendance_report($id,$PAGE,$download,$fn,$cn,$start,$to){
    $output = $PAGE->get_renderer('mod_attendance');
    $baseurl=$PAGE->url.'?id='.$id.'&type=masterattendance'.'&fn='.$fn.'&cn='.$cn.'&f='.$start.'&t='.$to;
    $fields='DISTINCT u.id, us.firstname, us.lastname, us.email, c.shortname AS \'class\',attsta.description, u.remarks, attsta.grade, attsess.description AS \'Topic\', FROM_UNIXTIME(us.lastaccess, \'%M %D %Y\') as \'Last_Access\',FROM_UNIXTIME(attsess.sessdate, \'%M %D %Y\') AS \'Class_Date\',(SELECT username FROM {user} as us WHERE us.id=attsess.lasttakenby) as \'Instructor\', timestampdiff(DAY, FROM_UNIXTIME(us.lastaccess), NOW()) as \'Days_since_last_access\'';
    $from='{attendance} AS att JOIN {attendance_sessions} AS attsess ON attsess.attendanceid = att.id JOIN {attendance_log} AS u ON u.sessionid = attsess.id JOIN {attendance_statuses} AS attsta ON attsta.id = u.statusid JOIN {user} AS us ON us.id = u.studentid JOIN {course} AS c ON c.id = att.course JOIN {role_assignments} as ra on ra.userid = us.id';
    if($start<=$to){
        //get data for a date range
        $to=$to+86399;
        if($fn==''){
            //return all name
            if($cn==''){
                //return all name and all class for a date range
                $where='ra.roleid =5 AND c.shortname NOT LIKE \'Bookshelf%\' AND attsess.sessdate between :from and :to';
                $params=array('from'=>$start,'to'=>$to);
            }else{
                //return all data for a specific class for a date range
                $where='ra.roleid =5 AND c.shortname LIKE :class AND attsess.sessdate between :from and :to';
                $params=array('class'=>$cn,'from'=>$start,'to'=>$to);
            }
        }else{
            //return for a specific student first name
            if($cn==''){
                //return all class for a specific student for a date range
                $where='ra.roleid =5 AND c.shortname NOT LIKE \'Bookshelf%\' AND attsess.sessdate between :from and :to AND us.firstname LIKE :fname';
                $params=array('from'=>$start,'to'=>$to,'fname'=>$fn);
            }else{
                //return a specific class for a specific student on a date range
                $where='ra.roleid =5 AND c.shortname LIKE :class AND attsess.sessdate between :from and :to AND us.firstname LIKE :fname';
                $params=array('class'=>$cn,'from'=>$start,'to'=>$to,'fname'=>$fn);
            }
        }
    }else{
        //return no result
        $where='ra.roleid =5 AND c.shortname NOT LIKE \'Bookshelf%\' AND attsess.sessdate between :from and :to';
        $params=array('from'=>$start,'to'=>$to);
    }
    $nosort=[];
     $size=100;
     $ourl=new moodle_url('/mod/attendance/morereports.php',array('id'=>$id));
     render_more_reports_table('masterattendancetable','Master Attendance Table',$output,$baseurl,$fields,$from,$where,$params,$nosort,$size,$ourl,$download,$PAGE);
}


 function render_lastedit_table($PAGE,$download,$interval,$id,$type,$fn){
     $output = $PAGE->get_renderer('mod_attendance');
     $baseurl=$PAGE->url.'?id='.$id.'&type='.$type.'&interval='.$interval.'&fn='.$fn;
     $fields='DISTINCT u.id, us.firstname, us.lastname, us.email, h.name as \'Class\', timestampdiff(DAY, FROM_UNIXTIME(us.lastaccess), NOW()) as \'Days_since_last_access\' ,FROM_UNIXTIME(us.lastaccess, \'%M %D %Y\') as \'Last_Access\'';
     $from='{user} us JOIN {role_assignments} ra on ra.userid = us.id JOIN {cohort_members} as u on u.userid = us.id JOIN {cohort} as h on h.id = u.cohortid';
     
     if($fn==''){
        if($interval==0){
            //show all users
            $where='ra.roleid =5 AND h.name NOT LIKE \'Bookshelf%\'';
            $params=[];

        }else{
            $where='ra.roleid =5 AND FROM_UNIXTIME(us.lastaccess, \'%Y-%m-%d\') <= DATE_SUB(NOW(), INTERVAL :interval DAY) AND us.suspended =0 AND us.lastaccess<>0 AND h.name NOT LIKE \'Bookshelf%\'';
            $interval=$interval+1; 
            $params=array('interval'=>$interval);
        }
     }else{
        if($interval==0){
            $where='ra.roleid =5 AND FROM_UNIXTIME(us.lastaccess, \'%Y-%m-%d\') <= DATE_SUB(NOW(), INTERVAL :interval DAY) AND us.suspended =0 AND us.firstname LIKE :fn AND h.name NOT LIKE \'Bookshelf%\'';
            $params=array('interval'=>$interval,'fn'=>$fn);
        }else{
            $where='ra.roleid =5 AND FROM_UNIXTIME(us.lastaccess, \'%Y-%m-%d\') <= DATE_SUB(NOW(), INTERVAL :interval DAY) AND us.suspended =0 AND us.firstname LIKE :fn AND us.lastaccess<>0 AND h.name NOT LIKE \'Bookshelf%\'';
            $interval=$interval+1;
            $params=array('interval'=>$interval,'fn'=>$fn,'fn1'=>$fn);
        }
     }
     //$nosort=array('last access','days since last access');
     $nosort=[];
     $size=100;
     $ourl=new moodle_url('/mod/attendance/morereports.php',array('id'=>$id));
     render_more_reports_table('lastedittable','Last Access Report',$output,$baseurl,$fields,$from,$where,$params,$nosort,$size,$ourl,$download,$PAGE);
 }

 echo "
 <script type=\"text/javascript\">
      window.onload=function(){
         const table=document.querySelector('table.flexible');  
         let textbox=document.querySelector('#search');
         const tb=table.querySelector('tbody');
         const rows=tb.querySelectorAll('tr');
         textbox.addEventListener('input', function(e){  
             let keyword=e.target.value.toLowerCase();
             for (let i = 0; i < rows.length; i++) {
                 let columns=rows[i].querySelectorAll('td');
                 let display=false;
                 for(let r=1;r<columns.length;r++){
                     let data=columns[r].textContent.toLowerCase();
                     if(data.includes(keyword)){
                         display=true;  
                     }
                 }
                 if(display==true){
                     if(rows[i].classList.contains(\"emptyrow\")){
                         rows[i].style.display='none'; 
                     }else{
                         rows[i].style.display='table-row';
                     }        
                 }else{
                     rows[i].style.display='none'; 
                 }
             }


         });
         const clear=document.querySelector('.clear');
         clear.addEventListener('click', function(e){
             textbox.value='';
             for (let i = 0; i < rows.length; i++) {
                if(rows[i].classList.contains(\"emptyrow\")){
                    rows[i].style.display='none'; 
                }else{
                    rows[i].style.display='table-row';
                }
             }
          });
             

          
            
     }                                                  
                                                                     
      </script>
 ";