<?php

namespace Blueskytechco\Themeoption\Block\Adminhtml\System\Config;

class DateTime extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $timezone;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat($this->timezone->getDateFormat());
        $element->setTimeFormat($this->timezone->getTimeFormat());
        $element->setShowsTime(true);
        return parent::render($element);
    }
}