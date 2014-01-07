Google Translate Alfred Workflow
=============================

Version 2.1

## License

The MIT License (MIT)

Copyright (c) 2013-2014 Thomas Hempel <thomas@scriptme.de>

### Attention!

This version 2 is a complete rewrite! It works differently than before while keeping the basic functionality, it is not longer bound to english and german. It know let Google decide which language is the source and you can define what the target language is in your query.

A workflow for Alfred 2 that implements translation from any language to any other language known to Google.

It's based on PHP and not very complex to understand.

## How to install
Just download and double click the [workflow file](https://github.com/thomashempel/AlfredGoogleTranslateWorkflow/raw/master/GoogleTranslate.alfredworkflow)
Say "yes" to import it into Alfred. Done!

## How to use
Open Alfred and type "translate" followed by the shortcode for the target language like "en" (english), "de" (german) or "it" (italian). You can find a complete list in the next section.

Alfred will show all the results that Google returned. Select the one that fits your situation best and the translated phrase will be copied to the clipboard.

Normally the workflow will let Google decide which language you typed in. This doesn't fit in every case. For example if the word is ambiguous, spelled wrong or just means something different in another language.
Since version 2.1 you can define from and to which language you want to translate. You do this via the ">" or "<" operator and the respective language codes. Here a few examples:

    translate de>en Haus		// Will translate "Haus" from german to english.
    translate de>fr Auto		// Will translate "Auto" from german to french
    translate fr<en bottle		// Will translate "bottle" from english to french

As you can see, it's not pre-defined which language comes first. The source language or the target language. You define it by yourself with the operator ">" or "<" depending on what you prefer.

You can also leave the operator out at all. The behavior will be as in previous versions. You can only define the target language and we will let Google decide in which language you typed your search word in.

When Google delivered the result, you can just copy the one you want to the clipboard by selecting it and pressing enter.

## Languages

* auto = Automatic
* af = Afrikaans
* sq = Albanian
* ar = Arabic
* hy = Armenian
* az = Azerbaijani
* eu = Basque
* be = Belarusian
* bn = Bengali
* bg = Bulgarian
* ca = Catalan
* zh-CN = Chinese (Simplified)
* zh-TW = Chinese (Traditional)
* hr = Croatian
* cs = Czech
* da = Danish
* nl = Dutch
* en = English
* eo = Esperanto
* et = Estonian
* tl = Filipino
* fi = Finnish
* fr = French
* gl = Galician
* ka = Georgian
* de = German
* el = Greek
* gu = Gujarati
* ht = Haitian Creole
* iw = Hebrew
* hi = Hindi
* hu = Hungarian
* is = Icelandic
* id = Indonesian
* ga = Irish
* it = Italian
* ja = Japanese
* kn = Kannada
* km = Khmer
* ko = Korean
* lo = Lao
* la = Latin
* lv = Latvian
* lt = Lithuanian
* mk = Macedonian
* ms = Malay
* mt = Maltese
* no = Norwegian
* fa = Persian
* pl = Polish
* pt = Portuguese
* ro = Romanian
* ru = Russian
* sr = Serbian
* sk = Slovak
* sl = Slovenian
* es = Spanish
* sw = Swahili
* sv = Swedish
* ta = Tamil
* te = Telugu
* th = Thai
* tr = Turkish
* uk = Ukrainian
* ur = Urdu
* vi = Vietnamese
* cy = Welsh
* yi = Yiddish

## Screenshots
This is how it should look like:

<img src="GoogleTranslate1.png" />
<img src="GoogleTranslate2.png" />
<img src="GoogleTranslate3.png" />
<img src="GoogleTranslate4.png" />

## The end

Thanks for using the workflow!
Feel free to fork and/or make suggestions.

Best wishes,
Thomas
