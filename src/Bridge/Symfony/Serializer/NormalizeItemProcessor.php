<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Yokai\Batch\Job\Item\InvalidItemException;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class NormalizeItemProcessor implements ItemProcessorInterface
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $context;

    public function __construct(NormalizerInterface $normalizer, string $format = null, array $context = [])
    {
        $this->normalizer = $normalizer;
        $this->format = $format;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        if (!$this->normalizer->supportsNormalization($item, $this->format)) {
            throw new InvalidItemException(
                'Unable to normalize item. Not supported.',
                [
                    'item' => is_object($item) ? get_class($item) : gettype($item),
                    'format' => $this->format,
                ]
            );
        }

        try {
            return $this->normalizer->normalize($item, $this->format, $this->context);
        } catch (ExceptionInterface $exception) {
            throw new InvalidItemException(
                'Unable to normalize item. An exception occurred.',
                [
                    'item' => is_object($item) ? get_class($item) : gettype($item),
                    'format' => $this->format,
                    'context' => $this->context,
                ],
                0,
                $exception
            );
        }
    }
}
