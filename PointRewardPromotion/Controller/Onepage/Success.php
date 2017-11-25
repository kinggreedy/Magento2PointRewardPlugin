<?php
namespace Magento\PointRewardPromotion\Controller\Onepage;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;

class Success extends \Magento\Checkout\Controller\Onepage\Success
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     *
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
    }

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
            $order = $session->getLastRealOrder();

            $customerId = $order->getCustomerId();
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerFactory->create()->load($customerId);
            $customerData = $customer->getData();
            $totalPoint = 0;
            if (isset($customerData["point_reward_customer"])) {
                $totalPoint = intval($customerData["point_reward_customer"]);
            }

            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $item) {
                $productSku = $item->getSku();
                /** @var Product $product */
                $productId = $item->getProduct()->getIdBySku($productSku);
                $product = $this->productFactory->create()->load($productId);
                $qty = $item->getQtyOrdered();
                $totalPoint += intval($product->getCustomAttribute('point_reward')->getValue()) * $qty;
            }

            //save the collected point of customer. No idea why the only way it works is by creating CustomerFactory from $om
            $customer->setData('point_reward_customer', $totalPoint);
            $customerResource = $objectManager->create(\Magento\Customer\Model\ResourceModel\CustomerFactory::class)->create();
            $customerResource->saveAttribute($customer, 'point_reward_customer');
        }
        return parent::execute();
    }
}
