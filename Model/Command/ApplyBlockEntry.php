<?php
declare(strict_types=1);

namespace Firegento\ContentProvisioning\Model\Command;

use Firegento\ContentProvisioning\Api\Data\BlockEntryInterface;
use Firegento\ContentProvisioning\Model\Query\GetFirstBlockByBlockEntry;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ApplyBlockEntry
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @var GetFirstBlockByBlockEntry
     */
    private $getFirstBlockByBlockEntry;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var NormalizeData
     */
    private $normalizeData;

    /**
     * @param LoggerInterface $logger
     * @param BlockInterfaceFactory $blockFactory
     * @param GetFirstBlockByBlockEntry $getFirstBlockByBlockEntry
     * @param BlockRepositoryInterface $blockRepository
     * @param NormalizeData $normalizeData
     */
    public function __construct(
        LoggerInterface $logger,
        BlockInterfaceFactory $blockFactory,
        GetFirstBlockByBlockEntry $getFirstBlockByBlockEntry,
        BlockRepositoryInterface $blockRepository,
        NormalizeData $normalizeData
    ) {
        $this->logger = $logger;
        $this->blockFactory = $blockFactory;
        $this->getFirstBlockByBlockEntry = $getFirstBlockByBlockEntry;
        $this->blockRepository = $blockRepository;
        $this->normalizeData = $normalizeData;
    }

    /**
     * @param BlockEntryInterface $blockEntry
     * @throws LocalizedException
     */
    public function execute(BlockEntryInterface $blockEntry): void
    {
        try {
            $block = $this->getFirstBlockByBlockEntry->execute($blockEntry);
            if ($block === null) {
                /** @var BlockInterface $page */
                $block = $this->blockFactory->create([]);
            }
            $block->addData($this->normalizeData->execute($blockEntry->getData()));
            $this->blockRepository->save($block);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
