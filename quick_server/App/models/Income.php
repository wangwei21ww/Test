<?php

class Income extends Base
{
    protected $_tableName = 'incomes';

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
     * 默认数据集大小
     * @var integer
     */
    static public $_defaultLimitSize = 100;

    /**
     * 最大允许读取的数据集大小
     * @var integer
     */
    static public $_maxOffsetSize = 1000;

    /**
     * 支持哪些HTTP请求方法
     * the request methods must be one of the _methods.
     * @var array defaults to ['read', 'create', 'update', 'delete']
     */
    static public $_methods = ['read', 'create', 'update', 'delete'];

    /**
     * 模型中的所有字段（属性）
     * @var array
     */
    protected $_attrs = ['id', 'totalAmount', 'incomeDate', 'productId', 'createdAt'];

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
    protected $_deniedUpdateAttrs = ['id'];


    /**
     * 模型验证规则，其中包含验证场景
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * 模型默认查询范围，应用于SQL条件查询
     * @return array
     */
    public function defaultScope()
    {
        return [];
    }

    /**
     * 该方法必须返回true，系统才会继续下一个步骤
     */
    public function beforeSave($insert)
    {
        return true;
    }

    /**
     * 如果该方法无法执行成功，需要抛出一样，以便在使用事务时回滚
     */
    public function aftereSave()
    {
    }
}
