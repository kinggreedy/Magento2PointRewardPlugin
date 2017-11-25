<?php
namespace Magento\PointRewardPromotion\Block;

class CheckoutCartConfigurableItemRenderer extends \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable
{
    /**
     * @return int
     */
    public function getProductCustomAttribute()
    {
        return intval($this->getChildProduct()->getCustomAttribute('point_reward')->getValue());
    }

    /**
     * @return int
     */
    public function getProductPointRewardField()
    {
        return $this->getProductCustomAttribute() * $this->getItem()->getQty();
    }
}
