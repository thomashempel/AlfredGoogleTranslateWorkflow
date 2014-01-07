<?php

require "./languages.php";
require "./alfred.php";

function parseRequest($request)
{
	$requestParts = explode(' ', $request);
	$targetLanguage = array_shift($requestParts);
	$phrase = implode(' ', $requestParts);

	return array($phrase, 'auto', $targetLanguage);
}

function googleTranslate($request)
{
	list($phrase, $sourceLanguage, $targetLanguage) = parseRequest($request);

	$url = 'http://translate.google.com.br/translate_a/t?client=p&text='.urlencode($phrase).'&hl=en-EN&sl='.$sourceLanguage.'&tl='.$targetLanguage.'&multires=1&ssel=0&tsel=0&sc=1&ie=UTF-8&oe=UTF-8';

	$defaults = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url,
		CURLOPT_FRESH_CONNECT => true
	);

	$ch  = curl_init();
	curl_setopt_array($ch, $defaults);
	$out = curl_exec($ch);
	$err = curl_error($ch);
	curl_close($ch);

	$xml = new AlfredResult();
	$xml->setShared("uid", "mtranslate");
	$xml->setShared("icon", "Icons/{$targetLanguage}.png");

	$json = json_decode($out);
	$sourceLanguage = $json->src;
	
	if (isset($json->dict)) {
		$googleResults = $json->dict[0]->entry;
		if (is_array($googleResults)) {
			foreach ($googleResults as $translatedData) {
				$xml->addItem(array(
					'arg' 		 => $translatedData->word,
					'title' 	 => $translatedData->word.' ('.languageMap($targetLanguage).')',
					'subtitle' => implode(', ', $translatedData->reverse_translation).' ('.languageMap($sourceLanguage).')'
				));
			}
		}
	} elseif (isset($json->sentences)) {
		foreach ($json->sentences as $sentence) {
			$xml->addItem(array(
				'arg' 		 => $sentence->trans,
				'title' 	 => $sentence->trans.' ('.languageMap($targetLanguage).')',
				'subtitle' => $sentence->orig.' ('.languageMap($sourceLanguage).')'
			));
		}
	} else {
		$xml->addItem(array('title' => 'No results found'));
	}

	echo $xml;
}