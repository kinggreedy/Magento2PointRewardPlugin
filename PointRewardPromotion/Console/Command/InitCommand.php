<?php
namespace Magento\PointRewardPromotion\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\{ObjectManager, State};
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class InitCommand
 */
class InitCommand extends Command
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * InitCommand constructor.
     * @param State $state
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory
     */
    public function __construct(
        State $state,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource;

        // We cannot use core functions (like saving a product) unless the area code is explicitly set.
        try {
            $state->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Intentionally left empty.
        }
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('pointreward:init')->setDescription('Init Point Reward points system');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->collectionFactory->create();
        $products = $productCollection->addAttributeToSelect('*')->load();

        $cachedPoint = [];
        $count = 0;

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $configurableProductCollection */
        $configurableProductCollection = $this->collectionFactory->create();
        $configurableProducts = $configurableProductCollection->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', 'configurable')
            ->load();

        $output->write("Processing " . count($products) . " products and ");
        $output->writeln(count($configurableProducts) . " configurable products");

        // Calculate reward point for each product
        // Formula: point = price * 100 (1 cent = 1 point) + bonus
        //          bonus = [500 * log10($point)]
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            if (!($count++ % 100)) {
                $output->write(".");
            }
            $point = $product->getPrice() * 100;
            if ($point > 1000) {
                $point = $point + floor(500 * log10($point));
            }
            $product->setCustomAttribute('point_reward', $point);
            $product->save();
            $cachedPoint[$product->getId()] = $point;
        }
        $output->writeln(PHP_EOL . "Updated: $count products");

        //Calculate point for configurable product
        //Point = min of [sku]
        $count = 0;
        foreach ($configurableProducts as $product) {
            if (!($count++ % 100)) {
                $output->write(".");
            }

            $optionSkuList = [];
            $optionCollection = $product->getTypeInstance()->getConfigurableOptions($product);
            foreach ($optionCollection as $option) {
                foreach ($option as $optionData) {
                    $optionSkuList[] = $optionData['sku'];
                }
            }

            $optionIdList = $this->productResource->getProductsIdsBySkus($optionSkuList);
            $minpoint = 0;
            foreach ($optionIdList as $productId) {
                $point = $cachedPoint[$productId];
                if ($point < $minpoint || $minpoint == 0) {
                    $minpoint = $point;
                }
            }

            $product->setCustomAttribute('point_reward', $point);
            $product->save();
            $cachedPoint[$product->getId()] = $point;
        }
        $output->writeln(PHP_EOL . "Updated: $count configurable products");
    }
}