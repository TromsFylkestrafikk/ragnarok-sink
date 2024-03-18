<?php


namespace Ragnarok\Sink\Tests\Unit;


use Ragnarok\Sink\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ragnarok\Sink\Services\CsvToTable;
use League\Csv\Reader;
use Ragnarok\Sink\Tests\TestTableModel;

class CsvToTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function canCreateCsvToTableObject() {
        $mapper = new CsvToTable('test', 'test_table', ['id']);
        $this->assertNotNull($mapper);
    }

    /** @test */
    public function canLoadCsvWithSingleEntry() 
    {
        $mapper = new CsvToTable(__DIR__.'/../single.csv', 'test_table', ['id']);
        $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        });
        $mapper->column('name', 'name');
        $mapper->column('city', 'city');
        $mapper->column('number', 'number');

        $mapper->exec();

        $result = TestTableModel::all();
        $this->assertCount(1, $result);
        $this->assertEquals('Maria A. Brown', $result[0]->name);
        $this->assertEquals('Ann Arbor', $result[0]->city);
        $this->assertEquals('734-232-0001', $result[0]->number);
    }

    /** @test */
    public function canLoadCsvWithMultipleEntries() 
    {
        $mapper = new CsvToTable(__DIR__.'/../multiple.csv', 'test_table', ['id']);
        $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        });
        $mapper->column('name', 'name');
        $mapper->column('city', 'city');
        $mapper->column('number', 'number');

        $mapper->exec();

        $result = TestTableModel::all();
        $this->assertCount(3, $result);
        
        $this->assertEquals('Maria A. Brown', $result[0]->name);
        $this->assertEquals('Ann Arbor', $result[0]->city);
        $this->assertEquals('734-232-0001', $result[0]->number);

        $this->assertEquals('Maria B. Brown', $result[1]->name);
        $this->assertEquals('Bnn Arbor', $result[1]->city);
        $this->assertEquals('734-232-0002', $result[1]->number);

        $this->assertEquals('Maria C. Brown', $result[2]->name);
        $this->assertEquals('Cnn Arbor', $result[2]->city);
        $this->assertEquals('734-232-0003', $result[2]->number);
    }

    /** @test */
    public function mapsColumnsCorrectly() 
    {
        $mapper = new CsvToTable(__DIR__.'/../mapping.csv', 'test_table', ['id']);
        $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        });
        $mapper->column('full_name', 'name');
        $mapper->column('current_city', 'city');
        $mapper->column('phone_number', 'number');

        $mapper->exec();

        $result = TestTableModel::all();
        $this->assertCount(3, $result);
        
        $this->assertEquals('Maria A. Brown', $result[0]->name);
        $this->assertEquals('Ann Arbor', $result[0]->city);
        $this->assertEquals('734-232-0001', $result[0]->number);

        $this->assertEquals('Maria B. Brown', $result[1]->name);
        $this->assertEquals('Bnn Arbor', $result[1]->city);
        $this->assertEquals('734-232-0002', $result[1]->number);

        $this->assertEquals('Maria C. Brown', $result[2]->name);
        $this->assertEquals('Cnn Arbor', $result[2]->city);
        $this->assertEquals('734-232-0003', $result[2]->number);
    }

    /** @test */
    public function mapsEmptyColumns() 
    {
        $mapper = new CsvToTable(__DIR__.'/../withemptycolumns.csv', 'test_table', ['id']);
        $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        });
        $mapper->column('full_name', 'name');
        $mapper->column('current_city', 'city');
        $mapper->column('phone_number', 'number');

        $mapper->exec();

        $result = TestTableModel::all();
        $this->assertCount(1, $result);
        
        $this->assertEquals('Maria B. Brown', $result[0]->name);
        $this->assertNull($result[0]->city);
        $this->assertEquals('734-232-0002', $result[0]->number);
    }

    /** @test */
    public function treatSpesificValuesAsNull() 
    {
        $mapper = new CsvToTable(__DIR__.'/../valueasnull.csv', 'test_table', ['id']);
        $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        });

        $mapper->nullValues(['NULL']);

        $mapper->column('full_name', 'name');
        $mapper->column('current_city', 'city');
        $mapper->column('phone_number', 'number');

        $mapper->exec();

        $result = TestTableModel::all();
        $this->assertCount(1, $result);
        
        $this->assertEquals('Maria B. Brown', $result[0]->name);
        $this->assertNull($result[0]->city);
        $this->assertEquals('734-232-0002', $result[0]->number);
    }
}