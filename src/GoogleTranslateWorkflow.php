<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013-2016 Thomas Hempel <thomas@scriptme.de>
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

require 'vendor/autoload.php';
require './GoogleTranslateWorkflowBase.php';

use Stichoza\GoogleTranslate\TranslateClient;

class GoogleTranslateWorkflow extends GoogleTranslateWorkflowBase
{

	public function process($request)
	{
		$this->log($request);

		$requestParts = explode(' ', $request);
		$command = array_shift($requestParts);
		$phrase = (count($requestParts) > 0) ? implode(' ', $requestParts) : $command;
		$result = '';

		if (strlen($phrase) < 3) {
			return $this->getSimpleMessage('More input needed', 'The word has to be longer than 2 characters');
		}

		list($sourceLanguage, $targetLanguage) = $this->extractLanguages($command);
		$this->log(array($sourceLanguage, $targetLanguage));
		$targetLanguages = explode(',', $targetLanguage);
		$googleResults = array();
		foreach ($targetLanguages as $targetLanguage) {
			$googleResults[$targetLanguage] = $this->fetchGoogleTranslation($sourceLanguage, $targetLanguage, $phrase);
		}
		$this->log($googleResults, 'Google Results');
		$result = $this->processGoogleResults($googleResults, $phrase, $sourceLanguage);

		$this->log($result);
		return $result;
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
		$client = new TranslateClient(($sourceLanguage == 'auto') ? null : $sourceLanguage, $targetLanguage);
		$response = $client->getResponse($phrase);
		return $response;
	}

	protected function processGoogleResults(array $googleResults, $sourcePhrase, $sourceLanguage)
	{
		$xml = new AlfredResult();
		$xml->setShared('uid', 'mtranslate');

		if (count($googleResults) > 0) {
			foreach ($googleResults as $targetLanguage => $result) {
				if (is_array($result)) {
					$xml->addItem(array(
						'arg' 		=> $this->getUserURL($result[1], $targetLanguage, $sourcePhrase).'|'.$result[0],
						'valid'		=> 'yes',
						'title' 	=> $result[0],
						'subtitle'	=> $sourcePhrase.' ('.$this->languages->map($result[1]).')',
						'icon'		=> $this->getFlag($targetLanguage)
					));
				} else {
					$xml->addItem(array(
						'arg' 		=> $this->getUserURL($sourceLanguage, $targetLanguage, $sourcePhrase).'|'.$result,
						'valid'		=> 'yes',
						'title' 	=> $result,
						'subtitle'	=> $sourcePhrase.' ('.$this->languages->map($sourceLanguage).')',
						'icon'		=> $this->getFlag($targetLanguage)
					));
				}

			}

		} else {
			$xml->addItem(array('title' => 'No results found'));

		}

		return $xml;
	}

	protected function getSimpleMessage($message, $subtitle = '')
	{
		$xml = new AlfredResult();
		$xml->setShared('uid', 'mtranslate');
		$xml->addItem(array('title' => $message, 'subtitle' => $subtitle));
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

}
