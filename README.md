# plg_system_backupslogs

## Versions lower then V2021.08.22 don't support
- Akeeba Backup **9** (Joomla 4).


Joomla plugin that copies backups informations of extensions "Akeeba Backup" and "Easy Joomla Backup" into a tab separated csv file inside each backups folder.

When a user logs in into backend:
- Read (if exists) database tables "#__ak_stats" (Akeeba Backup) and "#__easyjoomlabackup".
- Append only new OR CHANGED entries to files "plg_system_backupslogghsvs_akeeba.csv.txt" and "plg_system_backupslogghsvs_ejb.csv.txt"
- Files are located in the backups directories.
- Show a message only if new lines were added to log files.
- Nothing more.

The plugin uses hard coded paths to the backup directories! It doesn't detect changed locations in settings of extensions!
https://github.com/GHSVS-de/plg_system_backupslogghsvs/blob/2021.08.22/backupslogghsvs.php#L104-L108

Last tests: Joomla 3.9.7-dev, Joomla 4.x, PHP 8.2, MySql 5.7
