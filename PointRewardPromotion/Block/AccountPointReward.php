<?php
namespace Magento\PointRewardPromotion\Block;
/**
 * Class Link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class AccountPointReward extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Magento_PointRewardPromotion::point_reward.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('customer/account');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Reward');
    }
}