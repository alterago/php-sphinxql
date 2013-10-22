<?php
namespace Nerds;
use Nerds\SphinxQL;

class SphinxQLQuery {

    CONST QUERY_SELECT          = 1;
    CONST QUERY_UPDATE          = 2;
    CONST QUERY_INSERT          = 3;
    CONST QUERY_DELETE          = 4;
    CONST QUERY_SHOW            = 5;
    CONST QUERY_SET             = 6;
    CONST QUERY_CALL_KEYWORDS   = 7;
    CONST QUERY_FROM_STRING     = 8;

    protected $_types = array(self::QUERY_UPDATE, self::QUERY_DELETE, self::QUERY_INSERT, self::QUERY_SELECT,
                              self::QUERY_SET, self::QUERY_SHOW, self::QUERY_CALL_KEYWORDS, self::QUERY_FROM_STRING);
    /**
     * @var array The indexes that are to be searched
     */
    protected $_indexes = array();
    /**
     * @var array The fields that are to be returned in the result set
     */
    protected $_fields = array();
    /**
     * @var string A string to be searched for in the indexes
     */
    protected $_search = null;
    /**
     * @var array A set of WHERE conditions
     */
    protected $_wheres = array();
    /**
     * @var array The GROUP BY field
     */
    protected $_group = null;
    /**
     * @var array The IN GROUP ORDER BY options
     */
    protected $_groupOrder = null;
    /**
     * @var array A set of ORDER clauses
     */
    protected $_orders = array();
    /**
     * @var integer The offset to start returning results from
     */
    protected $_offset = 0;
    /**
     * @var integer The maximum number of results to return
     */
    protected $_limit = 20;
    /**
     * @var array A set of OPTION clauses
     */
    protected $_options = array();

    /**
     * @var int Type of query
     */
    protected $_typeQuery = self::QUERY_SELECT;

    /**
     * @var string Index
     */
    protected $_index = null;
    /**
     * @var int
     */
    protected $_hits = null;
    /**
     * @var string
     */
    protected $_typeShow = null;
    /**
     * @var int
     */
    protected $_id = null;

    protected $_query = null;

    /**
     * Magic method, returns the result of build().
     *
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * @static
     * @param $query
     * @throws SphinxQLException
     */
    public static function fromString($queryString) {
        $query = new self();
        $query->setQuery($queryString);
        $query->setType(self::QUERY_FROM_STRING);
        return $query;
    }

    public function toString() {
        switch ($this->_typeQuery) {
            case self::QUERY_CALL_KEYWORDS:
                return $this->_buildCallKeywords();
            case self::QUERY_SHOW:
                return $this->_buildShow();
            case self::QUERY_SET:
                return $this->_buildSet();
            case self::QUERY_DELETE:
                return $this->_buildDelete();
            case self::QUERY_UPDATE:
                return $this->_buildUpdate();
            case self::QUERY_FROM_STRING:
                return $this->_query;
            default:
                return $this->_buildSelect();
        }
    }
    protected function _buildIndexes() {
        return sprintf('%s ', implode(',', $this->_indexes));
    }

    protected function _buildCallKeywords() {
        $index = substr($this->_buildIndexes(), 0, -1);
        if ($this->_hits) {
            return "CALL KEYWORDS(" . $this->_search . ", " . $index . ", " . $this->_hits . ");";
        }
        return "CALL KEYWORDS(" . $this->_search . ", " . $index . ");";

    }

    protected function  _buildUpdate() {
        $wheres = array();
        $fields = array();

        if (!$this->_indexes) {
            throw new SphinxQLException ("Not found index");
        }

        $query = 'UPDATE ';
        $query .= $this->_buildIndexes();

        foreach ($this->_fields as $field => $value) {
            $fields[] = sprintf("%s=%s", $field, $value);
        }
        $query .= sprintf(' %s ', implode(',', $fields));

        if (is_string($this->_search)) {
            $wheres[] = sprintf("MATCH('%s')", $this->_escape_query($this->_search));
        }

        foreach ($this->_wheres as $where) {
            $wheres[] = sprintf("%s %s %s", $where['field'], $where['operator'], $where['value']);
        }

        if (count($wheres) > 0) {
            $query .= sprintf('WHERE %s ', implode(' AND ', $wheres));
        }

        return $query;
    }

    protected function _buildShow() {
        return "SHOW " . $this->_typeShow . ";";

    }

    protected function _buildSet() {
        $option = $this->_options[0];
        return "SET " . $option['name'] . ' = ' . $option['value'] . ";";
    }

    protected function _buildDelete() {
        return "DELETE FROM " . $this->_buildIndexes() . "WHERE id = " . $this->_id . ";";
    }

    protected function _escape_query ( $string ) {
        $qoute_count = substr_count($string, '"');
        if ($qoute_count % 2)
            $string = str_replace('"', '', $string);
        $from = array (   '\\',    '(',    ')',     '!',    '@',    '~',    '&',    '/',    '^',    '$',    '=',   "'",  "\x00",  "\n",  "\r",  "\x1a" );
        $to   = array ( '\\\\', '\\\(', '\\\)',  '\\\!', '\\\@', '\\\~', '\\\&', '\\\/', '\\\^', '\\\$', '\\\=', "\\'", "\\x00", "\\n", "\\r", "\\x1a" );
        return str_replace ( $from, $to, $string );
    }

    /**
     * Builds the query string from the information you've given.
     *
     * @return string The resulting query
     */
    protected function _buildSelect() {
        $fields = array();
        $wheres = array();
        $orders = array();
        $options = array();
        $query = '';

        foreach ($this->_fields as $field) {
            if (!isset($field['field']) OR !is_string($field['field'])) {
                next;
            }
            if (isset($field['alias']) AND is_string($field['alias'])) {
                $fields[] = sprintf("%s AS %s", $field['field'], $field['alias']);
            } else {
                $fields[] = sprintf("%s", $field['field']);
            }
        }

        unset($field);

        if (is_string($this->_search)) {
            $wheres[] = sprintf("MATCH('%s')", $this->_escape_query(($this->_search)));
        }

        foreach ($this->_wheres as $where) {
            $wheres[] = sprintf("%s %s %s", $where['field'], $where['operator'], $where['value']);
        }

        unset($where);

        foreach ($this->_orders as $order) {
            $orders[] = sprintf("%s %s", $order['field'], $order['sort']);
        }

        unset($order);

        foreach ($this->_options as $option) {
            $options[] = sprintf("%s=%s", $option['name'], $option['value']);
        }

        unset($option);

        $query .= sprintf('SELECT %s ', count($fields) ? implode(', ', $fields) : '*');

        $query .= 'FROM ' . $this->_buildIndexes();

        if (count($wheres) > 0) {
            $query .= sprintf('WHERE %s ', implode(' AND ', $wheres));
        }

        if (is_string($this->_group)) {
            $query .= sprintf('GROUP BY %s ', $this->_group);
        }

        if (is_array($this->_groupOrder)) {
            $query .= sprintf('WITHIN GROUP ORDER BY %s %s ', $this->_groupOrder['field'], $this->_groupOrder['sort']);
        }

        if (count($orders) > 0) {
            $query .= sprintf('ORDER BY %s ', implode(', ', $orders));
        }

        $query .= sprintf('LIMIT %d, %d ', $this->_offset, $this->_limit);

        if (count($options) > 0) {
            $query .= sprintf('OPTION %s ', implode(', ', $options));
        }

        while (substr($query, -1, 1) == ' ') {
            $query = substr($query, 0, -1);
        }

        return $query;
    }

    public function setQuery($query) {
        if (!is_string($query)) {
            throw new SphinxQLException ("Query is not string");
        }
        $this->_query = $query;
    }

    public function setType($type) {
        if (!in_array($type, $this->_types)) {
            throw new SphinxQLException ("Wrong query type");
        }
        $this->_typeQuery = $type;
        return $this;
    }

    public function setTypeShow($type) {
        $type = strtoupper($type);

        if (!in_array($type, array('META', 'WARNINGS', 'STATUS'))) {
            throw new SphinxQLException ("Wrong show type");
        }
        $this->_typeQuery = self::QUERY_SHOW;
        $this->_typeShow = $type;
        return $this;
    }

    public function setId($id) {
        $this->_id = $id;
    }

    /**
     * Adds an entry to the list of indexes to be searched.
     *
     * @param string The index to add
     * @return Query $this
     */
    public function addIndex($index) {
        if (!is_string($index)) {
            throw new SphinxQLException ("index is not string");
        }

        if (in_array($index, $this->_indexes)) {
            throw new SphinxQLException ("index with the same name already exists");
        }

        $this->_indexes[] = $index;

        return $this;
    }

    /**
     * Removes an entry from the list of indexes to be searched.
     *
     * @param string The index to remove
     * @return Query $this
     */
    public function removeIndex($index) {
        if (!is_string($index)) {
            throw new SphinxQLException ("index is not string");
        }

        $pos = array_search($index, $this->_indexes);
        unset($this->_indexes[$pos]);
        return $this;
    }

    /**
     * Adds a entry to the list of fields to return from the query.
     *
     * @param string Field to add
     * @param string Alias for that field, optional
     * @return Query $this
     */
    public function addField($field, $alias=null) {
        if (!is_string($field)) {
            throw new SphinxQLException ("field is not string");
        }

        if (!is_string($alias)) {
            $alias = null;
        }

        $this->_fields[] = array('field' => $field, 'alias' => $alias);

        return $this;
    }

    public function addUpdateField($field, $value) {
        $this->_fields[$field] = $value;
        return $this;
    }

    /**
     * Adds multiple entries at once to the list of fields to return.
     * Takes an array structured as so:
     * array(array('field' => 'user_id', 'alias' => 'user')), ...)
     * The alias is optional.
     *
     * @param $fields Array of fields to add
     * @return Query $this
     */
    public function addFields($fields) {
        if (!is_array($fields)) {
            throw new SphinxQLException ("fields must be an array");
        }

        foreach ($fields as $entry) {
            if (!is_array($entry) OR !isset($entry['field']) OR !is_string($entry['field'])) {
                throw new SphinxQLException ("field is not string");
            }
            if (!isset($entry['alias']) OR !is_string($entry['alias'])) {
                $entry['alias'] = null;
            }
            $this->addField($entry['field'], $entry['alias']);
        }

        return $this;
    }

    /**
     * Removes a field from the list of fields to search.
     *
     * @param string Alias of the field to remove
     * @return Query $this
     */
    public function removeField($alias) {
        if (!is_string($alias)) {
            throw new SphinxQLException ("field is not string");
        }

        foreach ($this->_fields AS $key => $value) {
            if (in_array($alias, $value)) {
                unset($this->_fields[$key]);
            }
        }

        return $this;
    }

    /**
     * Removes multiple fields at once from the list of fields to search.
     *
     * @param array List of aliases of fields to remove
     * @return Query $this
     */
    public function removeFields($array) {
        if (!is_array($array)) {
            throw new SphinxQLException ("fields must be an array");
        }

        foreach ($array as $alias) {
            $this->removeField($alias);
        }

        return $this;
    }

    /**
     * Sets the text to be matched against the index(es)
     *
     * @param string Text to be searched
     * @return Query $this
     */
    public function setSearch($search) {
        if (!is_string($search)) {
            throw new SphinxQLException ("search is not string");
        }

        $this->_search = $search;

        return $this;
    }

    /**
     * Removes the search text from the query.
     *
     * @return Query $this
     */
    public function removeSearch() {
        $this->_search = null;
        return $this;
    }

    /**
     * Sets the offset for the query
     *
     * @param integer Offset
     * @return Query $this
     */
    public function setOffset($offset) {
        if (!is_numeric($offset)) {
            throw new SphinxQLException ("offset is not numeric");
        }

        $this->_offset = $offset;
        return $this;
    }

    /**
     * Sets the limit for the query
     *
     * @param integer Limit
     * @return Query $this
     */
    public function setLimit($limit) {
        if (!is_numeric($limit)) {
            throw new SphinxQLException ("limit is not numeric");
        }

        $this->_limit = $limit;

        return $this;
    }

    public function setHits($hits) {
        if (!is_numeric($hits)) {
            throw new SphinxQLException ("Hits is not numeric");
        }
        $this->_hits = $hits;
        return $this;
    }

    /**
     * Adds a WHERE condition to the query.
     *
     * @param string The field/expression for the condition
     * @param mixed The field/expression/value to compare the field to
     * @param string The operator (=, <, >, etc)
     * @param string Whether or not to quote the value, defaults to true
     * @return Query $this
     */
    public function addWhere($field, $value, $operator=null) {

        if (!$operator) {
            $operator = "=";
        }

        if (!in_array($operator, array('=', '!=', '>', '<', '>=', '<=', 'AND', 'NOT IN', 'IN', 'BETWEEN'))) {
            throw new SphinxQLException ("Wrong operator");
        }

        if (in_array($operator, array('NOT IN', 'IN'))) {
            if (!is_array($value)) {
                throw new SphinxQLException ("value should be an array");
            }
            $value = "(" . implode(",", $value) . ")";
        }

        if ($operator == 'BETWEEN') {
            if (!is_array($value)) {
                throw new SphinxQLException ("BETWEEN value should be an array");
            }
            $value = implode(" AND ", $value);
        }

        if (!is_string($field)) {
            throw new SphinxQLException ("field is not string");
        }

        if (!is_string($value)) {
            $value = (string) $value;
        }

        $this->_wheres[md5($field.$operator)] = array('field' => $field, 'operator' => $operator, 'value' => $value);

        return $this;
    }

    /**
     * Removes a WHERE condition from the list of conditions
     *
     * @param string condition to remove
     * @param string the operator
     * @return Query $this
     */
    public function removeWhere($field, $operator = '=') {
        unset($this->_wheres[md5($field.$operator)]);
        return $this;
    }

    /**
     * Sets the GROUP BY condition for the query.
     *
     * @param string The field/expression for the condition
     * @return Query $this
     */
    public function addGroupBy($field) {
        if (!is_string($field)) {
            throw new SphinxQLException ("group by field is not string");
        }
        $this->_group = $field;

        return $this;
    }

    /**
     * Removes the GROUP BY condition from the query.
     *
     * @param string The field/expression for the condition
     * @param string The alias for the result set (optional)
     * @return Query $this
     */
    public function removeGroupBy() {
        $this->_group = null;
        return $this;
    }

    /**
     * Sets the WITHIN GROUP ORDER BY condition for the query. This is a
     * Sphinx-specific extension to SQL.
     *
     * @param string The field/expression for the condition
     * @param string The sort type (can be 'asc' or 'desc', capitals are also OK)
     * @return Query $this
     */
    public function groupOrder($field, $sort) {
        if (!is_string($field)) {
            throw new SphinxQLException ("GROUP ORDER BY field is not string");
        }

        if (!is_string($sort)) {
            throw new SphinxQLException ("GROUP ORDER BY sort is not string");
        }

        $this->_groupOrder = array('field' => $field, 'sort' => $sort);

        return $this;
    }

    /**
     * Removes the WITHIN GROUP ORDER BY condition for the query. This is a
     * Sphinx-specific extension to SQL.
     *
     * @return Query $this
     */
    public function removeGroupOrder() {
        $this->_groupOrder = null;
        return $this;
    }

    /**
     * Adds an OPTION to the query. This is a Sphinx-specific extension to SQL.
     *
     * @param string The option name
     * @param string The option value
     * @return Query $this
     */
    public function addOption($name, $value) {
        if (!is_string($name)) {
            throw new SphinxQLException ("OPTION name is not string");
        }

        $this->_options[] = array('name' => $name, 'value' => $value);

        return $this;
    }

    /**
     * Removes an OPTION from the query.
     *
     * @param string The option name, optional
     * @param string The option value, optional
     * @return Query $this
     */
    public function removeOption($name = null, $value = null) {
        if (!$name) {
            $this->_options = array();
            return $this;
        }
        if (!is_string($name)) {
            throw new SphinxQLException ("OPTION name is not string");
        }

        if ($value AND !is_string($value)) {
            throw new SphinxQLException ("OPTION value is not string");
        }

        foreach ($this->_options as $key => $option) {
            if ($option['name'] == $name AND (!$value OR $value == $option['value'])) {
                unset($this->_options[$key]);
            }
        }

        return $this;
    }

    /**
     * Adds an ORDER condition to the query.
     *
     * @param string The field/expression for the condition
     * @param string The sort type (can be 'asc' or 'desc', capitals are also OK)
     * @return Query $this
     */
    public function addOrderBy($field, $sort = "asc") {

        $sort  = strtolower($sort) == 'asc'? 'asc': 'desc';

        if (!is_string($field)) {
            throw new SphinxQLException ("order field is not string");
        }

        $this->_orders[] = array('field' => $field, 'sort' => $sort);

        return $this;
    }

    /**
     * Removes an ORDER from the query.
     *
     * @param string The option name
     * @return Query $this
     */
    public function removeOrderBy($field = null) {
        if (!$field) {
            $this->_orders = array();
            return $this;
        }

        if (!is_string($field)) {
            throw new SphinxQLException ("order field is not string");
        }


        foreach ($this->_orders as $key => $orders) {
            if ($orders['field'] == $field) {
                unset($this->_orders[$key]);
            }
        }

        return $this;
    }
}