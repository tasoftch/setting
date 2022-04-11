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


use TASoft\Setting\Exception\ReadonlySettingException;
use TASoft\Util\PDO;

abstract class AbstractReadonlyTableSetting extends AbstractSetting
{
	/**
	 * Specifies a readonly table. Values are fetched but can not be changed anymore.
	 *
	 * @return string
	 */
	abstract protected function getReadonlyTableName(): string;

	private $load_ro=true;
	private $readOnlySettingNames = [];

	public function __construct()
	{
		$table = $this->getReadonlyTableName();

		$this->importSettingsFromTable($table, $this->_getPDO());
		$this->load_ro=false;

		parent::__construct();
	}

	/**
	 * This method is used to register the readonly settings.
	 *
	 * @inheritDoc
	 */
	protected function isMultipleRecord($record): bool
	{
		if($this->load_ro)
			$this->readOnlySettingNames[] = $record[static::RECORD_NAME_KEY];

		return parent::isMultipleRecord($record);
	}

	/**
	 * @inheritDoc
	 */
	public function setSetting($key, $value, bool $multiple = false, bool $temporary = false)
	{
		if(in_array($key, $this->readOnlySettingNames))
			throw (new ReadonlySettingException("Can not change readonly setting", 401))->setSettingName($key);
		return parent::setSetting($key, $value, $temporary, $multiple);
	}

	/**
	 * @inheritDoc
	 */
	public function removeSetting($key, bool $temporary = false)
	{
		if(in_array($key, $this->readOnlySettingNames))
			throw (new ReadonlySettingException("Can not remove readonly setting", 402))->setSettingName($key);
		return parent::removeSetting($key, $temporary);
	}

	/**
	 * @inheritDoc
	 */
	public function refreshSettings(array $keys)
	{
		$keys = array_filter($keys, function($k) {
			return !in_array($k, $this->readOnlySettingNames);
		});
		return parent::refreshSettings($keys);
	}
}