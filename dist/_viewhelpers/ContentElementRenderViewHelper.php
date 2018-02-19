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

use Cobweb\FluidPatternEngine\Traits\FluidLoader;
use Cobweb\FluidPatternEngine\Resolving\PatternLabTemplatePaths;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\View\TemplateView;
use PatternLab\Config;


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
class ContentElementRenderViewHelper extends AbstractViewHelper
{

    use FluidLoader;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var TemplateView
     */
    protected $templateView;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Sets the paths for the current template
     */
    public function getTemplatePaths(){
        $this->templatePaths = new PatternLabTemplatePaths();
        $this->templatePaths->setFormat(Config::getOption("patternExtension"));
        $this->templatePaths->setLayoutRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Layouts/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Layouts/ContentElements/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . '_layouts/'
        ]);
        $this->templatePaths->setPartialRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Partials/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Partials/ContentElements/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . '_patterns/'
        ]);
        $this->templatePaths->setTemplateRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Templates/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Templates/',
        ]);
    }

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('templateName', 'string',
            'the name of the Template we want to render', true);
        $this->registerArgument('arguments', 'array',
            'Array of variables to be transferred. Use {_all} for all variables', false, []);

    }

    /**
     * Renders the TypoScript object in the given TypoScript setup path.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string the content of the rendered TypoScript object
     */
    public function render()
    {
        $templateName = $this->arguments['templateName'];

        $this->view->assignMultiple($this->arguments['arguments']);
        $template = $this->view->getTemplatePaths()->getTemplateSource('ContentElements',$templateName);
        $this->view->getTemplatePaths()->setTemplateSource($template);

        return $this->view->render();
    }


}
