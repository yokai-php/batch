<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Storage;

use Exception;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    /**
     * @dataProvider valid
     */
    public function testValid(callable $factory, Query $expected): void
    {
        /** @var QueryBuilder $builder */
        $builder = $factory();
        $actual = $builder->getQuery();
        self::assertSame($expected->jobs(), $actual->jobs());
        self::assertSame($expected->ids(), $actual->ids());
        self::assertSame($expected->statuses(), $actual->statuses());
        self::assertSame($expected->sort(), $actual->sort());
        self::assertSame($expected->limit(), $actual->limit());
        self::assertSame($expected->offset(), $actual->offset());
    }

    public function valid(): \Generator
    {
        /** default values for {@see Query::__construct} */
        $jobNames = [];
        $ids = [];
        $statuses = [];
        $sortBy = null;
        $limit = 10;
        $offset = 0;

        yield 'Query job names' => [
            fn() => (new QueryBuilder())->jobs(['job1', 'job2']),
            new Query(['job1', 'job2'], $ids, $statuses, $sortBy, $limit, $offset),
        ];
        yield 'Query job ids' => [
            fn() => (new QueryBuilder())->ids(['id1', 'id2', 'id3']),
            new Query($jobNames, ['id1', 'id2', 'id3'], $statuses, $sortBy, $limit, $offset),
        ];
        yield 'Query job statuses' => [
            fn() => (new QueryBuilder())->statuses([BatchStatus::ABANDONED, BatchStatus::STOPPED]),
            new Query($jobNames, $ids, [BatchStatus::ABANDONED, BatchStatus::STOPPED], $sortBy, $limit, $offset),
        ];
        yield 'Query with sort' => [
            fn() => (new QueryBuilder())->sort(Query::SORT_BY_START_DESC),
            new Query($jobNames, $ids, $statuses, Query::SORT_BY_START_DESC, $limit, $offset),
        ];
        yield 'Query with limit' => [
            fn() => (new QueryBuilder())->limit(30, 60),
            new Query($jobNames, $ids, $statuses, $sortBy, 30, 60),
        ];
        yield 'Query complex' => [
            fn() => (new QueryBuilder())
                ->ids(['123', '456'])
                ->jobs(['export', 'import'])
                ->statuses([BatchStatus::RUNNING, BatchStatus::COMPLETED])
                ->sort(Query::SORT_BY_END_DESC)
                ->limit(6, 12),
            new Query(
                ['export', 'import'],
                ['123', '456'],
                [BatchStatus::RUNNING, BatchStatus::COMPLETED],
                Query::SORT_BY_END_DESC,
                6,
                12
            ),
        ];
    }

    /**
     * @dataProvider invalid
     */
    public function testInvalid(callable $factory, Exception $expected): void
    {
        $this->expectExceptionObject($expected);
        $factory();
    }

    public function invalid(): \Generator
    {
        yield 'QueryBuilder::jobs expect string array' => [
            fn() => (new QueryBuilder())->jobs(['string', 666]),
            UnexpectedValueException::type('string', 666),
        ];
        yield 'QueryBuilder::ids expect string array' => [
            fn() => (new QueryBuilder())->ids(['string', 666]),
            UnexpectedValueException::type('string', 666),
        ];
        yield 'QueryBuilder::statuses expect BatchStatus::* constant array' => [
            fn() => (new QueryBuilder())->statuses([BatchStatus::FAILED, 666]),
            UnexpectedValueException::enum(
                [
                    BatchStatus::PENDING,
                    BatchStatus::RUNNING,
                    BatchStatus::STOPPED,
                    BatchStatus::COMPLETED,
                    BatchStatus::ABANDONED,
                    BatchStatus::FAILED,
                ],
                666
            ),
        ];
        yield 'QueryBuilder::sort expect any Query::SORT_*' => [
            fn() => (new QueryBuilder())->sort('wrong'),
            UnexpectedValueException::enum(
                [
                    Query::SORT_BY_START_ASC,
                    Query::SORT_BY_START_DESC,
                    Query::SORT_BY_END_ASC,
                    Query::SORT_BY_END_DESC,
                ],
                'wrong'
            ),
        ];
        yield 'QueryBuilder::limit $limit argument expect positive int' => [
            fn() => (new QueryBuilder())->limit(0, 0),
            UnexpectedValueException::min(1, 0),
        ];
        yield 'QueryBuilder::limit $offset argument expect positive int or 0' => [
            fn() => (new QueryBuilder())->limit(1, -1),
            UnexpectedValueException::min(0, -1),
        ];
    }
}
