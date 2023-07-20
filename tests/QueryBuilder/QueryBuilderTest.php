<?php

namespace App\Tests\QueryBuilder;

use App\Models\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    private ?QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        // В данном случае подставил свои значения для подключения к бд
        $this->queryBuilder = new QueryBuilder(['databaseType' => 'mysql',
            'host' => '127.0.0.1',
            'databaseName' => 'productmodel',
            'username' => 'root',
            'password' => 'Victory777']);
    }

    public function testSelect(): void
    {
        $this->assertSame('SELECT test,field ', $this->queryBuilder->select(['test','field'])->getQuery());
    }

    public function testFrom(): void
    {
        $this->assertEquals('FROM tableName ', $this->queryBuilder->from('tableName')->getQuery());
    }

    public function testWhere(): void
    {
        $this->assertEquals('WHERE amount = 3 '
            , $this->queryBuilder->where('amount','=','3')->getQuery());
    }

    public function testWhereIn(): void
    {
        $this->assertEquals("WHERE testvalue IN ('testvalue','anothervalue') ",
            $this->queryBuilder->whereIn('testvalue',['testvalue','anothervalue'])->getQuery());
    }

    public function testOrderBy(): void
    {
        $this->assertEquals('ORDER BY some ASC ',
        $this->queryBuilder->orderBy('some','ASC')->getQuery());
    }

    public function testJoin(): void
    {
        $this->assertEquals('LEFT JOIN tableName ON firstColumn = secondColumn ',
        $this->queryBuilder->join('LEFT JOIN','tableName','firstColumn','secondColumn')
        ->getQuery());
    }

    public function testlimit(): void
    {
        $this->assertEquals('LIMIT 5 ', $this->queryBuilder->limit(5)->getQuery());
    }

    public function testAndOr(): void
    {
        $this->assertEquals('AND test = something ',$this->queryBuilder->andOr('AND','test','=',
        'something')->getQuery());
    }

    public function testGroupBy(): void
    {
        $this->assertEquals('GROUP BY category ',$this->queryBuilder->groupBy('category')->getQuery());
    }

    public function testHaving(): void
    {
        $this->assertEquals('HAVING SUM(a * b) > 500 ',
            $this->queryBuilder->having('SUM','a * b','>','500')->getQuery());
    }

    public function testCreateNewRecordQuery(): void
    {
        $this->assertEquals("INSERT INTO tableName (columnOne, columnTwo) VALUES ('valueOne', 'valueTwo') ",
        $this->queryBuilder->createNewRecord('tableName',['valueOne','valueTwo'],['columnOne','columnTwo'])
        ->getQuery());
    }

    public function testUpdateRecordsQuery(): void
    {
        $this->assertEquals("UPDATE tableName SET name = Boris WHERE age = 25 ",
        $this->queryBuilder->updateRecords('tableName','name','=','Boris',
            ['key' =>'age',"comparisonSign" => '=','value' => 25])
            ->getQuery());
    }

    public function testDeleteRecordQuery(): void
    {
        $this->assertEquals('DELETE FROM tableName WHERE age = 25 ',
        $this->queryBuilder->deleteRecord('tableName',['key' =>'age',"comparisonSign" => '=','value' => 25])
            ->getQuery());
    }

    public function testDeleteRow(): void
    {
        // Получает количество имеющихся в базе данных рядов
        $startAmountOfRows = $this->queryBuilder->getPdo()->query('SELECT COUNT(*) FROM review')->fetchColumn();

        // Проверка запроса на удаление
        $this->queryBuilder->deleteRecord('review',['key' =>'id',"comparisonSign" => '=','value' => 19])
            ->execute();

        // Количество рядов после удаления
        $finishAmountOfRows = $this->queryBuilder->getPdo()->query('SELECT COUNT(*) FROM review')->fetchColumn();

        $this->assertEquals($finishAmountOfRows,$startAmountOfRows - 1);
    }

    public function testInsertRow(): void
    {
        $startAmountOfRows = $this->queryBuilder->getPdo()->query('SELECT COUNT(*) FROM review')->fetchColumn();

        //
        $this->queryBuilder->createNewRecord('review',['test7',5,1],['body','rating','product_id'])
            ->execute();

        $finishAmountOfRows = $this->queryBuilder->getPdo()->query('SELECT COUNT(*) FROM review')->fetchColumn();

        $this->assertEquals($finishAmountOfRows,$startAmountOfRows + 1);
    }

    public function testEditRow(): void
    {

        $this->queryBuilder->updateRecords('review','rating','=',2,
            ['key' => 'id','comparisonSign'=>'=','value' => 1])->execute();

        $expectedRating = 2;

        $elements = $this->queryBuilder->getPdo()->query('SELECT id,rating FROM review ')->fetchAll();

        foreach ($elements as $element)
        {
            if ($element['id'] === 1)
            {
                $this->assertEquals($expectedRating,$element['rating']);
            }
        }
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->queryBuilder = null;
    }




}