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

require "./languages.php";
require "./alfred.php";

function parseRequest($request)
{
	$requestParts = explode(' ', $request);
	$languageSelector = array_shift($requestParts);
	$phrase = implode(' ', $requestParts);

	$targetLanguage = $languageSelector;
	$sourceLanguage = 'auto';

	if (strpos($languageSelector, '>') > 0) {
		list($sourceLanguage, $targetLanguage) = explode('>', $languageSelector);
	} elseif (strpos($languageSelector, '<') > 0) {
		list($targetLanguage, $sourceLanguage) = explode('<', $languageSelector);
	}

	return array($phrase, $sourceLanguage, $targetLanguage);
}

/**
 * The method that is called by Alfred
 *
 * @param string $request	User input string
 */
function googleTranslate($request)
{
	list($phrase, $sourceLanguage, $targetLanguage) = parseRequest($request);

	$url = 'http://translate.google.com/translate_a/t?client=it&text='.urlencode($phrase).'&hl=en-EN&sl='.$sourceLanguage.'&tl='.$targetLanguage.'&multires=1&ssel=0&tsel=0&sc=1&ie=UTF-8&oe=UTF-8';
	$userUrl = 'https://translate.google.com/#'.$sourceLanguage.'/'.$targetLanguage.'/'.urlencode($phrase);

	$defaults = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		CURLOPT_FRESH_CONNECT => true
	);

	$ch  = curl_init();
	curl_setopt_array($ch, $defaults);
	$out = curl_exec($ch);
	curl_close($ch);


	$xml = new AlfredResult();
	$xml->setShared('uid', 'mtranslate');

	$iconFilename = 'Icons/'.$targetLanguage.'.png';
	if (!file_exists($iconFilename)) {
		$iconFilename = 'Icons/Unknown.png';
	}
	$xml->setShared('icon', $iconFilename);

	//$stderr = fopen('php://stderr', 'w');
	//fwrite($stderr, $out);
	//fclose($stderr);
	
	$json = json_decode($out);
	$sourceLanguage = $json->src;

	if (isset($json->dict)) {
		$googleResults = $json->dict[0]->entry;
		if (is_array($googleResults)) {
			foreach ($googleResults as $translatedData) {
				$xml->addItem(array(
					'arg' 		=> $userUrl.'|'.$translatedData->word,
					'valid'		=> 'yes',
					'title' 	=> $translatedData->word.' ('.languageMap($targetLanguage).')',
					'subtitle'	=> implode(', ', $translatedData->reverse_translation).' ('.languageMap($sourceLanguage).')',
				));
			}
		}
	} elseif (isset($json->sentences)) {
		foreach ($json->sentences as $sentence) {
			$xml->addItem(array(
				'arg' 		=> $userUrl.'|'.$sentence->trans,
				'valid'		=> 'yes',
				'title' 	=> $sentence->trans.' ('.languageMap($targetLanguage).')',
				'subtitle'	=> $sentence->orig.' ('.languageMap($sourceLanguage).')',
			));
		}
	} else {
		$xml->addItem(array('title' => 'No results found'));
	}
	
	// var_dump($xml);

	echo $xml;
}
