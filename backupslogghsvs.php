<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgSystemBackupslogghsvs extends CMSPlugin
{
  protected $app;
	protected $db;
	private $execute = null;
	protected $backuppaths = array();
	protected $backuptables = array();
	protected $log_file_name = 'plg_system_backupslogghsvs';
	
	public function onUserAfterLogin($options)
	{
		if (
			!$this->app->isClient('administrator')
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
		
		$query = $this->db->getQuery(true)
		->select('*');
		
		foreach ($this->backuptables as $component => $table)
		{
			$query->clear('from')->from($table);
			$this->db->setQuery($query);
		
			try
			{
				$result = $this->db->loadAssocList();
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage('plg_system_backupslogghsvs could not get database table entries. Code: ' . $e->getCode() . ' Message: ' . $e->getMessage() . ' Table: ' . $e->$table());
				$this->execute = false;
				return;
			}

			if ($result)
			{
				$lines = array();
				$do = 0;
				
				// .txt for opening in EXCEL with chance to make settings for tab separated.
				$logFile = $this->backuppaths[$component] . '/' . $this->log_file_name . '_' . $component . '.csv.txt';

				if (is_file($logFile))
				{
					$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					// $lines = array_flip($lines);
				}

				array_unshift($result, array_keys($result[0]));

				foreach ($result as $data)
				{
					$data = implode("\t", $data);
					$data = $this->removeJPATH_SITE(strip_tags($data));

					if (!in_array($data, $lines))
					{
						$lines[] = $data;
						$do++;
					}
				}
				
				if ($do)
				{
					file_put_contents($logFile, implode("\n", $lines), FILE_APPEND);
					$this->app->enqueueMessage('Just info: plg_system_backupslogghsvs added ' . $do . ' backup log entries. Extension: ' . $component);
				}
			}
		}
	}

	public function prepareExecutes()
	{
		$this->backuppaths = array(
			'akeeba' => JPATH_ADMINISTRATOR . '/components/com_akeeba/backup',
			'ejb'    => JPATH_ADMINISTRATOR . '/components/com_easyjoomlabackup/backups',
		);

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
			'ejb'    => 'easyjoomlabackup',
		);

		$prefix = $this->db->getPrefix();

		try
		{
			$tables = $this->db->getTableList();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage('plg_system_backupslogghsvs could not get database table list. Code: ' . $e->getCode() . ' Message: ' . $e->getMessage());
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

			$this->backuptables[$component] = $this->db->qn('#__' . $table);
		}

		if (!$this->backuptables)
		{
			$this->execute = false;
			return;
		}

		$this->execute = true;
	}

	protected static function removeJPATH_SITE($str)
	{
		return str_replace(array(JPATH_SITE, "\n"), array('', ' '), $str);
	}
}
