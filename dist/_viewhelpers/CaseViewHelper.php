<?php
namespace TYPO3\CMS\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper;

class CaseViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper
{
    /**
     * @return string the contents of this view helper if $value equals the expression of the surrounding switch view helper, otherwise an empty string
     * @throws ViewHelper\Exception
     * @api
     */
    public function render()
    {
        $value = $this->arguments['value'];
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        if (!$viewHelperVariableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            throw new ViewHelper\Exception('The "case" View helper can only be used within a switch View helper', 1368112037);
        }
        $switchExpression = $viewHelperVariableContainer->get(SwitchViewHelper::class, 'switchExpression');

        // non-type-safe comparison by intention
        if ($switchExpression == $value) {
            $viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'break', true);
            return $this->renderChildren();
        }
        return '';
    }
}