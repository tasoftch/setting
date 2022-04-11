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


interface SettingInterface
{
	/**
	 * Fetches a setting from the SQL table
	 *
	 * @param $key
	 * @param null $default
	 * @return string|int|float|array
	 */
	public function getSetting($key, $default = NULL);

	/**
	 * Defines or updates a new setting.
	 *
	 * If temporary is true, the setting is not written to the sql table.
	 *
	 * @param $key
	 * @param $value
	 * @param bool $multiple
	 * @param bool $temporary
	 * @return static
	 */
	public function setSetting($key, $value, bool $multiple = false, bool $temporary = false);

	/**
	 * @param $key
	 * @param bool $temporary
	 * @return static
	 */
	public function removeSetting($key, bool $temporary = false);
}