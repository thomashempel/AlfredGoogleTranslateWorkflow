<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013-2015 Thomas Hempel <thomas@scriptme.de>
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

require './languages.php';
require './alfred.php';
require './workflows.php';

class GoogleTranslateWorkflow
{
	protected $DEBUG = false;

	protected $workflowsInstance;

	protected $languages;

	protected $settings = array('sourceLanguage' => 'auto', 'targetLanguage' => 'en');

	protected $validOptions = array('sourceLanguage' => 'Source Language', 'targetLanguage' => 'Target Language');

	public function __construct()
	{
		$this->workflowsInstance = new Workflows();
		$this->languages = new Languages();
		$this->loadSettings();
	}

	public function process($request)
	{
		$this->log($request);

		$requestParts = explode(' ', $request);
		$command = array_shift($requestParts);
		$phrase = (count($requestParts) > 0) ? implode(' ', $requestParts) : $command;
		$result = '';

		if ($command == 'settings') {
			$result = $this->showSettings();
		} else if ($command == 'set') {
			$result = $this->set($requestParts[0], $requestParts[1]);
		} else {
			list($sourceLanguage, $targetLanguage) = $this->extractLanguages($command);
			$this->log(array($sourceLanguage, $targetLanguage));
			$targetLanguages = explode(',', $targetLanguage);
			$googleResults = array();
			foreach ($targetLanguages as $targetLanguage) {
				$googleResults[$targetLanguage] = $this->fetchGoogleTranslation($sourceLanguage, $targetLanguage, $phrase);
			}
			$this->log($googleResults);
			$result = $this->processGoogleResults($googleResults);
		}

		$this->log($result);
		return $result;
	}

	protected function showSettings()
	{
		$xml = new AlfredResult();
		$xml->setShared('uid', 'setting');

		foreach ($this->settings as $settingKey => $settingValue) {
			$xml->addItem(array(
				'arg' 	=> $settingKey,
				'valid'	=> 'yes',
				'title'	=> $settingKey . ' = ' . $settingValue
			));
		}

		return $xml;
	}

	protected function set($setting, $value)
	{
		$xml = new AlfredResult();
		$xml->setShared('uid', 'setting');

		if (array_key_exists($setting, $this->validOptions)) {
			$trimmedValue = trim($value);
			if ($this->languages->isAvailable($trimmedValue)) {
				$this->settings[$setting] = $trimmedValue;
				$this->saveSettings();
				$xml->addItem(array('title' => 'New default value for ' . $setting . ' is ' . $this->languages->map($trimmedValue) . ' (' . $trimmedValue . ')'));
			} else {
				$requestedLanguages = explode(',', $value);
				$validLanguages = array();
				foreach ($requestedLanguages as $languageKey) {
					$trimmedKey = trim($languageKey);
					if ($this->languages->isAvailable($trimmedKey)) {
						$validLanguages[$trimmedKey] = $this->languages->map($trimmedKey);
					}
				}
				if (count($validLanguages) > 0) {
					$checkedValue = implode(',', array_keys($validLanguages));
					$this->settings[$setting] = $checkedValue;
					$this->saveSettings();
					$xml->addItem(array('title' => 'New default value for ' . $setting . ' is ' . implode(', ', $validLanguages) . ' (' . $checkedValue . ')'));
				} else {
					$xml->addItem(array('title' => 'Invalid value ' . $value));
				}
			}

		} else {
			$xml->addItem(array('title' => 'Invalid option ' . $setting));
		}

		return $xml;
	}

	protected function loadSettings()
	{
		$this->log('loadSettings');
		$settings = null;
		$filePath = $this->getConfigFilePath();
		if (file_exists($filePath)) {
			$settings = json_decode($filePath);
		}

		$this->log($settings, 'LOADED settings');
		// Only set settings if anything is stored in config file. Otherwise use the defaults.
		if (is_array($settings)) {
			$this->log('Write settings');
			$this->settings = $settings;
		}
	}

	protected function saveSettings()
	{
		$filePath = $this->getConfigFilePath();
		file_put_contents($filePath, json_encode($this->settings));
	}

	/**
 	 * This exracts valid languages from an input string
	 *
	 * @param string $command
	 *
	 * @return array
 	 */
	protected function extractLanguages($command)
	{
		//
		// First check wether both, source and target language, are set
		//
		if (strpos($command, '>') > 0) {
			list($sourceLanguage, $targetLanguage) = explode('>', $command);
		} elseif (strpos($command, '<') > 0) {
			list($targetLanguage, $sourceLanguage) = explode('<', $command);
		} else {
			$targetLanguage = $command;
		}

		//
		// Check if the source language is valid
		//
		if (!$this->languages->isAvailable($sourceLanguage)) {
			$sourceLanguage = $this->settings['sourceLanguage'];
		}

		//
		// Check if the target language is valid
		//
		if (!$this->languages->isAvailable($targetLanguage)) {
			//
			// If not, maybe multiple target languages are defined.
			// Try to parse multiple target languages
			//
			$incomingTargetLanguages = explode(',', $targetLanguage);
			$targetLanguageList = array();
			foreach ($incomingTargetLanguages as $itl) {
				if ($this->languages->isAvailable($itl)) {
					$targetLanguageList[] = $itl;
				}
			}

			//
			// If any valid target languages are selected, write them back as csl
			// or just return the default
			//
			if (count($targetLanguageList) == 0) {
				$targetLanguage = $this->settings['targetLanguage'];
			} else {
				$targetLanguage = implode(',', $targetLanguageList);
			}
		}

		return array(strtolower($sourceLanguage), strtolower($targetLanguage));
	}

	protected function fetchGoogleTranslation($sourceLanguage, $targetLanguage, $phrase)
	{
		$url = 'http://translate.google.com/translate_a/t?client=it&text='.urlencode($phrase).'&hl=en-EN&sl='.$sourceLanguage.'&tl='.$targetLanguage.'&multires=1&ssel=0&tsel=0&sc=1&ie=UTF-8&oe=UTF-8';
		$userUrl = 'https://translate.google.com/#'.$sourceLanguage.'/'.$targetLanguage.'/'.urlencode($phrase);

		$defaults = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_USERAGENT => 'AlfredGoogleTranslateWorkflow'
		);

		$ch  = curl_init();
		curl_setopt_array($ch, $defaults);
		$out = curl_exec($ch);
		// file_put_contents('/tmp/alfred.out', $url . "\n" . $out);
		$this->log($out, $url);
		curl_close($ch);

		return json_decode($out);
	}

	protected function processGoogleResults(array $googleResults)
	{
		$xml = new AlfredResult();
		$xml->setShared('uid', 'mtranslate');

		foreach ($googleResults as $targetLanguage => $result) {
			$sourceLanguage = $result->src;

			if (isset($result->dict)) {
				$dictResults = $result->dict[0]->entry;
				if (is_array($dictResults)) {
					foreach ($dictResults as $translatedData) {

						$xml->addItem(array(
							'arg' 		=> $this->getUserURL($sourceLanguage, $targetLanguage, $translatedData->reverse_translation[0]).'|'.$translatedData->word,
							'valid'		=> 'yes',
							'title' 	=> $translatedData->word.' ('.$this->languages->map($targetLanguage).')',
							'subtitle'	=> implode(', ', $translatedData->reverse_translation).' ('.$this->languages->map($sourceLanguage).')',
							'icon'		=> $this->getFlag($targetLanguage)
						));

						//
						// If more than one target language is set, break after the first entry
						if (count($googleResults) > 1) {
							break;
						}
					}
				}
			} elseif (isset($result->sentences)) {
				foreach ($result->sentences as $sentence) {
					$xml->addItem(array(
						'arg' 		=> $this->getUserURL($sourceLanguage, $targetLanguage, $sentence->orig).'|'.$sentence->trans,
						'valid'		=> 'yes',
						'title' 	=> $sentence->trans.' ('.$this->languages->map($targetLanguage).')',
						'subtitle'	=> $sentence->orig.' ('.$this->languages->map($sourceLanguage).')',
						'icon'		=> $this->getFlag($targetLanguage)
					));
				}
			} else {
				$xml->addItem(array('title' => 'No results found'));
			}
		}

		return $xml;
	}

	protected function getUserURL($sourceLanguage, $targetLanguage, $phrase)
	{
		return 'https://translate.google.com/#'.$sourceLanguage.'/'.$targetLanguage.'/'.urlencode($phrase);
	}

	protected function getFlag($language)
	{
		$iconFilename = 'Icons/'.$language.'.png';
		if (!file_exists($iconFilename)) {
			$iconFilename = 'Icons/Unknown.png';
		}

		return $iconFilename;
	}

	protected function getConfigFilePath()
	{
		return $this->workflowsInstance->data() . '/config.json';
	}

	protected function log($data, $title = null)
	{
		if ($this->DEBUG) {
			$msg = (!empty($title) ? $title . ': ' : '') . print_r($data, TRUE);
			file_put_contents('php://stdout', $msg . "\n");
		}
	}
}
