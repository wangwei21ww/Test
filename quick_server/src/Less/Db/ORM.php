<?php
/**
 * 已经实例化的模型不要重新new，而是用this，否则场景可能不一致
 */
namespace Less\Db;

use Less\Db\Model;

class ORM extends Model
{
    /**
     * 如果 userId = null，
     * 那么除了普通（公开非必须userId=currentUserId）读取不能做任何业务
     * @var mixed
     */
    public static $_userId = null;

    /**
     * The default db connection instance
     * @var object
     */
    public static $_db;

    /**
     * store more than one db conn instance
     * @var array Defaults to an empty array
     */
    protected static $_dbs = [];

    /**
     * The default primary key name
     * @var string
     */
    protected $_defaultPK = 'id';

    /**
     * 是否禁用defaultScope 默认为 false，表示不禁用defaultScope
     * @var boolean defaults to false
     */
    protected $_disableDefaultScope = false;

    /**
     * 如果该值为true，则表示该模型从入口到结束的所有数据库操作都支持事物
     * @var boolean
     */
    protected $_transaction = false;

    /**
     * 该属性用于在ORM的不同模型中使用事务，当一个模型开启了事务，
     * 这个过程中，其它模型执行相关数据操作，也需要在该事务中执行，否则无法成功
     * @var string 默认为0， 表示当前没有token
     */
    protected static $_transactionToken = 0;

    /**
     * 默认数据集大小
     * @var integer
     */
    public static $_defaultLimitSize = 100;

    /**
     * 最大允许读取的数据集大小
     * @var integer
     */
    public static $_maxOffsetSize = 1000;

    /**
     * The methods supported options.
     * the request methods must be one of the _methods.
     * @var array defaults to ['read', 'create', 'update', 'delete']
     */
    public static $_methods = ['read', 'create', 'update'];

    /**
     * Set the model method, that support option like the following:
     * read, create, update, delete, auth
     * @var string defaults to read
     */
    public $_method = 'read';

    /**
     * db name
     * @var string
     */
    protected $_dbName;

    /**
     * db table name
     * @var string
     */
    protected $_tableName;

    /**
     * 模型中的所有字段（属性）
     * @var array
     */
    protected $_attrs = ['id', 'userId', 'createdAt', 'updatedAt'];

    /**
     * 在没有填写该属性值的时候使用, 只在第一次写入数据的时候使用
     * @var array
     */
    protected $_defaultValues = [];

    /**
     * 写入必须覆盖的默认值，写入后可更改，例如 order status=pending
     * @var array
     */
    protected $_defaultEnforceValues = [];

    /**
     * 模型中不能读取的属性
     * @var array
     */
    protected $_deniedReadAttrs = [];

    /**
     * 模型中不能更新的属性
     * @var array
     */
    protected $_deniedUpdateAttrs = ['id', 'userId', 'createdAt'];

    /**
     * SQL 条件占位符模板
     * @var array
     */
    public $operators = [
        '$lt' => '%s < :%s',
        '$lte' => '%s <= :%s',
        '$gt' => '%s > :%s',
        '$gte' => '%s >= :%s',
        '$ne' => '%s != :%s',
        '$in' => '%s IN (:%s)',
        '$nin' => '%s NOT IN (:%s)',
    ];

    /**
     * construct method
     * @param array $options The param options could override the value of the class property
     */
    public function __construct(array $options = [])
    {
        if ($options !== []) {
            foreach ($options as $key => $value) {
                $this->{$key} = $value;
            }
        }
        $this->init();
        DbPool::initDbConn();
    }

    /**
     * Get a DB connection instance
     * @param mixed $useTrans Defaults to false
     * @return object The db connection instance
     */
    public function getDB($useTrans = false)
    {
        if ($useTrans !== false and self::$_transactionToken == 0) {
            self::$_transactionToken = $useTrans;
        }
        if ($useTrans === false and self::$_transactionToken != 0) {
            $useTrans = self::$_transactionToken;
        }
        return DbPool::getDB($useTrans);
    }

    /**
     * close db conn
     * @param mixed $useTrans defaults to false
     */
    public function closeDB($useTrans = false)
    {
        DbPool::closeDB($useTrans);
        self::$_transactionToken = 0;
    }

    /**
     * Get the database table name,
     * if the db name is setted, then return with the table name
     * @return string
     */
    public function getTableName()
    {
        $tablePrefix = 'tbl_';
        if (isset($GLOBALS['app_conf']['db']['tablePrefix'])) {
            $tablePrefix = $GLOBALS['app_conf']['db']['tablePrefix'];
        }
        return ($this->_dbName != '' ? $this->_dbName . '.' : '') . $tablePrefix . $this->_tableName;
    }

    /**
     * Get default primary key name
     * @return string
     */
    public function getPkName()
    {
        return $this->_defaultPK;
    }

    /**
     * Get the PK value
     * @return string
     */
    public function getPk()
    {
        return isset($this->_attributes[$this->_defaultPK]) ? $this->_attributes[$this->_defaultPK] : null;
    }

    /**
     * Model entry method
     */
    public function init()
    {
    }

    /**
     * 验证数据表schema, 所提交数据需要符合struct
     */
    public function baseSchemaValidator()
    {
        return [];
    }

    /**
     * 默认条件，读取只能读取自己的数据
     * @return array
     */
    public function defaultScope()
    {
        return [];
    }

    /**
     * 过滤存储或查询中不存在的属性
     * @param array $attrs
     * @return array
     */
    protected function ensureAttrs($attrs)
    {
        $keys = array_keys($attrs);
        $filters = array_diff($keys, $this->_attrs);
        foreach ($filters as $key => $item) {
            if (isset($attrs[$item])) {
                unset($attrs[$item]);
            }
        }
        return $attrs;
    }

    /**
     * 过滤掉不允许更新或读取的字段
     * @param $type update or read
     */
    protected function filterDeniedAttrs($attrs, $type)
    {
        $name = $type == 'read' ? '_deniedReadAttrs' : '_deniedUpdateAttrs';
        foreach ($this->{$name} as $key => $item) {
            if (isset($attrs[$item])) {
                unset($attrs[$item]);
            }
        }
        return $attrs;
    }

    protected function resolveSQLOperator($items)
    {
        $container = [];
        foreach ($items as $operator => $value) {
            if (!isset($this->operators[$operator])) {
                return \Less::getApp()->addError('The query has an invalid operator', null, 'public');
            }
            $container[] = str_replace('%s', $value, $this->operators[$operator]);
        }
        return join(" AND ", $container);
    }

    /**
     * Create SQL placeholders of condition, and bind values
     * @param array $attrs
     * @return array
     */
    protected function makeAttrPlaceholders($attrs)
    {
        $attrs = $this->ensureAttrs($attrs);
        $keys = array_keys($attrs);
        $conditions = [];
        foreach ($keys as $key => $item) {
            $conditions[] = $item . ' = :' . $item;
        }
        return ['values' => $attrs, 'conditions' => implode(" AND ", $conditions)];
    }

    /**
     * TODO 由于 ORM 在复杂条件下功能不足，需要使用原生sql，但原生sql没有orm特性，需要加上这些特性
     * 使用sql查询，不过可以加入ORM的功能，丰富ORM的不足
     * 支持使用默认scope，过滤字段，不能更改，应用强制默认值，不能读取，过滤转换字段等功能
     * @
     */
    public function ModelQuery($model, $subscribe, $queryType, $sql)
    {
        // 改造 query 应用orm部分功能
    }

    /**
     * The key of the $query['$order'] must be asc or desc and the value must be an array
     * filter $query['$order'], the every value, that must be one of the _attrs
     * @param array $orders
     */
    protected function parseOrder($orders)
    {
        $container = [];
        foreach ($orders as $orderType => $attrs) {
            if (is_array($attrs) and count(array_intersect($attrs, $this->_attrs)) == count($attrs) and $this->validateOrderAttrs($attrs)) {
                $method = strtolower($orderType) == 'asc' ? 'orderByASC' : 'orderByDESC';
                $container[$method] = $attrs;
            }
        }
        return $container;
    }

    /**
     * Validate the attr is valid and must be a string
     * @param array $attrs
     * @return boolean true to valid, false to invalid
     */
    protected function validateOrderAttrs($attrs)
    {
        if (!is_array($attrs)) {
            return false;
        }
        foreach ($attrs as $key => $attr) {
            if (!is_string($attr)) {
                return false;
            }
        }
        return true;
    }

    /**
     * make a sql query with the request JSON query
     * @param array $query
     */
    public function SQLQueryBuilder($query)
    {
        $fields = $this->applyFields(isset($query['$fields']) ? $query['$fields'] : ' * ');

        $conditions = $this->applyScope(isset($query['$where']) ? $query['$where'] : []);

        $attrs = $this->makeAttrPlaceholders($conditions);

        $limit = (isset($query['$limit']) and (int) $query['$limit'] < self::$_maxOffsetSize) ? (int) $query['$limit'] : self::$_defaultLimitSize;

        $page = isset($query['$page']) ? (int) $query['$page'] : 1;

        $orders = (isset($query['$order']) and is_array($query['$order'])) ? $query['$order'] : ['desc' => [$this->_defaultPK]];

        $queries = [
            'select' => $fields,
            'from' => $this->getTableName(),
            'where' => $attrs['conditions'] == '' ? ' 1 ' : $attrs['conditions'],
            'bindValues' => $attrs['values'],
            'limit' => $limit,
            'offset' => $limit * ($page - 1),
        ];

        $queries = array_merge($queries, $this->parseOrder($orders));

        $db = $this->getDB();
        foreach ($queries as $method => $query) {
            $db = call_user_func_array([$db, $method], [$query]);
        }
        return $db;
    }

    /**
     * 把 array 条件组转换为 字符SQL条件
     * @
     */
    public function conditionsStringify(array $conditions)
    {
        $container = [];
        foreach ($conditions as $key => $value) {
            $container[] = $key . '="' . $value . '"';
        }
        return implode(" AND ", $container);
    }

    /**
     * 应用默认scope
     * @param array $conditions
     * @return array
     */
    public function applyScope($conditions)
    {
        if ($this->_disableDefaultScope === true) {
            return $conditions;
        }
        if (!is_array($conditions)) {
            $conditions = [];
        }
        return array_merge($conditions, $this->defaultScope());
    }

    /**
     * unset the item not in _attrs
     * @param mixed $fields array or string
     * @return string
     */
    public function applyFields($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $key => $item) {
                if (!in_array($item, $this->_attrs)) {
                    unset($fields[$key]);
                }
            }
            return [] === $fields ? ' * ' : join(" , ", $fields);
        }
        return ' * ';
    }

    /**
     * 应用默认值，此方法只在第一次写入的时候调用
     * @param array $attrs
     */
    public function applyDefaultValues(array $attrs = [])
    {
        foreach ($this->_defaultValues as $key => $value) {
            if (!isset($attrs[$key]) or (isset($attrs[$key]) and trim($attrs[$key]) == '')) {
                $attrs[$key] = $value;
            }
        }
        foreach ($this->_defaultEnforceValues as $key => $value) {
            $attrs[$key] = $value;
        }

        // if(get_class($this)!='User' and in_array('userId',$this->_attrs)) {
        //   $attrs['userId'] = self::$_userId;
        // }
        $attrs['createdAt'] = $attrs['updatedAt'] = time();
        return $attrs;
    }

    /**
     * TODO to be continued
     * @param string $sql
     */
    public function query($sql)
    {
        return $this->getDB()->query($sql);
    }

    /**
     * 程序内部使用的find方法，同read
     * @param array $conditions defaults to an empty array
     * @return array
     */
    public function find($conditions = [])
    {
        $conditions = $this->applyScope($conditions);
        $attrs = $this->makeAttrPlaceholders($conditions);
        return $this->getDB()->select('*')->from($this->getTableName())->where($attrs['conditions'])->bindValues($attrs['values'])->limit(1)->row();
    }

    /**
     * 程序内部使用的find方法，同read
     * @param array $conditions defaults to an empty array
     * @return array
     */
    public function findAll($conditions = [])
    {
        $conditions = $this->applyScope($conditions);
        $attrs = $this->makeAttrPlaceholders($conditions);
        return $this->getDB()->select('*')->from($this->getTableName())->where($attrs['conditions'])->bindValues($attrs['values'])->query();
    }

    /**
     * 读取多条数据
     * @param array $query
     */
    public function readAll($query = [])
    {
        return $this->read($query, true);
    }

    /**
     * 读取数据
     * @
     */
    public function read($query = [], $all = false)
    {
        if ($this->beforeRead()) {
            $db = $this->SQLQueryBuilder($query);
            $results = $all === true ? $db->query() : $db->limit(1)->row();
        }
        if ($results) {
            $results = $this->afterRead($results, $all);
        }
        return $results;
    }

    /**
     * create an record for the current model
     * @param array $attrs The model data, defaults to an empty array
     * @return boolean
     */
    public function create($attrs = [])
    {
        return $this->save(null, $attrs);
    }

    /**
     * Update attrs by PK
     * @param int $id
     * @param int $attrs
     * @return boolean
     */
    public function updateByPK($id, array $attrs)
    {
        $pk = $this->getPkName();
        $attrs = $this->ensureAttrs($attrs);
        return $this->getDB()->update($this->getTableName())->cols($attrs)->where($pk . ' = :' . $pk)->bindValues([$pk => $id])->row() === 1;
    }

    /**
     * Update the model record
     * @param array $query The query conditions
     * @param array $attrs The specified update data, defaults to an empty array.
     * @return boolean
     */
    public function update($query = [], $attrs = [])
    {
        return $this->save($query, $attrs);
    }

    /**
     * Save the data to the database
     * @param array $query
     * @param array $attrs
     */
    public function save($query = null, $attrs = [])
    {
        $result = false;
        if ($attrs !== []) {
            $this->setAttributes($attrs);
        }
        try {
            $transId = false;
            if ($this->_transaction === true) {
                $transId = md5(microtime());
                $this->getDB($transId)->beginTrans();
            }

            if ($this->_validate() === false or $this->beforeSave($query === null) === false) {
                if ($this->_transaction === true) {
                    $this->getDB($transId)->rollBackTrans();
                }
                return $result;
            }
            $attrs = $this->getAttributes(); // assign the attributes again, may the before method reset it
            $afterReadOne = false;
            if ($query === null) {
                if (isset($this->_attributes[$this->_defaultPK])) { // do not use the id with custom when create record.
                    unset($this->_attributes[$this->_defaultPK]);
                }
                $attrs = $this->applyDefaultValues($this->getAttributes()); // apply default values
                $attrs = $this->ensureAttrs($attrs);
                $this->setAttributes($attrs);
                $result = $this->getDB($transId)->insert($this->getTableName())->cols($attrs)->query(); // return the insert_id
                $afterReadOne = [$this->_defaultPK => $result];
            } else {
                $attrs = $this->filterDeniedAttrs($attrs, 'update'); // denied not allowed updated attributes.
                $attrs = $this->ensureAttrs($attrs);
                $attrs = $this->setLastUpdateTime($attrs);
                $this->setAttributes($attrs);
                $conditions = $this->applyScope(isset($query['$where']) ? $query['$where'] : []); // apply the default scope
                $SQLConditions = $this->conditionsStringify($conditions); // json conditions to sql conditions
                $result = $this->getDB($transId)->update($this->getTableName())->cols($attrs)->where($SQLConditions)->query(); // return the effect rows
                $afterReadOne = $conditions;
            }
            if ($result && $afterReadOne !== false) {
                $result = $this->read(['$where' => $afterReadOne]); // NOTE if there data is more than one, then should to use the first row.
                if (is_array($result) and count($result) > 0) {$this->setAttributes($result);}
                $this->afterSave($query === null);
                if ($this->_transaction === true) {
                    $this->getDB($transId)->commitTrans();
                }
            } else {
                if ($this->_transaction === true) {
                    $this->getDB($transId)->rollBackTrans();
                }
            }
        } catch (Exception $e) {
            if ($this->_transaction === true) {
                $this->getDB($transId)->rollBackTrans();
            }
        }
        return $result;
    }

    /**
     * Set the update time for attrs
     * @param array $attrs
     * @return array
     */
    public function setLastUpdateTime($attrs)
    {
        $attrs['updatedAt'] = time();
        return $attrs;
    }

    /**
     * Remove an record, with query condition
     * @param array $query
     * @return integer Return the number of affected rows.
     */
    public function delete($query)
    {
        try {
            if ($this->_transaction === true) {
                $this->getDB($transId)->beginTrans();
            }
            if ($this->beforeDelete()) {
                $conditions = $this->applyScope(isset($query['$where']) ? $query['$where'] : []);
                $conditions = $this->conditionsStringify($conditions);
                $result = $this->getDB()->delete($this->getTableName())->where($conditions)->query();
            }
            if ($result) {
                $this->afterDelete();
                if ($this->_transaction === true) {
                    $this->getDB()->commitTrans();
                }
            }
        } catch (Exception $e) {
            if ($this->_transaction === true) {
                $this->getDB()->rollBackTrans();
            }
        }
        return $result;
    }

    public function beforeRead()
    {
        return true;
    }

    public function afterRead($result, $all = false)
    {
        $container = [];
        if ($all === true) {
            foreach ($result as $key => $item) {
                $container[] = $this->filterDeniedAttrs($item, 'read');
            }
        } else {
            $container = $this->filterDeniedAttrs($result, 'read');
        }
        return $container;
    }

    public function beforeSave($insert)
    {
        if ($insert === false) {
            $this->updatedAt = time();
        }
        return true;
    }

    public function afterSave($insert)
    {}

    public function beforeDelete()
    {
        return true;
    }

    public function afterDelete()
    {}

    // relation 暂不重要，不需要处理

    // auth 后续再做，前期默认scope userId = current user id.

    // rules 用于验证器验证模型属性的规则，需要做。
}
