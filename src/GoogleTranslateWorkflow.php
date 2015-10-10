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

require './GoogleTranslateWorkflowBase.php';

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
		$this->log($googleResults);
		$result = $this->processGoogleResults($googleResults);

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
