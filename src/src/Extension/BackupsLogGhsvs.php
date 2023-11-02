<?php
namespace GHSVS\Plugin\System\BackupsLogGhsvs\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Exception;

final class BackupsLogGhsvs extends CMSPlugin
{
	use DatabaseAwareTrait;

	private $execute = null;
	protected $backuppaths = [];
	protected $backuptables = [];
	protected $log_file_name = 'plg_system_backupslogghsvs';

	public function onUserAfterLogin($options)
	{
		if (
			!$this->getApplication()->isClient('administrator')
			|| Factory::getDocument()->getType() !== 'html'
		){
			$this->execute = false;
			return;
		}

		$this->prepareExecutes();

		if ($this->execute !== true)
		{
			return;
		}

		$root = Uri::root();

		if(!empty($root))
		{
			$root = implode('-', array_filter(explode('/', str_replace(array('http://', 'https://'), '', $root))));
		}
		else
		{
			$root = Uri::getInstance()->getHost();
		}

		$db = $this->getDatabase();
		$query = $db->getQuery(true)->select('*');

		foreach ($this->backuptables as $component => $table)
		{
			$query->clear('from')->from($table);
			$db->setQuery($query);

			try
			{
				$result = $db->loadAssocList();
			}
			catch (Exception $e)
			{
				$this->getApplication()->enqueueMessage('plg_system_backupslogghsvs could not get database table entries. Code: ' . $e->getCode() . ' Message: ' . $e->getMessage() . ' Table: ' . $e->$table());
				$this->execute = false;
				return;
			}

			if ($result)
			{
				$lines = [];
				$newLines = [];

				// .txt for opening in EXCEL with chance to make settings for tab separated.
				$logFile = $this->backuppaths[$component] . '/' . $this->log_file_name
					. '_' . $root . '_' . $component . '.csv.txt';

				if (is_file($logFile))
				{
					$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				}

				array_unshift($result, array_keys($result[0]));

				foreach ($result as $data)
				{
					$data = implode("\t", $data);
					$data = $this->removeJPATH_SITE(strip_tags($data));

					if (!in_array($data, $lines))
					{
						$newLines[] = $data;
					}
				}

				if ($newLines)
				{
					file_put_contents($logFile, implode("\n", $newLines) . "\n", FILE_APPEND);
					$this->getApplication()->enqueueMessage('Just info: plg_system_backupslogghsvs added ' . count($newLines) . ' backup log entries. Extension: ' . $component);
				}
			}
		}
	}

	public function prepareExecutes()
	{
		$this->backuppaths = [
			'akeeba' => JPATH_ADMINISTRATOR . '/components/com_akeeba/backup',
			'akeebabackup' => JPATH_ADMINISTRATOR . '/components/com_akeebabackup/backup',
			'ejb'    => JPATH_ADMINISTRATOR . '/components/com_easyjoomlabackup/backups',
		];

		foreach ($this->backuppaths as $component => $path)
		{
			if (!is_dir($path) || !is_writable($path))
			{
				unset($this->backuppaths[$component]);
			}
		}

		if (!$this->backuppaths)
		{
			$this->execute = false;
			return;
		}

		$this->backuptables = array(
			'akeeba' => 'ak_stats',
			'akeebabackup' => 'akeebabackup_backups',
			'ejb'    => 'easyjoomlabackup',
		);

		$db = $this->getDatabase();

		$prefix = $db->getPrefix();

		try
		{
			$tables = $db->getTableList();
		}
		catch (Exception $e)
		{
			$this->getApplication()->enqueueMessage('plg_system_backupslogghsvs could not get database table list. Code: ' . $e->getCode() . ' Message: ' . $e->getMessage());
			$this->execute = false;
			return;
		}

		foreach ($this->backuptables as $component => $table)
		{
			if (
				!isset($this->backuppaths[$component])
				|| !in_array($prefix . $table, $tables)
			){
				unset($this->backuptables[$component]);
				continue;
			}

			$this->backuptables[$component] = $db->quoteName('#__' . $table);
		}

		if (!$this->backuptables)
		{
			$this->execute = false;
			return;
		}

		$this->execute = true;
	}

	protected function removeJPATH_SITE($str)
	{
		return str_replace(array(JPATH_SITE, "\n"), array('', ' '), $str);
	}
}
