Для тестов необходим Phpunit.

Как пользоваться:
Создаем:

$sphinx = new SphinxQL($server, $port)

Получаем заготовку для запроса:
$query = $sphinx->getQuery();

После чего составляем запрос:
$time = time()-3600;
        $time2 = time()-600;
        $query->addIndex('my_index')
            ->addField('field_name', 'alias')
            ->addField('another_field')
            ->addFields(array(array('field' => 'title', 'alias' => 'title_alias'), array('field' => 'user_id')))
            ->search('some words to search for')
            ->addWhere('category1', 36)
            ->addWhere('category2', 0, '!=')
            ->addWhere('time', $time, '>')
            ->addWhere('time', $time2, '<=')
            ->addWhere('tags_i_do_not_want', array(4, 5, 6), 'NOT IN')
            ->addWhere('tags_i_would_like_one_of', array(7, 8, 9), 'IN')
            ->addWhere('tags_i_do_between', array(10, 11), 'BETWEEN')
            ->addOrderBy('@weight', 'desc')
            ->offset(10)->limit(50)
            ->addOption('max_query_time', '100')
            ->addGroupBy('field')
            ->groupOrder('another_field', 'desc');

В результате будет сгенерирован запрос:
"SELECT field_name AS alias, another_field, title AS title_alias, user_id FROM my_index WHERE MATCH('some words to search for') AND category1 = 36 AND category2 != 0 AND time > $time AND time <= $time2 AND tags_i_do_not_want NOT IN (4,5,6) AND tags_i_would_like_one_of IN (7,8,9) AND tags_i_do_between BETWEEN 10 AND 11 GROUP BY field WITHIN GROUP ORDER BY another_field desc ORDER BY @weight desc LIMIT 10, 50 OPTION max_query_time=100"
Полученные запрос отдаем на исполнение:

$sphinx->query($query);

Если нужно выполнить самописный запрос:
$query = SphinxQLQuery::fromString('DELETE FROM test WHERE id = 123;');

В результате получим объект запроса.
После запроса можем получить или одну строчку результата как ассоциированный массив

$rows = $sphinx->fetch();

нумерованный массив
$rows = $sphinx->fetch(SpinxQLClient::FETCH_NUM);
как объект:

$rows = $sphinx->fetch(SpinxQLClient::FETCH_OBJ, 'className');

Можно использовать в while
Или все:
$rows = $sphinx->fetchAll();

Можно указать какой тип будет капать (например SpinxQLClient::FETCH_NUM), так же как и построчно
Edit
Конструктор запросов Конструктор запросов позволяет составить:
SELECT
UPDATE
DELETE
SHOW
CALL KEYWORDS
Select запрос, с помощью методов:
addIndex($index) может добавлять несколько
removeIndex($index) удалить какой нить ненужный индекс
addField($field, $alias=null) добавить поля, поддерживаются алиасы
addFields($fields) добавить сразу несколько полей
removeField($alias)
removeFields($array)
setSearch($search) установить строку поиска, при втором вызове - перезапишется
removeSearch()
setOffset($offset)
setLimit($limit)
addWhere($field, $value, $operator=null)
addGroupBy($field)
removeGroupBy()
groupOrder($field, $sort)
removeGroupOrder()
addOption($name, $value)
removeOption($name = null, $value = null)
addOrderBy($field, $sort = "ask")
removeOrderBy($field = null)
Примеры использования в тестах, в аннотациях есть что ожидается и небольшое описание.

CALL KEYWORDS запрос, с помощью методов:

$query = $sphinx->getQuery();
$query->setType(SphinxQLQuery::QUERY_CALL_KEYWORDS);
$query->addIndex('index');
$query->setSearch('string');

Этот код выполнит запрос:
CALL KEYWORDS(string, index);
SHOW запрос, с помощью методов:

$query = $sphinx->getQuery();
$query->setType(SphinxQLQuery::QUERY_SHOW);
$query->setTypeShow('meta');

Этот код выполнит запрос:
SHOW META;;

UPDATE
$time = time()-3600;
        $time2 = time()-600;
        $query = new SpinxQLQuery();
        $query->addUpdate();
        $query->addIndex('tindex');
        $query->addUpdateField('test1', 'testval1');
        $query->addUpdateField('test2', 'testval2');
        $query->addUpdateField('test3', 'testval3');
        $query->search('this is search');
        $query->addWhere('category1', 36)
            ->addWhere('category2', 0, '!=')
            ->addWhere('time', $time, '>')
            ->addWhere('time', $time2, '<=')
            ->addWhere('tags_i_do_not_want', array(4, 5, 6), 'NOT IN');

Результат:
UPDATE tindex  test1=testval1,test2=testval2,test3=testval3 WHERE MATCH('this is search') AND category1 = 36 AND category2 != 0 AND time > $time AND time <= $time2 AND tags_i_do_not_want NOT IN (4,5,6)

Методы для update:
addUpdate() - инициализация запроса типа update
addIndex('tindex') - добавить индекс
addUpdateField('test1', 'testval1') = добавить поле на обновление, Первое название поля, второе значение
Блок addWhere и search такой же как в select
В случае ошибки прилетает Nerds\SphinxQL\SphinxQLException

[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/alterago/php-sphinxql?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)