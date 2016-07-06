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

require './alfred.php';
require './workflows.php';
require './languages.php';

class GoogleTranslateWorkflowBase
{

    protected $DEBUG = false;

    protected $workflowsInstance;

    protected $languages;

    protected $settings;

    protected $defaultSettings = array('sourceLanguage' => 'auto', 'targetLanguage' => 'en');

	protected $validOptions = array('sourceLanguage' => 'Source Language', 'targetLanguage' => 'Target Language');

    public function __construct()
    {
        $this->workflowsInstance = new Workflows();
        $this->languages = new Languages();

        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->log('loadSettings');
        $settings = null;
        $filePath = $this->getConfigFilePath();
        if (file_exists($filePath)) {
            $settings = json_decode(file_get_contents($filePath), true);
            // file_put_contents('/tmp/alfed.log', 'LOADED: ' . print_r($settings, true));
        }

        // Only set settings if anything is stored in config file. Otherwise use the defaults.
        if (is_array($settings)) {
            $this->settings = $settings;

        } else {
            $this->settings = $this->defaultSettings;

        }

        // file_put_contents('/tmp/alfed.log', 'FINAL: ' . print_r($this->settings, true));
    }

    protected function saveSettings()
	{
		$filePath = $this->getConfigFilePath();
        file_put_contents($filePath, json_encode($this->settings));
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

?>
