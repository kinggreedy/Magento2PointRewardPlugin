<?php
namespace Magento\PointRewardPromotion\Controller\CustomerData;

class Customer extends \Magento\Customer\CustomerData\Customer
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Helper\View $customerViewHelper
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($currentCustomer, $customerViewHelper);
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = parent::getSectionData();

        $customer = $this->currentCustomer->getCustomer();
        $customerData = $this->customerFactory->create()->load($customer->getId())->getData();

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
