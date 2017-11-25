<?php
namespace Magento\PointRewardPromotion\Controller\CustomerData;

use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * {@inheritdoc}
     */
    public function getItemData(Item $item)
    {
        $data = parent::getItemData($item);

        $product = $this->item->getProduct();
        $pointReward = intval($product->getCustomAttribute('point_reward')->getValue()) * $this->item->getQty();
        $data['product_point_reward'] = "Reward: $pointReward P";
        $data['product_point_reward_value'] = $pointReward;

        return $data;
    }
}
