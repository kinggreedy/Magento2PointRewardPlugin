<?php
namespace Magento\PointRewardPromotion\Model\Quote;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $eventManager;
    protected $validator;
    protected $storeManager;
    protected $priceCurrency;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode('point_reward_discount');
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $parentResult = parent::collect($quote, $shippingAssignment, $total);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);

        //load customer total point
        $discountPercentage = 0;
        $customerPoint = 0;

        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
            $customerData = $customer->getData();
            if (isset($customerData["point_reward_customer"])) {
                $customerPoint = intval($customerData["point_reward_customer"]);
            }
        }

        if ($customerPoint > 100000000) {
            $discountPercentage = floor(log10($customerPoint) + 7) / 100;
        }
        if ($customerPoint > 1000000) {
            $discountPercentage = floor(log10($customerPoint) + 2) / 100;
        }
        if ($customerPoint > 1000) {
            $discountPercentage = floor(log10($customerPoint)) / 100;
        }

        if ($discountPercentage < 0.01) {
            return $parentResult;
        }

        //apply discount
        $label = round($discountPercentage * 100) . "% Reward Discount";
        $totalAmount = $total->getSubtotal();
        $totalAmount = $totalAmount * $discountPercentage;

        $discountAmount = 0 - $totalAmount;
        $appliedCartDiscount = 0;

        if ($total->getDiscountDescription()) {
            $appliedCartDiscount = $total->getDiscountAmount();
            $discountAmount = $total->getDiscountAmount() + $discountAmount;
            $label = $total->getDiscountDescription() . ', ' . $label;
        }

        $total->setDiscountDescription($label);
        $total->setDiscountAmount($discountAmount);
        $total->setBaseDiscountAmount($discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

        if (isset($appliedCartDiscount)) {
            $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
            $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
        } else {
            $total->addTotalAmount($this->getCode(), $discountAmount);
            $total->addBaseTotalAmount($this->getCode(), $discountAmount);
        }
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $description = $total->getDiscountDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Point Reward Discount');
    }
}