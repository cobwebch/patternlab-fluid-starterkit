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

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * This ViewHelper generates a HTML dump of the tagged variable.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:debug>{testVariables.array}</f:debug>
 * </code>
 * <output>
 * foobarbazfoo
 * </output>
 *
 * <code title="All Features">
 * <f:debug title="My Title" maxDepth="5" blacklistedClassNames="{0:'Tx_BlogExample_Domain_Model_Administrator'}" plainText="true" ansiColors="false" inline="true" blacklistedPropertyNames="{0:'posts'}">{blogs}</f:debug>
 * </code>
 * <output>
 * [A HTML view of the var_dump]
 * </output>
 */
class CommentViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * This prevents double escaping as the output is encoded in DebuggerUtility::var_dump
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Output of this viewhelper is already escaped
     *
     * @var bool
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
    }

    /**
     * A wrapper for \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump().
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        return '<!--'. $renderChildrenClosure() . '-->';
    }
}
