<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class PlgSystemBackupslogghsvs extends CMSPlugin
{
  protected $app;
	protected $db;
	private $execute = null;
	protected $backuppaths = [];
	protected $backuptables = [];

	public function onAfterDispatch()
	{
		if (
			!$this->app->isClient('administrator')
			|| Factory::getDocument()->getType() !== 'html'
		){
			$this->execute = false;
			return;
		}

	}
	
	public function onUserAfterLogin($options)
	{
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
				$lines = $this->db->loadAssocList();
				echo ' 4654sd48sa7d98sD81s8d71dsa ' . print_r($lines, true);exit;
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage('plg_system_backupslogghsvs could not get database table entries. Code: ' . $e->getCode() . ' Message: ' . $e->getMessage() . ' Table: ' . $e->$table());
				$this->execute = false;
				return;
			}
			
		}
	}
	
	public function prepareExecutes()
	{
		if ($this->execute !== true)
		{
			return;
		}
		
		$this->backuppaths = array(
			'akeeba' => JPATH_ADMINISTRATOR . '/components/com_akeeba/backup',
			'ejb'    => JPATH_ADMINISTRATOR . '/components/com_easyjoomlabackup/backups'
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
				!in_array($component, $this->backuppaths)
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
}
