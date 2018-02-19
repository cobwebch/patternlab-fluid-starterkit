<?php
namespace TYPO3\CMS\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper;

class DefaultCaseViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\DefaultCaseViewHelper
{
    /**
     * @return string the contents of this view helper if no other "Case" view helper of the surrounding switch view helper matches
     * @throws ViewHelper\Exception
     * @api
     */
    public function render()
    {
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        if (!$viewHelperVariableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            throw new ViewHelper\Exception('The "default case" View helper can only be used within a switch View helper', 1368112037);
        }
        return $this->renderChildren();
    }
}