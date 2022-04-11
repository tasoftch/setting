<?php
/*
 * Copyright (c) 2022 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Setting;


use TASoft\Util\PDO;

abstract class AbstractSetting implements SettingInterface
{
	const RECORD_ID_KEY = 'id';
	const RECORD_NAME_KEY = 'name';
	const RECORD_CONTENT_KEY = 'content';
	const RECORD_MULTIPLE_KEY = 'multiple';

	/** @var array  */
	protected $settings = [];

	/** @var static */
	protected static $settingInstance;

	/** @var PDO */
	protected $PDO;

	/**
	 * Gets the table name to fetch settings from.
	 *
	 * @return string
	 */
	abstract protected function getTableName(): string;

	/**
	 * Provides an instance of PDO to fetch the settings.
	 *
	 * @return PDO
	 */
	abstract protected function getPDO(): PDO;


	/**
	 * Creates the desired setting instance once or return it if it was already created.
	 *
	 * @return static
	 */
	public static function getDefaultSetting() {
		if(!static::$settingInstance)
			static::$settingInstance = new static();
		return static::$settingInstance;
	}


	protected function __construct() {
		$table = $this->getTableName();
		$this->importSettingsFromTable($table, $this->_getPDO());
	}

	/**
	 * Internal import setting method
	 *
	 * @param string $table
	 * @param PDO $PDO
	 */
	protected function importSettingsFromTable(string $table, PDO $PDO) {
		$n = static::RECORD_NAME_KEY;
		$c = static::RECORD_CONTENT_KEY;
		$m = static::RECORD_MULTIPLE_KEY;

		foreach($PDO->select("SELECT DISTINCT $n, $c, $m FROM $table") as $record) {
			if($k = $record[ $n ] ?? NULL) {
				$cnt = $record[ $c ] ?? NULL;

				if($this->isMultipleRecord($record))
					$this->settings[$k][] = $cnt;
				else
					$this->settings[$k] = $cnt;
			}
		}
	}

	/**
	 * Declares a record as multiple values setting or not.
	 *
	 * @param $record
	 * @return bool
	 */
	protected function isMultipleRecord($record): bool
	{
		return ( $record[static::RECORD_MULTIPLE_KEY] ) ? true : false;
	}


	/**
	 * @inheritDoc
	 */
	public function getSetting($key, $default = NULL)
	{
		return $this->settings[$key] ?? $default;
	}

	/**
	 * @inheritDoc
	 */
	public function setSetting($key, $value, bool $multiple = false, bool $temporary = false)
	{
		if($multiple) {
			if(NULL === $this->settings[$key] || is_array($this->settings[$key]))
				$this->settings[$key][] = $value;
		} else
			$this->settings[$key] = $value;

		if(!$temporary) {
			$table = $this->getTableName();
			$i = static::RECORD_ID_KEY;
			$n = static::RECORD_NAME_KEY;
			$c = static::RECORD_CONTENT_KEY;
			$m = static::RECORD_MULTIPLE_KEY;

			if($multiple)
				$this->_getPDO()->inject("INSERT INTO $table ($n, $c, $m) VALUES (?, ?, 1)")->send([
					$key,
					$value
				]);
			else {
				$vid = $this->_getPDO()->selectFieldValue("SELECT $i FROM $table WHERE $n = ?", $i, [$key]);
				if($vid)
					$this->_getPDO()->inject("UPDATE $table SET $c = ? WHERE $i = $vid")->send([$value]);
				else
					$this->_getPDO()->inject("INSERT INTO $table ($n, $c, $m) VALUES (?, ?, 0)")->send([
						$key,
						$value
					]);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function removeSetting($key, bool $temporary = false)
	{
		if(isset($this->settings[$key]))
			unset($this->settings[$key]);

		if(!$temporary) {
			$table = $this->getTableName();
			$n = static::RECORD_NAME_KEY;
			$this->_getPDO()->inject("DELETE FROM $table WHERE $n = ?")->send([$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getSettings(): array
	{
		return $this->settings;
	}

	protected function _getPDO(): PDO {
		if(!$this->PDO)
			$this->PDO = $this->getPDO();
		return $this->PDO;
	}
}