<?php
namespace Magento\PointRewardPromotion\Block;

class CheckoutCartItemRenderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * @return int
     */
    public function getProductCustomAttribute()
    {
        return intval($this->getProduct()->getCustomAttribute('point_reward')->getValue());
    }

    /**
     * @return int
     */
    public function getProductPointRewardField()
    {
        return $this->getProductCustomAttribute() * $this->getItem()->getQty();
    }
}
