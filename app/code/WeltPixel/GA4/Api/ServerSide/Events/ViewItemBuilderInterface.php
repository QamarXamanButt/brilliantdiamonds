<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemBuilderInterface
{
    /**
     * @param $productId
     * @param $variant
     * @return null|ViewItemInterface
     */
    function getViewItemEvent($productId, $variant = '');
}
