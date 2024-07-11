 1. To allow teacher to edit attendance on class day, admin needs to login and gives teacher permission to "change attendance" (in attendance activity->permission, teacher usually default not allowed to change attendance)
 
 2. To enable grouping, when admin add course, need to set group mode to be "visible group", force group setting->No 
        3a. admin needs to go to course->edit icon->more->user tab->groups->create groups
        3b. In order to use the idea "current", admin need to insert a group called "current" (all lower cases)
        3c. When adding attendance activity to the class, set group mode to be visible group too (should be default checked). 

 3. Instructor/admin has to mark attendance for all students before "save attendance", or alert message will raise

 4. Instructor has to change session description when they take attendance, alert message will raise if they leave session description empty. Confirm window will pop up everytime they submit attendance because if the session description fails backend validation the instructor has to retake attendance. Instructor does not have to change session description when they edit attendance. 
        
 5. The server timezone is set to America/Edmonton

 6. The current attendance rule is: - No one can take attendance before session start
                                    - Teacher can take attendance from session start until 12 hours after session ends
                                    - Teacher can modify attendance from session start until 12 hours after session ends
                                    - Admin can take & change attendance after session start

7. The all sessions attendance reports crashes for teacher. It is fixed.
8. Apply the attendance rules to 3 more pages which the user could potentially bypass the rule
