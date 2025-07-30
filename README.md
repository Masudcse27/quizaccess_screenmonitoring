# Moodle Screen monitoring

This project provides a screen monitoring solution for Moodle quizzes using a custom Moodle quiz access rule plugin and a Chrome Extension. It enhances online exam integrity by enforcing screen sharing and capturing periodic screenshots of the student’s screen during quiz attempts.

When a student begins a quiz, the plugin verifies that screen sharing is active through a Chrome Extension. The extension captures screenshots at regular intervals and uploads them to Moodle using a secure, token-authenticated Web Service API. These screenshots are stored in a custom database table and can be reviewed later by teachers via a dedicated interface.

## Features
- Enforces screen sharing before the quiz starts.
- Captures screenshots every few seconds during the attempt.
- Secure screenshot upload using Moodle Web Services.
- Screenshots are stored in the Moodle database for teacher review.
- The teacher views student-wise screenshot logs.
- Seamless integration with Moodle quiz access rules and permissions.
- Works with a custom-built Chrome Extension.

## Installation

### Install by downloading the ZIP file
- Install by downloading the ZIP file from Moodle plugins directory
- Download zip file from GitHub
- Unzip the zip file in /path/to/moodle/mod/quiz/accessrule/screenmonitoring folder or upload the zip file in the install plugins options from site administration : Site Administration -> Plugins -> Install Plugins -> Upload zip file
- In your Moodle site (as admin), Visit site administration to finish the installation.

### Install using git clone

Go to Moodle Project `root/mod/quiz/accessrule/` directory and clone code by using following commands:

```
https://github.com/Masudcse27/quizaccess_screenmonitoring.git
```
## Configuration

After installing the plugin, you can enable the plugin by configuring the quiz settings:
- Go to your quiz setting (Edit Quiz):
- Change the ‘Extra restrictions on attempts’ to Screen monitoring ‘Enable’

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_1.png">

## Settings

To update the plugin settings, navigate to plugin settings: Site Administration->Plugins->screenmonitoring
- Go to Site Administrations plugins section.
- Select screen monitoring from the activity module section to configure your plugin settings

### Screenshot Interval
Admins can adjust the Screenshot interval

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_2.png">

### External Service API Token Settings
- Admin creates an external service API token from Site administration -> server -> Web services -> Manage token
- Set the token in admin settings.

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_3.png">

## Attempting the quiz
When the 'Attempt Quiz' button is clicked, the plugin checks whether the Chrome extension is installed. If the extension is not installed, it displays an alert to install the extension and prevent the quiz from starting.

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_4.png">

If the extension is installed, then the extension is asking for screen share permissions

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_5.png">

Screen share permissions denied show an alert and prevent the quiz from starting.

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_6.png">

If the user does not share their entire screen, the plugin shows an alert, and prevent the quiz from starting.

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_7.png">

During the quiz attempt, if screen sharing is stopped manually, the plugin displays an alert and redirects the user to the quiz view page.

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_8.png">

## Monitoring Report
Admins can view the monitoring report:

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_9.png">

Admins can view individual monitoring reports:

<img width="960" alt="Upload user image & delete record settings" src="https://github.com/Masudcse27/readme_images/blob/main/screenmonitoring_images/monitor_10.png">