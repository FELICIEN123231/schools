ONLINE STUDENT REGISTRATION WEB APPLICATION
===============================================

Requirements:
- XAMPP with Apache and MySQL
- PHP
- phpMyAdmin

INSTALLATION:
1. Copy this folder into:
   C:\xampp\htdocs\school

2. Start Apache and MySQL in XAMPP.

3. Open:
   http://localhost/phpmyadmin

4. Select Import and import:
   school.sql
   (This creates the database, tables, sample departments, and an admin user)

5. Open:
   http://localhost/school/

6. Login with the default admin account:
   Username: Admin
   Password: Admin@123

   (You can also create a new account via the Sign Up link)

NOTE FOR EXISTING INSTALLATIONS:
If you already had the database running before the approval feature was added,
open http://localhost/phpmyadmin, select the "school" database, and run the
statements in migrate_status_column.sql once. This adds the "status" column to
the user table and marks existing users as Approved.

Database:
- Database: school
- Tables: department, students, user
- Relationship: students.department_id -> department.id

Features:
- User registration and login (session-based authentication)
- Student accounts require admin approval before they can log in
- Approved student accounts get the default password: student@123
- Admins can approve pending registrations from the Manage Users page
- Student Create
- Student Read
- Student Update
- Student Delete
- Department Create
- Department Read
- Department Update
- Department Delete (only if no students assigned)
- Duplicate detection (student ID, email, username)
- Input validation
- Responsive CSS interface
- Logout functionality

Files:
- index.php          - Dashboard with statistics
- login.php          - User login page
- createaccount.php  - User registration page
- logout.php         - Logout handler
- auth.php           - Session protection helper
- connection.php     - Database connection
- student_add.php    - Add student form
- student_view.php   - List students
- student_edit.php   - Edit student
- student_delete.php - Delete student
- department_add.php - Add department
- department_view.php- List departments
- department_edit.php- Edit department
- department_delete.php - Delete department
- style.css          - Stylesheet
- school.sql         - Database setup script