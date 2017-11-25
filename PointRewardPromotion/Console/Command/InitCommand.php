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
    public function __construct(State $state, ProductRepositoryInterface $prepo)
    {
        // We cannot use core functions (like saving a product) unless the area
        // code is explicitly set.
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $products = $productCollection->addAttributeToSelect('*')->load();
        $count = 0;
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
            $product->save();  //deprecated ?
        }
        $output->writeln("Updated: $count products");
    }
}