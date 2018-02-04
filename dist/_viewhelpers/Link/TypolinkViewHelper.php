<?php
namespace TYPO3\CMS\Fluid\ViewHelpers\Link;

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


use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class TypolinkViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Arguments initialization
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('target', 'string', '', false, '');
        $this->registerArgument('class', 'string', '', false, '');
        $this->registerArgument('title', 'string', '', false, '');
        $this->registerArgument('additionalParams', 'string', '', false, '');
        $this->registerArgument('additionalAttributes', 'array', '', false, []);
        $this->registerArgument('useCacheHash', 'bool', '', false, false);
    }

    /**
     * Render
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed|string
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $parameter = $arguments['parameter'];

        if (!$parameter) {
            // If no link has to be rendered, the inner content will be returned as such
            return (string)$renderChildrenClosure();
        }

        $target = isset($arguments['target']) && $arguments['target'] != '' ? ' target="'.$arguments['target'].'"' : '';
        $class = isset($arguments['class']) && $arguments['class'] != '' ? ' class="'.$arguments['class'].'"' : '';
        $title = isset($arguments['title']) && $arguments['title'] != '' ? ' title="'.$arguments['title'].'"' : '';
        $additionalAttributes = $arguments['additionalAttributes'];

        // array(param1 -> value1, param2 -> value2) --> param1="value1" param2="value2" for typolink.ATagParams
        $extraAttributes = [];
        foreach ($additionalAttributes as $attributeName => $attributeValue) {
            $extraAttributes[] = $attributeName . '="' . htmlspecialchars($attributeValue) . '"';
        }
        $aTagParams = implode(' ', $extraAttributes);


        return '<a href="' . $parameter . '" ' . $target . $class . $title . $aTagParams . '>' . (string)$renderChildrenClosure() . '</a>';
    }
}
