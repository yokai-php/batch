<?php

declare(strict_types=1);

namespace Yokai\Batch\Logger;

use Psr\Log\LoggerInterface;

/**
 * This logger interface extends PSR-3 {@see LoggerInterface} with two additional methods
 * designed to support job execution log storage and streaming.
 *
 * Implementing this interface allows plugging in any logging strategy (in-memory, file, database…)
 * while staying compatible with the PSR-3 ecosystem (Monolog, etc.).
 */
interface JobExecutionLoggerInterface extends LoggerInterface
{
    /**
     * Returns the string that will be stored alongside the {@see JobExecution}.
     *
     * The meaning depends on the implementation:
     * - In-memory: the accumulated log content (current default behaviour)
     * - File-based: the path to the log file
     * - etc...
     *
     * This value is serialized by {@see \Yokai\Batch\Serializer\JobExecutionSerializerInterface}
     * and can later be used to restore the logs reference on deserialization.
     */
    public function getReference(): string;

    /**
     * Returns the log content as a lazy iterable of lines.
     *
     * Implementations should avoid loading all content in memory at once.
     * This method is intended for display and download (e.g. streamed HTTP response).
     *
     * @return iterable<string>
     */
    public function getLogs(): iterable;

    /**
     * Returns the log content as a full string.
     *
     * You should avoid using this method as possible, because it may load a lot your memory.
     * Prefer {@see self::getLogs}.
     */
    public function getLogsContent(): string;
}
