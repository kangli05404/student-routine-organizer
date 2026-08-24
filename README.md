
# Student Routine Organizer

A server-side web application developed for the **UCCD3243 Server-Side Web Applications Development Group Assignment – June 2026 Trimester**.

The Student Routine Organizer provides students with one centralized platform to manage exercise activities, diary entries, financial transactions and personal habits.

## Main Features

### Centralized User Management

- Secure Student registration and login.
- Student and Admin user roles.
- Shared authentication session across all modules.
- Role-based page access.
- Personal profile and password management.
- Students can only manage their own records.
- Admin users can view system summaries and registered users.

### Exercise Tracker

- Add, view, edit and delete exercise records.
- Track activity type, duration, calories burned and exercise date.
- Sort records by date, duration or calories burned.
- Display total workouts, exercise minutes and calories burned.
- Display workouts completed during the current week.
- Track progress towards a fixed monthly calorie goal.
- Restore a deleted record using the 20-second Undo feature.
- Input validation and operation feedback messages.

### Diary Journal

- Add, view, edit and delete diary entries.
- Record a title, mood, date and personal reflection.
- Display total entries, recent entries and latest mood.
- Character limits and input validation.

### Money Tracker

- Add, view, edit and delete financial transactions.
- Record income and expenses.
- Track transaction categories, amounts and dates.
- Display personal financial summaries.

### Habit Tracker

- Add, view, edit and delete habit records.
- Track habit frequency, completion status and date.
- Display habit progress and summary information.

### Admin Dashboard

- View all registered Student and Admin accounts.
- View total records for every module.
- View basic system activity summaries.
- Export the Student Activity Report in CSV format.
- Restrict Admin users from Student CRUD modules.

## Technologies Used

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- PDO
- Apache
- XAMPP
- phpMyAdmin
- Git and GitHub

## Security Features

- Password hashing using `password_hash()`.
- Password verification using `password_verify()`.
- PHP session-based authentication.
- Student and Admin role authorization.
- Prepared SQL statements.
- CSRF security tokens for sensitive operations.
- HTML output escaping.
- Record ownership checks using both record ID and user ID.
- `HttpOnly` and `SameSite` session-cookie settings.

## Project Structure

```text
student-routine-organizer/
├── admin/
│   └── index.php
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
│   └── database.php
├── diary/
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   └── index.php
├── exercise/
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   ├── index.php
│   └── undo.php
├── habit/
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   └── index.php
├── includes/
│   ├── auth.php
│   ├── footer.php
│   └── header.php
├── money/
│   ├── add.php
│   ├── delete.php
│   ├── edit.php
│   └── index.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── register.php
└── README.md
