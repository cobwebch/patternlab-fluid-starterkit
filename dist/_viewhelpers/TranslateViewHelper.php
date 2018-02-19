<?php

namespace TYPO3\CMS\Fluid\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;


/**
 * This ViewHelper renders CObjects from the global TypoScript configuration.
 * NOTE: You have to ensure proper escaping (htmlspecialchars/intval/etc.) on your own!
 *
 * = Examples =
 *
 * <code title="Render lib object">
 * <f:cObject typoscriptObjectPath="lib.someLibObject" />
 * </code>
 * <output>
 * rendered lib.someLibObject
 * </output>
 *
 * <code title="Specify cObject data & current value">
 * <f:cObject typoscriptObjectPath="lib.customHeader" data="{article}" currentValueKey="title" />
 * </code>
 * <output>
 * rendered lib.customHeader. data and current value will be available in TypoScript
 * </output>
 *
 * <code title="inline notation">
 * {article -> f:cObject(typoscriptObjectPath: 'lib.customHeader')}
 * </code>
 * <output>
 * rendered lib.customHeader. data will be available in TypoScript
 * </output>
 */
class TranslateViewHelper extends AbstractViewHelper
{
    protected $translationMap = null;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('key', 'string',
            'key to find the string in the translate.json file', true);
        $this->registerArgument('arguments', 'array','Variable to pass', false);
        $this->registerArgument('extensionName', 'string','Name of the extension', false);
    }

    /**
     * Renders the TypoScript object in the given TypoScript setup path.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string the content of the rendered TypoScript object
     */
    public function render()
    {

        $args = is_array($this->arguments['arguments']) ? $this->arguments['arguments'] : array();
        $key = $this->arguments['key'];
        if ($this->translationMap === null) {
            $translationFilePath = __DIR__.'/../_data/translation.json';
            if (!file_exists($translationFilePath)) {
                return 'Please provide a valid translation file in : `'. str_replace(__DIR__.'/../', '', $translationFilePath) .'`';
            }

            $this->translationMap = json_decode(file_get_contents($translationFilePath), true);

            if (!isset($this->translationMap['translation'])) {
                return 'Please provide a key translation as the only object of the file, eg. : {"translation": { "search": "Recherche" }}';
            }
        }
        return isset($this->translationMap['translation'][$key]) ? sprintf($this->translationMap['translation'][$key], ...$args) : 'Unknown translation';
    }
}
