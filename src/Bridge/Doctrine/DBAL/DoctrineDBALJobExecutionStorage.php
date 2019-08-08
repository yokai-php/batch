<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryableJobExecutionStorageInterface;

final class DoctrineDBALJobExecutionStorage implements QueryableJobExecutionStorageInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table = 'yokai_batch_job_execution';

    /**
     * @var string
     */
    private $idCol = 'id';

    /**
     * @var string
     */
    private $jobNameCol = 'job_name';

    /**
     * @var string
     */
    private $statusCol = 'status';

    /**
     * @var string
     */
    private $parametersCol = 'parameters';

    /**
     * @var string
     */
    private $startTimeCol = 'start_time';

    /**
     * @var string
     */
    private $endTimeCol = 'end_time';

    /**
     * @var string
     */
    private $summaryCol = 'summary';

    /**
     * @var string
     */
    private $failuresCol = 'failures';

    /**
     * @var string
     */
    private $warningsCol = 'warnings';

    /**
     * @var string
     */
    private $childExecutionsCol = 'child_executions';

    /**
     * @var array
     */
    private $types;

    /**
     * @var JobExecutionRowNormalizer|null
     */
    private $normalizer;

    public function __construct(Connection $connection, array $options)
    {
        $this->connection = $connection;

        $this->table = $options['table'] ?? $this->table;
        $this->idCol = $options['id_col'] ?? $this->idCol;
        $this->jobNameCol = $options['job_name_col'] ?? $this->jobNameCol;
        $this->statusCol = $options['status_col'] ?? $this->statusCol;
        $this->parametersCol = $options['parameters_col'] ?? $this->parametersCol;
        $this->startTimeCol = $options['start_time_col'] ?? $this->startTimeCol;
        $this->endTimeCol = $options['end_time_col'] ?? $this->endTimeCol;
        $this->summaryCol = $options['summary_col'] ?? $this->summaryCol;
        $this->failuresCol = $options['failures_col'] ?? $this->failuresCol;
        $this->warningsCol = $options['warnings_col'] ?? $this->warningsCol;
        $this->childExecutionsCol = $options['child_executions_col'] ?? $this->childExecutionsCol;

        $this->types = [
            $this->idCol => Type::STRING,
            $this->jobNameCol => Type::STRING,
            $this->statusCol => Type::INTEGER,
            $this->parametersCol => Type::JSON,
            $this->startTimeCol => Type::DATETIME_IMMUTABLE,
            $this->endTimeCol => Type::DATETIME_IMMUTABLE,
            $this->summaryCol => Type::JSON,
            $this->failuresCol => Type::JSON,
            $this->warningsCol => Type::JSON,
            $this->childExecutionsCol => Type::JSON,
        ];
    }

    public function createTable(): void
    {
        $this->connection->exec(
            $this->createTableSQL()
        );
    }

    public function createTableSQL(): string
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'mysql':
                $json = $this->connection->getDatabasePlatform()->getJsonTypeDeclarationSQL([]);

                return <<<SQL
CREATE TABLE {$this->table} (
    {$this->idCol} VARBINARY(128) NOT NULL PRIMARY KEY,
    {$this->jobNameCol} VARCHAR (255) NOT NULL,
    {$this->statusCol} INT NOT NULL,
    {$this->parametersCol} {$json} NOT NULL,
    {$this->startTimeCol} DATETIME DEFAULT NULL,
    {$this->endTimeCol} DATETIME DEFAULT NULL,
    {$this->summaryCol} {$json} NOT NULL,
    {$this->failuresCol} {$json} NOT NULL,
    {$this->warningsCol} {$json} NOT NULL,
    {$this->childExecutionsCol} {$json} NOT NULL
)
SQL;

            case 'sqlite':
                return <<<SQL
CREATE TABLE {$this->table} (
    {$this->idCol} VARCHAR(128) NOT NULL PRIMARY KEY,
    {$this->jobNameCol} VARCHAR (255) NOT NULL,
    {$this->statusCol} INT NOT NULL,
    {$this->parametersCol} CLOB NOT NULL,
    {$this->startTimeCol} DATETIME DEFAULT NULL,
    {$this->endTimeCol} DATETIME DEFAULT NULL,
    {$this->summaryCol} CLOB NOT NULL,
    {$this->failuresCol} CLOB NOT NULL,
    {$this->warningsCol} CLOB NOT NULL,
    {$this->childExecutionsCol} CLOB NOT NULL
)
SQL;
        }

        throw new \DomainException(
            sprintf('Platform "%s" is not supported.', $platform->getName())
        );
    }

    public function dropTable(): void
    {
        $this->connection->exec(
            $this->dropTableSQL()
        );
    }

    public function dropTableSQL(): string
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'mysql':
            case 'sqlite':
                return <<<SQL
DROP TABLE {$this->table}
SQL;
        }

        throw new \DomainException(
            sprintf('Platform "%s" is not supported.', $platform)
        );
    }

    /**
     * @inheritDoc
     */
    public function store(JobExecution $execution): void
    {
        try {
            $this->fetchRow($execution->getJobName(), $execution->getId());
            $stored = true;
        } catch (\InvalidArgumentException $exception) {
            $stored = false;
        }

        $data = $this->toRow($execution);

        try {
            if ($stored) {
                $this->connection->update($this->table, $data, $this->identity($execution), $this->types);
            } else {
                $this->connection->insert($this->table, $data, $this->types);
            }
        } catch (DBALException $exception) {
            throw new CannotStoreJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(JobExecution $execution): void
    {
        try {
            $this->connection->delete($this->table, $this->identity($execution));
        } catch (DBALException $exception) {
            throw new CannotStoreJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        try {
            $row = $this->fetchRow($jobName, $executionId);
        } catch (\InvalidArgumentException $exception) {
            throw new JobExecutionNotFoundException($jobName, $executionId, $exception);
        }

        return $this->fromRow($row);
    }

    /**
     * @inheritDoc
     */
    public function list(string $jobName): iterable
    {
        yield from $this->queryList(
            "SELECT * FROM {$this->table} WHERE {$this->jobNameCol} = :jobName",
            ['jobName' => $jobName],
            ['jobName' => Type::STRING]
        );
    }

    /**
     * @inheritDoc
     */
    public function query(Query $query): iterable
    {
        $queryConditions = [];
        $queryParameters = [];
        $queryTypes = [];

        $names = $query->jobs();
        if (count($names) === 1) {
            $queryConditions[] = "{$this->jobNameCol} = :jobName";
            $queryParameters['jobName'] = array_shift($names);
            $queryTypes['jobName'] = Type::STRING;
        } elseif (count($names) > 1) {
            $queryConditions[] = "{$this->jobNameCol} IN (:jobNames)";
            $queryParameters['jobNames'] = $names;
            $queryTypes['jobNames'] = Connection::PARAM_STR_ARRAY;
        }

        $ids = $query->ids();
        if (count($ids) === 1) {
            $queryConditions[] = "{$this->idCol} = :id";
            $queryParameters['id'] = array_shift($ids);
            $queryTypes['id'] = Type::STRING;
        } elseif (count($ids) > 1) {
            $queryConditions[] = "{$this->idCol} IN (:ids)";
            $queryParameters['ids'] = $ids;
            $queryTypes['ids'] = Connection::PARAM_STR_ARRAY;
        }

        $statuses = $query->statuses();
        if (count($statuses) === 1) {
            $queryConditions[] = "{$this->statusCol} = :status";
            $queryParameters['status'] = array_shift($statuses);
            $queryTypes['status'] = Type::INTEGER;
        } elseif (count($statuses) > 1) {
            $queryConditions[] = "{$this->statusCol} IN (:statuses)";
            $queryParameters['statuses'] = $statuses;
            $queryTypes['statuses'] = Connection::PARAM_INT_ARRAY;
        }

        $conditions = '';
        if (count($queryConditions) > 0) {
            $conditions = 'WHERE '.implode(' AND ', $queryConditions);
        }

        $order = '';
        switch ($query->sort()) {
            case Query::SORT_BY_START_ASC:
                $order = "ORDER BY {$this->startTimeCol} ASC";
                break;
            case Query::SORT_BY_START_DESC:
                $order = "ORDER BY {$this->startTimeCol} DESC";
                break;
            case Query::SORT_BY_END_ASC:
                $order = "ORDER BY {$this->endTimeCol} ASC";
                break;
            case Query::SORT_BY_END_DESC:
                $order = "ORDER BY {$this->endTimeCol} DESC";
                break;
        }

        $count = $query->limit();
        $offset = $query->offset();
        $platform = $this->connection->getDatabasePlatform()->getName();
        switch ($platform) {
            case 'mysql':
                $limit = "LIMIT {$count}, {$offset}";
                break;
            case 'sqlite':
                $limit = "LIMIT {$offset}, {$count}";
                break;
            default:
                throw new \DomainException(
                    sprintf('Platform "%s" is not supported.', $platform)
                );
        }

        yield from $this->queryList(
            "SELECT * FROM {$this->table} {$conditions} {$order} {$limit}",
            $queryParameters,
            $queryTypes
        );
    }

    private function identity(JobExecution $execution): array
    {
        return [
            $this->jobNameCol => $execution->getJobName(),
            $this->idCol => $execution->getId(),
        ];
    }

    private function fetchRow(string $jobName, string $id): array
    {
        $statement = $this->connection->executeQuery(
            "SELECT * FROM {$this->table} WHERE {$this->jobNameCol} = :jobName AND {$this->idCol} = :id",
            ['jobName' => $jobName, 'id' => $id],
            ['jobName' => Type::STRING, 'id' => Type::STRING]
        );
        $rows = $statement->fetchAll();
        switch (count($rows)) {
            case 1:
                return array_shift($rows);
            case 0:
                throw new \InvalidArgumentException();//todo
            default:
                throw new \InvalidArgumentException();//todo
        }
    }

    private function queryList(string $query, array $parameters, array $types): iterable
    {
        $statement = $this->connection->executeQuery($query, $parameters, $types);

        while ($row = $statement->fetch()) {
            yield $this->fromRow($row);
        }

        $statement->closeCursor();
    }

    private function toRow(JobExecution $jobExecution): array
    {
        return $this->getNormalizer()->toRow($jobExecution);
    }

    private function fromRow(array $row): JobExecution
    {
        return $this->getNormalizer()->fromRow($row);
    }

    private function getNormalizer(): JobExecutionRowNormalizer
    {
        if ($this->normalizer === null) {
            $this->normalizer = new JobExecutionRowNormalizer(
                $this->idCol,
                $this->jobNameCol,
                $this->statusCol,
                $this->parametersCol,
                $this->startTimeCol,
                $this->endTimeCol,
                $this->summaryCol,
                $this->failuresCol,
                $this->warningsCol,
                $this->childExecutionsCol,
                $this->connection->getDatabasePlatform()->getDateTimeFormatString()
            );
        }

        return $this->normalizer;
    }
}
