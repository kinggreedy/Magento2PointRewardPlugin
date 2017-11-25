<?php
namespace Magento\PointRewardPromotion\Model\Quote;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->setCode('point_reward_discount');
        $this->customerSession = $customerSession;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $parentResult = parent::collect($quote, $shippingAssignment, $total);

        //load customer total point
        $discountPercentage = 0;
        $customerPoint = 0;

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $customerData = $customer->getData();
            if (isset($customerData["point_reward_customer"])) {
                $customerPoint = intval($customerData["point_reward_customer"]);
            }
        }

        /*
         * Giving customer discount based on their current tier
         * The customer must have at least 1000 point (~10$) spent to begin to get benefit
         *
         * DiscountPercentage is based on log10 of point, basically, the number of digit
         * If point > 1000000 (~10 000$ spent), discount +2%
         * If point > 100000000 (~1 000 000$ spent), discount +7%
         */

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