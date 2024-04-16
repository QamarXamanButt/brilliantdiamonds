<?php

declare(strict_types=1);

namespace TA\InvalidAddressFix\Plugin;

use Magento\Quote\Model\Quote;

class InvalidAddress
{
    /**
     * @param Quote $subject
     * @param $result
     * @return Quote
     */
    public function afterBeforeSave(Quote $subject, $result): Quote
    {
        if ($result->getCustomerId()) {
            $result->setCustomerIsGuest(false);
        }

        return $result;
    }
}