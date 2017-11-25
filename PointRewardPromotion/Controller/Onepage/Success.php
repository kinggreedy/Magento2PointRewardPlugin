<?php
namespace Magento\PointRewardPromotion\Controller\Onepage;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;

class Success extends \Magento\Checkout\Controller\Onepage\Success
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        $objectManager = $this->_objectManager;
        if ($objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            $orderId = $session->getLastOrderId();

            /** @var Order $order */
            $order = $objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
            $orderItems = $order->getAllVisibleItems();
            $customerId = $order->getCustomerId();

            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $objectManager->create(\Magento\Customer\Model\Customer::class)->load($customerId);
            $customerData = $customer->getData();
            $totalPoint = 0;
            if (isset($customerData["point_reward_customer"])) {
                $totalPoint = intval($customerData["point_reward_customer"]);
            }
            foreach ($orderItems as $item) {
                $productSku = $item->getSku();
                /** @var Product $product */
                $productId = $item->getProduct()->getIdBySku($productSku);
                $product = $objectManager->create(\Magento\Catalog\Model\Product::class)->load($productId);
                $totalPoint += intval($product->getCustomAttribute('point_reward')->getValue()) * $item->getQtyOrdered();
            }
            $customer->setData('point_reward_customer', $totalPoint);
            $customerResource = $objectManager->create(\Magento\Customer\Model\ResourceModel\CustomerFactory::class)->create();
            $customerResource->saveAttribute($customer, 'point_reward_customer');
        }
        return parent::execute();
    }
}
