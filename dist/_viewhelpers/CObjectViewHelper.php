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
use TYPO3Fluid\Fluid\Exception;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableInterface;
use TYPO3Fluid\Fluid\View\TemplatePaths;


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
class CObjectViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('typoscriptObjectPath', 'string',
            'the TypoScript setup path of the TypoScript object to render', true);
        $this->registerArgument('data', 'mixed',
            'the data to be used for rendering the cObject. Can be an object, array or string. If this argument is not set, child nodes will be used');
        $this->registerArgument('currentValueKey', 'string', 'currentValueKey');
        $this->registerArgument('table', 'string',
            'the table name associated with "data" argument. Typically tt_content or one of your custom tables. This argument should be set if rendering a FILES cObject where file references are used, or if the data argument is a database record.',
            false, '');
    }

    /**
     * Renders the TypoScript object in the given TypoScript setup path.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string the content of the rendered TypoScript object
     */
    public function render()
    {
        $ts = $this->arguments['typoscriptObjectPath'];
        $tsParts = explode('.', $ts);
        $page = isset($this->arguments['data']['pageUid']) ? $this->arguments['data']['pageUid'] : false;
        $colPos = (isset($this->arguments['data']['colPos'] ) && $this->arguments['data']['colPos'] !== false) ? intval($this->arguments['data']['colPos']) : false;
        $args = array_merge_recursive($this->arguments, $this->templateVariableContainer->getAll());

        /**
         * On tente d'afficher le contenu d'une colonne
         * On va le chercher dans le répertoire 03-pageContent/ColsContents
         * Le fichier doit être `{nomDuTemplate}-{colPos}.fluid`, par exemple SpecialStart-0.fluid
         */
        if ($tsParts[0] === 'lib' && ($tsParts[1] === 'dynamicContent' || $tsParts[1] === 'dynamicContentSlide')) {
            // On tente d'afficher du contenu pour le colPos courant de la page courante
            if ($this->partialExists("03-pageContent/ColsContents/" . $page . "-" . $colPos)) {
                return $this->getContent("03-pageContent/ColsContents/" . $page . "-" . $colPos, $args);
            } // Pas de contenu trouvé, donc on tente d'afficher du contenu par défaut pour le colPos courant
            elseif ($this->partialExists("03-pageContent/ColsContents/DummyContent-" . $colPos)) {
                return $this->getContent("03-pageContent/ColsContents/DummyContent-" . $colPos, $args);
            } // Toujours pas de contenu, on affiche donc le contenu de page par défaut
            else {
                return $this->getContent("03-pageContent/ColsContents/DummyContent", $args);
            }
        }


        /**
         * On tente d'afficher le contenu d'un plugin
         * On va le chercher dans le répertoire 03-pageContent/PluginContents
         * Le fichier doit être `{cType}.fluid`, par exemple mailform.fluid
         */
        if ($tsParts[0] === 'tt_content') {
            $plugin = $tsParts[1];
            // Il y a peut-être plusieurs types de ce plugin
            if (isset($tsParts[3])) {
                $plugin .= '-' . $tsParts[3];
            }
            // On tente d'afficher du contenu par défaut pour le plugin courant
            if ($this->partialExists("03-pageContent/PluginContents/" . $plugin)) {
                return $this->getContent("03-pageContent/PluginContents/" . $plugin, $args);
            } else {
                // Pas de contenu trouvé, on affiche donc le contenu de plugin par défaut
                return $this->getContent("03-pageContent/PluginContents/DummyPlugin", $args);
            }
        }


        /**
         * On tente d'afficher le contenu d'en librairie TypoScript
         * On va le chercher dans le répertoire 03-pageContent/LibContents
         * Le fichier doit être `{libpath}.fluid`, par exemple lib.page.title correspond
         * à 03-pageContent/LibContents/page_title.fluid (les points sont remplacés par '_'
         */
        $tsType = array_shift($tsParts);
        if ($tsType === 'lib') {
            // Il y a peut-être plusieurs types de ce plugin
            $libpath = implode('_', $tsParts);
            // On tente d'afficher du contenu par défaut pour le plugin courant
            if ($this->partialExists("03-pageContent/LibContents/" . $libpath)) {
                return $this->getContent("03-pageContent/LibContents/" . $libpath, $args);
            }
        }

        return '';
    }

    /**
     * Indique si un fichier Partial existe pour le nom donné
     * @param $partialName
     * @return bool
     */
    protected function partialExists($partialName)
    {
        /** @var TemplatePaths $templatePaths */
        $templatePaths = $this->renderingContext->getTemplatePaths();
        $paths = $templatePaths->getPartialRootPaths();
        foreach ($paths as $path) {
            if (file_exists($path . $partialName . '.' . $templatePaths->getFormat())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tries to get a content from a partial
     * @param string $partialName
     * @param array $arguments
     * @return string|boolean false if no partial has been found
     */
    protected function getContent($partialName, $arguments)
    {
        /** @var AbstractTemplateView $view */
        $view = $this->renderingContext->getViewHelperVariableContainer()->getView();
        try {
            $content = $view->renderPartial(
                $partialName,
                null,
                $arguments
            );
            return $content;
        } catch (Exception $e) {
            return $e;
        }
    }
}
