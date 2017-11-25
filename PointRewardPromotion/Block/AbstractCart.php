<?php
namespace Magento\PointRewardPromotion\Block;

class AbstractCart extends \Magento\Checkout\Block\Cart\AbstractCart
{
    public function afterGetItemRenderer(\Magento\Checkout\Block\Cart\AbstractCart $subject, $result)
    {
        $result->setTemplate('Magento_PointRewardPromotion::cart/item/default.phtml');
        return $result;
    }
}