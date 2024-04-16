<?php

namespace WeltPixel\GA4\Plugin;

class WishlistAddToCart
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4ServerSideHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4ServerSideHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $helper,
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4ServerSideHelper,
        \Magento\Checkout\Model\Session $checkoutSession
        )
    {
        $this->helper = $helper;
        $this->ga4ServerSideHelper = $ga4ServerSideHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Wishlist\Model\Item $subject
     * @param $result
     * @return bool
     * @throws \Magento\Catalog\Model\Product\Exception
     */
    public function afterAddToCart(
        \Magento\Wishlist\Model\Item $subject,
        $result)
    {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        if (($this->ga4ServerSideHelper->isServerSideTrakingEnabled() && $this->ga4ServerSideHelper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_ADD_TO_CART)
            && $this->ga4ServerSideHelper->isDataLayerEventDisabled())) {
            return $result;
        }

        if ($result) {
            $buyRequest = $subject->getBuyRequest();
            $qty = $buyRequest->getData('qty');
            $product = $subject->getProduct();

            /** multiple products can be added at once, so they are merged */
            $currentAddToCartData = $this->_checkoutSession->getGA4AddToCartData();
            $addToCartPushData = $this->helper->addToCartPushData($qty, $product, $buyRequest, true);

            $newAddToCartPushData = $this->helper->mergeAddToCartPushData($currentAddToCartData, $addToCartPushData);
            $this->_checkoutSession->setGA4AddToCartData($newAddToCartPushData);
        }

        return $result;
    }


}
