<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\services;

/**
 * Auto Database Restore configuration service.
 * We store config settings in a file instead of the database so that
 * we can backup and restore all tables including the config table
 * without affecting the settings of this exension
 */
class settings
{
	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $config_file;

	/** @var array */
	protected $settings = array(
		'backup_file'		=> '',
		'restore_frequency'	=> 60,
		'cron_last_run'		=> 0,
		'auto_refresh'		=> true,
		'show_notice'		=> true,
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\filesystem\filesystem	$filesystem			phpBB file system
	 * @param string						$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string						$config_file		autodbrestore config file
	 * @return void
	 */
	public function __construct(\phpbb\filesystem\filesystem $filesystem, $phpbb_root_path, $config_file)
	{
		$this->filesystem = $filesystem;
		$this->config_file = $config_file;

		if (!file_exists($this->config_file))
		{
			$this->filesystem->copy($phpbb_root_path . 'ext/blitze/autodbrestore/default.config', $this->config_file);
		}

		$this->set_settings(include($this->config_file));
	}

	/**
	 * @return bool
	 */
	public function is_ready()
	{
		return ($this->settings['backup_file'] && $this->settings['restore_frequency']);
	}

	/**
	 * @param string $setting
	 * @return mixed
	 */
	public function get($setting)
	{
		return $this->settings[$setting];
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	public function save(array $settings)
	{
		$this->set_settings($settings);
		$settings = var_export($this->settings, true);
		$comment = '// Auto Database Restore settings';

		$this->filesystem->dump_file($this->config_file, "<?php\n$comment\nreturn $settings;\n");
	}

	/**
	 * @return array
	 */
	public function get_settings()
	{
		return $this->settings;
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	public function set_settings($settings)
	{
		$this->settings = array_merge($this->settings, $settings);
	}
}
