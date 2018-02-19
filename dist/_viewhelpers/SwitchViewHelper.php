<?php
namespace TYPO3\CMS\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class SwitchViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper
{
    /**
     * @return string the rendered string
     * @api
     */
    public function render()
    {
        $expression = $this->arguments['expression'];
        $this->backupSwitchState();
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();

        $variableContainer->addOrUpdate(SwitchViewHelper::class, 'switchExpression', $expression);
        $variableContainer->addOrUpdate(SwitchViewHelper::class, 'break', false);

        $content = $this->retrieveContentFromChildNodes($this->viewHelperNode->getChildNodes());

        if ($variableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            $variableContainer->remove(SwitchViewHelper::class, 'switchExpression');
        }
        if ($variableContainer->exists(SwitchViewHelper::class, 'break')) {
            $variableContainer->remove(SwitchViewHelper::class, 'break');
        }

        $this->restoreSwitchState();
        return $content;
    }

    /**
     * @param NodeInterface[] $childNodes
     * @return mixed
     */
    protected function retrieveContentFromChildNodes(array $childNodes)
    {
        $content = null;
        $defaultCaseViewHelperNode = null;

        foreach ($childNodes as $childNode) {
            if ($this->isDefaultCaseNode($childNode)) {
                $defaultCaseViewHelperNode = $childNode;
            }

            if (!$this->isCaseNode($childNode)) {
                continue;
            }
            $content = $childNode->evaluate($this->renderingContext);
            if ($this->viewHelperVariableContainer->get(SwitchViewHelper::class, 'break') === true) {
                $defaultCaseViewHelperNode = null;
                break;
            }
        }

        if ($defaultCaseViewHelperNode !== null) {
            $content = $defaultCaseViewHelperNode->evaluate($this->renderingContext);
        }
        return $content;
    }

    /**
     * @param NodeInterface $node
     * @return boolean
     */
    protected function isDefaultCaseNode(NodeInterface $node)
    {
        return ($node instanceof ViewHelperNode && $node->getViewHelperClassName() === DefaultCaseViewHelper::class);
    }

    /**
     * @param NodeInterface $node
     * @return boolean
     */
    protected function isCaseNode(NodeInterface $node)
    {
        return ($node instanceof ViewHelperNode && $node->getViewHelperClassName() === CaseViewHelper::class);
    }

    /**
     * Backups "switch expression" and "break" state of a possible parent switch ViewHelper to support nesting
     *
     * @return void
     */
    protected function backupSwitchState()
    {
        if ($this->renderingContext->getViewHelperVariableContainer()->exists(SwitchViewHelper::class, 'switchExpression')) {
            $this->backupSwitchExpression = $this->renderingContext->getViewHelperVariableContainer()->get(SwitchViewHelper::class, 'switchExpression');
        }
        if ($this->renderingContext->getViewHelperVariableContainer()->exists(SwitchViewHelper::class, 'break')) {
            $this->backupBreakState = $this->renderingContext->getViewHelperVariableContainer()->get(SwitchViewHelper::class, 'break');
        }
    }

    /**
     * Restores "switch expression" and "break" states that might have been backed up in backupSwitchState() before
     *
     * @return void
     */
    protected function restoreSwitchState()
    {
        if ($this->backupSwitchExpression !== null) {
            $this->renderingContext->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'switchExpression', $this->backupSwitchExpression);
        }
        if ($this->backupBreakState !== false) {
            $this->renderingContext->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', true);
        }
    }
}
