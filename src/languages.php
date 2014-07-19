<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Wilker Lúcio
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

$LANGUAGE_MAP = array(
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

function languageMap($key = null)
{
	global $LANGUAGE_MAP;

	return is_null($key) ? $LANGUAGE_MAP['auto'] : $LANGUAGE_MAP[$key];
}