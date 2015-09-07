<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Wilker Lúcio
 * Copyright (c) 2014 Thomas Hempel <thomas@scriptme.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class AlfredResult {
	private $items, $shared;

	public function __construct() {
		$this->items = array();
		$this->shared = array();
	}

	public function addItem($item) {
		array_push($this->items, new AlfredResultItem($this, $item));
	}

	public function getShared() {
		return $this->shared;
	}

	public function setShared($key, $value) {
		$this->shared[$key] = $value;
	}

	public function __toString() {
		$xml = '<?xml version="1.0" encoding="utf-8"?><items>';

		foreach ($this->items as $item) {
			$xml .= $item;
		}

		$xml .= "</items>";

		return $xml;
	}
}

class AlfredResultItem {
	private $result, $item;

	public function __construct($result, $item) {
		$this->result = $result;
		$this->item = $item;
	}

	public function __toString() {
		$shared = $this->result->getShared();
		$options = array_merge($shared, $this->item);

		$xml = '<item';
		foreach (array('uid', 'arg', 'valid', 'autocomplete') as $key) {
			if (array_key_exists($key, $options)) $xml .= ' '.$key.'="'.$options[$key].'"';
		}
		$xml .= '>';

		foreach (array('title', 'subtitle', 'icon') as $key) {
			if (array_key_exists($key, $options)) $xml .= "<$key>".$options[$key]."</$key>";
		}

		$xml .= '</item>';

		return $xml;
	}
}
