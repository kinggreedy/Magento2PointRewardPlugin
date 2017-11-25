<?php
namespace Magento\PointRewardPromotion\Controller\CustomerData;

class Customer extends \Magento\Customer\CustomerData\Customer
{
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = parent::getSectionData();

        $customer = $this->currentCustomer->getCustomer();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerData = $objectManager->create(\Magento\Customer\Model\Customer::class)->load($customer->getId())->getData();
        $customerPoint = 0;
        if (isset($customerData["point_reward_customer"])) {
            $customerPoint = intval($customerData["point_reward_customer"]);
        }

        $discountPercentage = 0;
        if ($customerPoint > 100000000) {
            $discountPercentage = floor(log10($customerPoint) + 7);
        }
        if ($customerPoint > 1000000) {
            $discountPercentage = floor(log10($customerPoint) + 2);
        }
        if ($customerPoint > 1000) {
            $discountPercentage = floor(log10($customerPoint));
        }
        if ($discountPercentage < 1) {
            $discountPercentage = 0;
        }

        $data['point_reward_customer'] = "$discountPercentage% ($customerPoint P)";
        return $data;
    }
}
