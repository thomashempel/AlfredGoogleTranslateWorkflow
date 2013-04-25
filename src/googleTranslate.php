<?php

function googleTranslate($request, $sourceLanguage = 'auto', $targetLanguage = NULL)
{
	$knownLanguages = array(
		'auto' => 'Automatic',
		'af' => 'Afrikaans',
		'sq' => 'Albanian',
		'ar' => 'Arabic',
		'hy' => 'Armenian',
		'az' => 'Azerbaijani',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bg' => 'Bulgarian',
		'ca' => 'Catalan',
		'zh-CN' => 'Chinese (Simplified)',
		'zh-TW' => 'Chinese (Traditional)',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'nl' => 'Dutch',
		'en' => 'English',
		'eo' => 'Esperanto',
		'et' => 'Estonian',
		'tl' => 'Filipino',
		'fi' => 'Finnish',
		'fr' => 'French',
		'gl' => 'Galician',
		'ka' => 'Georgian',
		'de' => 'German',
		'el' => 'Greek',
		'gu' => 'Gujarati',
		'ht' => 'Haitian Creole',
		'iw' => 'Hebrew',
		'hi' => 'Hindi',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'id' => 'Indonesian',
		'ga' => 'Irish',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'kn' => 'Kannada',
		'km' => 'Khmer',
		'ko' => 'Korean',
		'lo' => 'Lao',
		'la' => 'Latin',
		'lv' => 'Latvian',
		'lt' => 'Lithuanian',
		'mk' => 'Macedonian',
		'ms' => 'Malay',
		'mt' => 'Maltese',
		'no' => 'Norwegian',
		'fa' => 'Persian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ro' => 'Romanian',
		'ru' => 'Russian',
		'sr' => 'Serbian',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'es' => 'Spanish',
		'sw' => 'Swahili',
		'sv' => 'Swedish',
		'ta' => 'Tamil',
		'te' => 'Telugu',
		'th' => 'Thai',
		'tr' => 'Turkish',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'vi' => 'Vietnamese',
		'cy' => 'Welsh',
		'yi' => 'Yiddish'
	);

	if ($targetLanguage == NULL) {
		$requestParts = explode(' ', $request);
		$targetLanguage = $requestParts[0];
		array_shift($requestParts);
		$phrase = implode(' ', $requestParts);
	} else {
		$phrase = $request;
	}

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

	$result = '<?xml version="1.0" encoding="utf-8"?><items>';

	$json = json_decode($out);
	$sourceLanguage = $json->src;
	
	if (isset($json->dict)) {
		$googleResults = $json->dict[0]->entry;
		if (is_array($googleResults)) {
			foreach ($googleResults as $translatedData) {
				$result .= '<item uid="mtranslate" arg="'.$translatedData->word.'">';
				$result .= '<title>'.$translatedData->word.' ('.$knownLanguages[$targetLanguage].')</title>';
				$result .= '<subtitle>'.implode(', ', $translatedData->reverse_translation).' ('.$knownLanguages[$sourceLanguage].')</subtitle>';
				$result .= '<icon>Icons/'.$targetLanguage.'.png</icon>';
				$result .= '</item>';
			}
		}
	} elseif (isset($json->sentences)) {
		foreach ($json->sentences as $sentence) {
			$result .= '<item uid="mtranslate" arg="'.$sentence->trans.'">';
			$result .= '<title>'.$sentence->trans.' ('.$knownLanguages[$targetLanguage].')</title>';
			$result .= '<subtitle>'.$sentence->orig.' ('.$knownLanguages[$sourceLanguage].')</subtitle>';
			$result .= '<icon>Icons/'.$targetLanguage.'.png</icon>';
			$result .= '</item>';
		}
	} else {
		$result .= '<item uid="mtranslate">';
		$result .= '<title>No results found</title>';
		$result .= '</item>';
	}

	$result .= '</items>';
	echo $result;
}

// googleTranslate('über', 'auto', 'en');

?>
