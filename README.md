# plg_user_backupslogs
Joomla plugin that copies backups informations of extensions "Akeeba Backup" and "Easy Joomla Backup" into a tab separated csv file inside each backups folder.

When a user logs in into backend:
- Read (if exists) database tables "#__ak_stats" (Akeeba Backup) and "#__easyjoomlabackup".
- Append only new entries to files "plg_system_backupslogghsvs_akeeba.csv.txt" and "plg_system_backupslogghsvs_ejb.csv.txt"
- Files are located in the backups directories.
- Show a message only if new lines were added to log files.
- Nothing more.

Last tests: Joomla 3.9.7-dev, PHP 7.3.3, MySql 5.7.25