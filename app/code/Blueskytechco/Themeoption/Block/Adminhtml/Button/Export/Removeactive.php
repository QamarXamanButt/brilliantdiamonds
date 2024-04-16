<?php
namespace Blueskytechco\Themeoption\Block\Adminhtml\Button\Export;

class Removeactive extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $params = [
            'website' => $buttonBlock->getRequest()->getParam('website')
        ];

        $url = $this->getUrl("themeoption/exportoptions/removepurchasecode", $params);
        $data = [
            'id' => 'export_removepurchasecode' ,
            'label' => __('Remove'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}