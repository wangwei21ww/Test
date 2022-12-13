<?php

use Less\Db\ORM;

class Base extends ORM
{
    /**
      * format time for unix time
      * @param string $records
      * @param boolean $all defaults to true
      * @return string
      */
    protected function formatTime($records, $all=true)
    {
      if($all) {
        foreach ($records as $key => $item) {
          $records[$key] = $this->formatTime($item, false);
        }
      }else{
        if(isset($records['createdAt'])) {
          $records['createdAt'] = date('Y-m-d H:i:s',$records['createdAt']);
        }
        if(isset($records['updatedAt'])) {
          $records['updatedAt'] = date('Y-m-d H:i:s',$records['updatedAt']);
        }
      }
      return $records;
    }

    /**
      * Get the app nonce
      * @return string
      */
    public function getAppNonce()
    {
        $BKUUID = isset($_SERVER['BKUUID']) ? $_SERVER['BKUUID'] : '';
        $appId = $GLOBALS['sdkHeaders']['appId'];
        $appUserId = $GLOBALS['sdkHeaders']['appUserId'];
        $token = $appId.$appUserId.$BKUUID;
        if(!isset(AppBootstrap::$nonces[$token])) {
          throw new Exception("The request nonce not exists", 391144);
        }
        return AppBootstrap::$nonces[$token];
    }

    /**
      * Get the userId
      * @return string
      */
    public function getUserId()
    {
        return hash('sha256', strtolower($this->getAppId().$this->getAppUserId()));
    }

    /**
      * Get the appId from HTTP request token
      * @return string
      */
    public function getAppId()
    {
        if(!isset($GLOBALS['sdkHeaders']['appId'])) {
            throw new Exception("The appId not in HTTP Request headers", 40313);
        }
        return strtolower(trim($GLOBALS['sdkHeaders']['appId']));
    }

    /**
      * Get the appUserId from HTTP request token
      * @return string
      */
    public function getAppUserId()
    {
        if(!isset($GLOBALS['sdkHeaders']['appUserId'])) {
            throw new Exception("The appUserId not in HTTP Request headers", 40314);
        }
        return strtolower(trim($GLOBALS['sdkHeaders']['appUserId']));
    }

    /**
     * Get records
     *
     * @param [type] $tbl
     * @param [type] $fields
     * @param [type] $condition
     * @param [type] $bind
     * @param array $groupBy sort key
     * @param array $sortBy sort key
     * @param integer $page
     * @param integer $size
     * @return void
     */
  /**
   * Get records
   *
   * @param [type] $tbl
   * @param [type] $fields
   * @param [type] $condition
   * @param [type] $bind
   * @param array $groupBy sort key
   * @param array $sortBy sort key
   * @param integer $page
   * @param integer $size
   * @return void
   */
  public function getRecords($tbl, $fields, $condition, $bind, $groupBy = [], $sortBy = ['id'], $page = 1, $size = 100)
  {
    if ($page < 1) {
      throw new Exception('The page must be more than 0', 984211);
    }

    if ($size > 100 or $size < 10) {
      throw new Exception('The size must less than 100 and more than 10', 984711);
    }

    $count = $this->getDB()->select('count(id) as _total')->from($tbl)->where($condition)->groupBy($groupBy)->bindValues($bind)->row();

    $data = $this->getDB()->select($fields)->from($tbl)->where($condition)->groupBy($groupBy)->bindValues($bind)->limit((int) $size)->offset($size * ($page - 1))->orderByDESC($sortBy)->query();

    $rows = [
      'total' => (string) (isset($count['_total']) ? $count['_total'] : 0),
      'items' => is_array($data) ? $data : []
    ];
    return $rows;
  }

    public function translates($data)
    {
      $model = strtolower(get_class($this));
      $language = getHeader('language');
      foreach($data as $key=>$item) {
        $trans = $this->translate($model, $item[$this->getPkName()], $language);
        if($trans!==false) {
          $data[$key] = array_merge($item, $trans);
        }
      }
      return $data;
    }

    public function translate($model, $id, $language)
    {
      $trans = (new Translate)->find(['model'=>$model, 'bid'=>$id, 'language'=>$language]);
      if(isset($trans['attrs'])) {
        $attrs = json_decode($trans['attrs'], true);
        return $attrs;
      }
      return false;
    }

    public function transFind($condition=[])
    {
      $language = getHeader('language');
      $model = strtolower(get_class($this));
      $language = $language;
      $translate = 'table';
      if(in_array('language', $this->_attrs) and !isset($condition['language'])) {
        $condition['language'] = $language;
      }else{
        $translate = 'model';
      }
      // $data = $this->convertFields($this->find($condition));
      $data = $this->find($condition);
      if($translate == 'model') {
        $trans = $this->translate($model, $data['id'], $language);
        if($trans!==false) {
          $data = array_merge($data, $trans);
        }
      }
      return $data;
    }

    public function convertFields($data)
    {
      foreach($data as $key=>$val) {
        if($this->isJson($val)) {
          $data[$key] = json_decode($val, true);
        }
      }
      return $data;
    }

    /**
     * Is JSON
     *
     * @param [type] $string
     * @return boolean
     */
    public function isJson($string)
    {
      json_decode($string);
      return (json_last_error() == JSON_ERROR_NONE);
     }

  /**
   * The param name is exists in attrs or not
   *
   * @param [type] $name
   * @return boolean
   */
  public function inAttrs($name)
  {
    return in_array($name, $this->_attrs);
  }
}
