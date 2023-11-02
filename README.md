# plg_system_backupslogs

## Versions lower then V2021.08.22 don't support
- Akeeba Backup **9** (Joomla 4).

Joomla system plugin that copies db entries of Akeeba Backup and Easy Joomla Backup into a tab separated csv file inside each backups folder. No other luxury included!

When a user logs in into backend:
- Read (if exists) database tables "#__ak_stats" and "#__akeebabackup_backups" (Akeeba Backup) and "#__easyjoomlabackup".
- Append only new OR CHANGED entries to files "plg_system_backupslogghsvs_akeeba.csv.txt" and "plg_system_backupslogghsvs_akeebabackup.csv.txt" and "plg_system_backupslogghsvs_ejb.csv.txt"
- Files are located in the backups directories.
- Show a message only if new lines were added to log files.
- Nothing more.

The plugin uses hard coded paths to the backup directories! It doesn't detect changed locations in settings of extensions!

```
$this->backuppaths = [
 'akeeba' => JPATH_ADMINISTRATOR . '/components/com_akeeba/backup',
 'akeebabackup' => JPATH_ADMINISTRATOR . '/components/com_akeebabackup/backup',
 'ejb'    => JPATH_ADMINISTRATOR . '/components/com_easyjoomlabackup/backups',
];
```

-----------------------------------------------------

# My personal build procedure (WSL 1, Debian, Win 10)

- Prepare/adapt `./package.json`.
- `cd /mnt/z/git-kram/plg_system_backupslogghsvs`

## node/npm updates/installation
- `npm install` (if never done before)

### Update dependencies
- `npm run updateCheck` or (faster) `npm outdated`
- `npm run update` (if needed) or (faster) `npm update --save-dev`

## Build installable ZIP package
- `node build.js`
- New, installable ZIP is in `./dist` afterwards.
- All packed files for this ZIP can be seen in `./package`. **But only if you disable deletion of this folder at the end of `build.js`**.

### For Joomla update and changelog server
- Create new release with new tag.
  - See and copy and complete release description in `dist/release.txt`.
- Extracts(!) of the update and changelog XML for update and changelog servers are in `./dist` as well. Copy/paste and make necessary additions.
