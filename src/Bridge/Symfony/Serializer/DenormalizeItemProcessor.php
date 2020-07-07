<?php

namespace Yokai\Batch\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Yokai\Batch\Job\Item\InvalidItemException;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class DenormalizeItemProcessor implements ItemProcessorInterface
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var array
     */
    private $context;

    public function __construct(
        DenormalizerInterface $denormalizer,
        string $type,
        string $format = null,
        array $context = []
    ) {
        $this->denormalizer = $denormalizer;
        $this->type = $type;
        $this->format = $format;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        if (!$this->denormalizer->supportsDenormalization($item, $this->type, $this->format)) {
            throw new InvalidItemException(
                'Unable to denormalize item. Not supported.',
                [
                    'item' => is_object($item) ? get_class($item) : gettype($item),
                    'format' => $this->format,
                ]
            );
        }

        try {
            $object = $this->denormalizer->denormalize($item, $this->type, $this->format, $this->context);
        } catch (ExceptionInterface $exception) {
            throw new InvalidItemException(
                'Unable to denormalize item. An exception occurred.',
                [
                    'item' => is_object($item) ? get_class($item) : gettype($item),
                    'format' => $this->format,
                    'context' => $this->context,
                ],
                0,
                $exception
            );
        }

        return $object;
    }
}
