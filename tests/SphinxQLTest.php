<?
namespace Nerds;

use Nerds\SphinxQLException;
use Nerds\SphinxQLClient;
use Nerds\SphinxQLQuery;
use \PHPUnit_Framework_TestCase;

require_once 'src/Nerds/SphinxQLQuery.php';
require_once 'src/Nerds/SphinxQL.php';
require_once 'src/Nerds/SphinxQLException.php';

class SphinxQLTest extends PHPUnit_Framework_TestCase
{
    private $exeption = 'Nerds\SphinxQLException';

    public function testShouldTrowExeptionAddIntToIndex()
    {
        $query = new SphinxQLQuery();
        $this->setExpectedException($this->exeption);
        $query->addIndex((int) 0);
    }

    public function testShouldAddStringToIndex() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldAdd2indexes() {
        $query = new SphinxQLQuery();
        $query->addIndex('test1');
        $query->addIndex('test2');
        $this->assertEquals('SELECT * FROM test1,test2 LIMIT 0, 20', $query->toString());
    }

    public function testShouldAddTwoEquivalentIndex() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addIndex('test');
    }

    public function testShouldAddField() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addField('test');
        $this->assertEquals('SELECT test FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldAddFieldWithAlias() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addField('test', 'alias');
        $this->assertEquals('SELECT test AS alias FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldTrowExeptionAddIntToField() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addField((int) 1);
    }

    public function testShouldTrowExeptionAddIntToFields() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addFields(array((int) 1));
    }

    public function testShouldTrowExeptionAddNotArrayToFields() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addFields('test');
    }

    public function testShouldAddFieldsToIndex() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addFields(array(array('field'=>'test1', 'alias' => 'alias1'), array('field'=>'test2')));
        $this->assertEquals('SELECT test1 AS alias1, test2 FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveField() {
        $query = new SphinxQLQuery();
        $query->addIndex('test')
            ->addFields(array(array('field'=>'test1', 'alias' => 'alias1'), array('field'=>'test2')))
            ->removeField('test1');
        $this->assertEquals('SELECT test2 FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveFieldByAlias() {
        $query = new SphinxQLQuery();
        $query->addIndex('test')
            ->addFields(array(array('field'=>'test1', 'alias' => 'alias1'), array('field'=>'test2')));
        $this->assertEquals('SELECT test1 AS alias1, test2 FROM test LIMIT 0, 20', $query->toString());
        $query->removeField('alias1');
        $this->assertEquals('SELECT test2 FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveFields() {
        $query = new SphinxQLQuery();
        $query->addIndex('test')
            ->addFields(array(array('field'=>'test1', 'alias' => 'alias1'), array('field'=>'test2'), array('field'=>'test3')));
        $this->assertEquals('SELECT test1 AS alias1, test2, test3 FROM test LIMIT 0, 20', $query->toString());
        $query->removeFields(array('alias1', 'test3'));
        $this->assertEquals('SELECT test2 FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldAddSearch() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setSearch('test');
        $this->assertEquals("SELECT * FROM test WHERE MATCH('test') LIMIT 0, 20", $query->toString());
    }

    public function testShouldTrowExeptionAddIntToSearch() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setSearch((int) 0);

    }

    public function testShouldRemoveSearch() {

        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setSearch('test');
        $this->assertEquals("SELECT * FROM test WHERE MATCH('test') LIMIT 0, 20", $query->toString());
        $query->removeSearch();
        $this->assertEquals("SELECT * FROM test LIMIT 0, 20", $query->toString());
    }

    public function testShouldAddOffset() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setOffset(10);
        $this->assertEquals("SELECT * FROM test LIMIT 10, 20", $query->toString());
    }

    public function testShouldTrowExeptionAddStringToOffset() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setOffset('string');
    }

    public function testShouldAddLimit() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setLimit(10);
        $this->assertEquals("SELECT * FROM test LIMIT 0, 10", $query->toString());
    }

    public function testShouldTrowExeptionAddStringToLimit() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->setLimit('string');
    }

    public function testShouldAddWhere() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23');
        $this->assertEquals("SELECT * FROM test WHERE test = 23 LIMIT 0, 20", $query->toString());
    }

    public function testShouldAddWhereIn() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', array('23', '24'), 'IN');
        $this->assertEquals("SELECT * FROM test WHERE test IN (23,24) LIMIT 0, 20", $query->toString());
    }

    public function testShouldAddWhereBetween() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', array('23', '24'), 'BETWEEN');
        $this->assertEquals("SELECT * FROM test WHERE test BETWEEN 23 AND 24 LIMIT 0, 20", $query->toString());
    }

    public function testShouldAdd2Where() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23');
        $this->assertEquals("SELECT * FROM test WHERE test = 23 LIMIT 0, 20", $query->toString());
        $query->addWhere('test', array('23', '24'), 'IN');
        $this->assertEquals("SELECT * FROM test WHERE test = 23 AND test IN (23,24) LIMIT 0, 20", $query->toString());;
    }
    
    public function testShouldDeleteWhere() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23');
        $query->removeWhere('test');
        $this->assertEquals("SELECT * FROM test LIMIT 0, 20", $query->toString());
    }
    
    public function testShouldDeleteOneWhere() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23');
        $this->assertEquals("SELECT * FROM test WHERE test = 23 LIMIT 0, 20", $query->toString());
        $query->addWhere('test', array('23', '24'), 'IN');
        $query->removeWhere('test');
        $this->assertEquals("SELECT * FROM test WHERE test IN (23,24) LIMIT 0, 20", $query->toString());;
    }

    public function testShouldTrowExeptionWrongOperator() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23', '=!');
    }

    public function testShouldTrowExeptionValueNotArrayIn() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23', 'IN');
    }

    public function testShouldTrowExeptionValueNotArrayNotIn() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23', 'NOT IN');
    }

    public function testShouldTrowExeptionValueNotArrayBetween() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere('test', '23', 'BETWEEN');

    }


    public function testShouldTrowExeptionFieldNotString() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addWhere(23, "23");
    }

    public function testShouldTrowExeptionAddIntToGroupBy()
    {
        $query = new SphinxQLQuery();
        $this->setExpectedException($this->exeption);
        $query->addGroupBy(0);
    }

    public function testShouldAddGroupBy() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addGroupBy('test');
        $this->assertEquals('SELECT * FROM test GROUP BY test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveGroupBy() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addGroupBy('test');
        $this->assertEquals('SELECT * FROM test GROUP BY test LIMIT 0, 20', $query->toString());
        $query->removeGroupBy();
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldTrowExeptionGroupOrderField() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->groupOrder(23, "23");
    }

    public function testShouldTrowExeptionGroupOrderSort() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->groupOrder('test', 23);
    }

    public function testShouldGroupOrderSort() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->groupOrder('test', 'test1');
        $this->assertEquals('SELECT * FROM test WITHIN GROUP ORDER BY test test1 LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveGroupOrderSort() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->groupOrder('test', 'test1');
        $this->assertEquals('SELECT * FROM test WITHIN GROUP ORDER BY test test1 LIMIT 0, 20', $query->toString());
        $query->removeGroupOrder();
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldTrowExeptionAddIntToNameOption() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption(23, "test");
    }


    public function testShouldAddOption() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('testName', 'testValue');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION testName=testValue', $query->toString());
    }

    public function testShouldAdd2Option() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('test1Name', 'test1Value');
        $query->addOption('test2Name', 'test2Value');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION test1Name=test1Value, test2Name=test2Value', $query->toString());
    }

    public function testShouldTrowExeptionAddIntToRemoveNameOption() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('testName', 'testValue');
        $query->removeOption(23, 'testValue');
    }

    public function testShouldTrowExeptionIntToRemoveValueOption() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('testName', 'testValue');
        $query->removeOption('testName', 23);
    }

    public function testShouldRemoveOption() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('testName', 'testValue');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION testName=testValue', $query->toString());
        $query->removeOption('testName');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveOptionWithValue() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('testName', 'testValue');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION testName=testValue', $query->toString());
        $query->removeOption('testName', 'testValueFalse');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION testName=testValue', $query->toString());
        $query->removeOption('testName', 'testValue');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldClearOptions() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('test1Name', 'test1Value');
        $query->addOption('test2Name', 'test2Value');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION test1Name=test1Value, test2Name=test2Value', $query->toString());
        $query->removeOption();
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldRemoveSecondOption() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOption('test1Name', 'test1Value');
        $query->addOption('test2Name', 'test2Value');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION test1Name=test1Value, test2Name=test2Value', $query->toString());
        $query->removeOption('test2Name');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20 OPTION test1Name=test1Value', $query->toString());
    }

    public function testShouldAddOrder() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOrderBy('test1');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc LIMIT 0, 20', $query->toString());
        $query->addOrderBy('test2', 'desc');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc, test2 desc LIMIT 0, 20', $query->toString());
    }


    public function testShouldTrowExeptionWrongFieldOrderBy() {
        $this->setExpectedException($this->exeption);
        $query = new SphinxQLQuery();
        $query->addOrderBy(4);
    }

    public function testShouldRemoveOrder() {
        $query = new SphinxQLQuery();
        $query->addIndex('test');
        $query->addOrderBy('test1');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc LIMIT 0, 20', $query->toString());
        $query->removeOrderBy('test1');
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
        $query->addOrderBy('test1');
        $query->addOrderBy('test2', 'desc');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc, test2 desc LIMIT 0, 20', $query->toString());
        $query->removeOrderBy('test2');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc LIMIT 0, 20', $query->toString());
        $query->addOrderBy('test2', 'desc');
        $this->assertEquals('SELECT * FROM test ORDER BY test1 asc, test2 desc LIMIT 0, 20', $query->toString());
        $query->removeOrderBy();
        $this->assertEquals('SELECT * FROM test LIMIT 0, 20', $query->toString());
    }

    public function testShouldCreateSphinxQL() {
        $query = new SphinxQLQuery();
        $time = time()-3600;
        $time2 = time()-600;
        $query->addIndex('my_index')
            ->addField('field_name', 'alias')
            ->addField('another_field')
            ->addFields(array(array('field' => 'title', 'alias' => 'title_alias'), array('field' => 'user_id')))
            ->setSearch('some words to search for')
            ->addWhere('category1', 36)
            ->addWhere('category2', 0, '!=')
            ->addWhere('time', $time, '>')
            ->addWhere('time', $time2, '<=')
            ->addWhere('tags_i_do_not_want', array(4, 5, 6), 'NOT IN')
            ->addWhere('tags_i_would_like_one_of', array(7, 8, 9), 'IN')
            ->addWhere('tags_i_do_between', array(10, 11), 'BETWEEN')
            ->addOrderBy('@weight', 'desc')
            ->setOffset(10)->setLimit(50)
            ->addOption('max_query_time', '100')
            ->addGroupBy('field')
            ->groupOrder('another_field', 'desc');
        $this->assertEquals(
            "SELECT field_name AS alias, another_field, title AS title_alias, user_id FROM my_index WHERE MATCH('some words to search for') AND category1 = 36 AND category2 != 0 AND time > $time AND time <= $time2 AND tags_i_do_not_want NOT IN (4,5,6) AND tags_i_would_like_one_of IN (7,8,9) AND tags_i_do_between BETWEEN 10 AND 11 GROUP BY field WITHIN GROUP ORDER BY another_field desc ORDER BY @weight desc LIMIT 10, 50 OPTION max_query_time=100",
            $query->toString());
    }

    public function testShouldCallKeyWords() {
        $query = new SphinxQLQuery();
        $query->setType(SphinxQLQuery::QUERY_CALL_KEYWORDS);
        $query->addIndex('index');
        $query->setSearch('string');


        $this->assertEquals('CALL KEYWORDS(string, index);', $query->toString());
        $query->setHits(300);
        $this->assertEquals('CALL KEYWORDS(string, index, 300);', $query->toString());
    }

    public function testShouldAddShow() {
        $query = new SphinxQLQuery();
        $query->setType(SphinxQLQuery::QUERY_SHOW);
        $query->setTypeShow('meta');
        $this->assertEquals('SHOW META;', $query->toString());
        $query->setTypeShow('WARNINGS');
        $this->assertEquals('SHOW WARNINGS;', $query->toString());
        $query->setTypeShow('status');
        $this->assertEquals('SHOW STATUS;', $query->toString());
        $this->setExpectedException($this->exeption);
        $query->setTypeShow('test');
    }

    public function testShouldAddSet() {
        $query = new SphinxQLQuery();
        $query->setType(SphinxQLQuery::QUERY_SET);
        $query->addOption('test_name', 'test_value');
        $this->assertEquals('SET test_name = test_value;', $query->toString());
    }

    public function testShouldAddDelete() {
        $query = new SphinxQLQuery();
        $query->setType(SphinxQLQuery::QUERY_DELETE);
        $query->addIndex('test');
        $query->setId(123);
        $this->assertEquals('DELETE FROM test WHERE id = 123;', $query->toString());

    }

    public function testShouldUpdate() {
        $time = time()-3600;
        $time2 = time()-600;
        $query = new SphinxQLQuery();
        $query->setType(SphinxQLQuery::QUERY_UPDATE);
        $query->addIndex('tindex');
        $query->addUpdateField('test1', 'testval1');
        $query->addUpdateField('test2', 'testval2');
        $query->addUpdateField('test3', 'testval3');
        $query->setSearch('this is search');
        $query->addWhere('category1', 36)
            ->addWhere('category2', 0, '!=')
            ->addWhere('time', $time, '>')
            ->addWhere('time', $time2, '<=')
            ->addWhere('tags_i_do_not_want', array(4, 5, 6), 'NOT IN');
        $this->assertEquals("UPDATE tindex  test1=testval1,test2=testval2,test3=testval3 WHERE MATCH('this is search') AND category1 = 36 AND category2 != 0 AND time > $time AND time <= $time2 AND tags_i_do_not_want NOT IN (4,5,6) ", $query->toString());
    }

    public function testShouldInsert() {
        $values = array('id' => 1, 'test' => 'test', 'text' => 'text');
        $query = new SphinxQLQuery();
        $query->addIndex('tindex');
        $query->addInsertFields($values);
        $query->setType(SphinxQLQuery::QUERY_INSERT);

        $testSQL = "INSERT INTO tindex  ( id,test,text )  VALUES ( '1','test','text' );";

        $this->assertEquals($testSQL, $query->toString());
    }

    public function testShouldGetQueryFromString() {
        $query = SphinxQLQuery::fromString('DELETE FROM test WHERE id = 123;');
        $this->assertEquals('DELETE FROM test WHERE id = 123;', $query->toString());
        $this->setExpectedException($this->exeption);
        $query = SphinxQLQuery::fromString(123);
    }

}