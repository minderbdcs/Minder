<?php
/**
 * Minder
 *
 * @category  Minder
 * @package   Minder_DAO
 * @copyright Copyright (c) 2008 Barcoding & Data Collection Systems (http://www.barcoding.com.au/)
 */

/**
 * DAO providing methods for creating, retrieving, updating and deleting
 * @category  Minder
 * @package   Minder_DAO
 * @copyright Copyright (c) 2008 Barcoding & Data Collection Systems (http://www.barcoding.com.au/)
 */
class Minder_DAO_CRUD extends Minder_DAO
{
    protected $key;
    public $errors;
    protected $filters;
    protected $validators;
    protected $autoCompletes;
    protected $lookups;
    protected $createRequiresKey;


    public function __construct()
    {
        parent::__construct();

        $this->key = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1)) . '_id';
        $this->errors = null;
        $this->filters = null;
        $this->validators = null;
        $this->autoCompletes = null;
        $this->lookups = null;
        $this->createRequiresKey = false;
    }

    public function delete($criteria)
    {
        $table = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1));
        $sql = null;
        $data = array();
        if (is_array($criteria)) {
            if (isset($criteria['cond'])) {
                $sql = 'DELETE FROM ' . $table . ' WHERE ' . $criteria['cond'];
            }
            if (isset($criteria['query'])) {
                $sql = $criteria['query'];
            }
            if (isset($criteria['data']) && is_array($criteria['data'])) {
                $data = $criteria['data'];
            }
        } else {
            $sql = 'DELETE FROM ' . $table . ' WHERE ' . $this->key . ' = ?';
            $data = array($criteria);
        }

        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt === false) {
            return false;
        }
        array_unshift($data, $stmt);
        $result = call_user_func_array('ibase_execute', $data);
        if ($result === false) {
            $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
            ibase_free_query($stmt);
            return false;
        }
        ibase_free_result($result);
        ibase_free_query($stmt);
        return true;
    }

    public function find($id)
    {
        $table = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1));
        $sql = 'SELECT *  FROM ' . $table . ' WHERE ' . $this->key . ' = ?';
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt === false) {
            return false;
        }
        $result = ibase_execute($stmt, $id);
        if ($result !== false) {
            $row = ibase_fetch_assoc($result);
            if ($row !== false) {
                $dtoName = substr(get_class($this), 0, -3);
                $dto = new $dtoName();
                $this->init($dto);
                foreach ($dto as $k => $v) {
                    $field = strtoupper(preg_replace('/[A-Z]/', '_$0', $k));
                    $dto->$k = $row[$field];
                }
                ibase_free_result($result);
                ibase_free_query($stmt);
                return $dto;
            }
        }
        ibase_free_query($stmt);
        return null;
    }

    public function findAll($search)
    {
        $table = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1));
        $sql = 'SELECT ';
        if ($search !== null) {
            if ($search->limit !== null) {
                $sql = $sql . ' FIRST ' . $search->limit;
            }
            if ($search->offset !== null) {
                $sql = $sql . ' SKIP ' . $search->offset;
            }
        }
        $data = array();
        if ($search !== null) {
            $sql = $sql . '*  FROM ' . $table;
            if ($search->condition !== null) {
                $sql = $sql . ' WHERE ' . $search->condition;
            }
            if ($search->order !== null) {
                $sql = $sql . ' ORDER BY ' . $search->order;
            }
        }
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt !== false) {
            $data = $search->data;
            array_unshift($data, $stmt);
            $result = call_user_func_array('ibase_execute', $data);
            if ($result !== false) {
                $dtoName = substr(get_class($this), 0, -3);
                $results = array();
                while (($row = ibase_fetch_assoc($result)) !== false) {
                    $dto = new $dtoName();
                    $this->init($dto);
                    foreach ($dto as $k => $v) {
                        $field = strtoupper(preg_replace('/[A-Z]/', '_$0', $k));
                        $dto->$k = $row[$field];
                    }
                    $results[] = $dto;
                }
                ibase_free_result($result);
                ibase_free_query($stmt);
                return $results;
            }
            ibase_free_query($stmt);
        }
        return null;
    }

    public function update($id, $dto)
    {
        if ($this->{$this->key} === null) {
            $this->{$this->key} = $id;
        }

        if (!$this->validate($dto)) {
            return false;
        }

        $table = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1));
        $sql = 'UPDATE ' . $table . ' SET ';
        $data = array();
        foreach($dto as $k => $v) {
            if ($k[0] !== '_') {
                $dbField = strtolower(preg_replace('/[A-Z]/', '_$0', $k));
                if ($dbField !== $this->key) {
                    $sql = $sql . $dbField . ' = ?, ';
                    $data[] = $v;
                }
            }
        }
        $sql = substr($sql, 0, -2) . ' WHERE ' . $this->key . ' = ?';
        $data[] = $id;
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt !== false) {
            array_unshift($data, $stmt);
            $result = call_user_func_array('ibase_execute', $data);
            if ($result === false) {
                $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
                ibase_free_query($stmt);
                return false;
            }

            ibase_free_result($result);
            ibase_free_query($stmt);
        }

        return true;
    }

    public function create($dto)
    {
        $validators = $this->validators;
        if (!$this->createRequiresKey && isset($validators[$this->key])) {
            if (is_array($validators[$this->key])) {
                $validators[$this->key]['allowEmpty'] = true;
            } else {
                $validators[$this->key] = array($validators[$this->key], 'allowEmpty' => true);
            }
        }

        if (!$this->validate($dto, $validators)) {
            return false;
        }

        $table = strtolower(substr(preg_replace('/[A-Z]/', '_$0', substr(get_class($this), 0, -3)), 1));
        $sql = 'INSERT INTO ' . $table . ' (';
        $sql2 = ') VALUES (';
        $data = array();
        foreach($dto as $k => $v) {
            if ($k[0] !== '_') {
                $sql = $sql . strtolower(preg_replace('/[A-Z]/', '_$0', $k)) . ', ';
                $sql2 = $sql2 . '?, ';
                $data[] = $v;
            }
        }
        $sql = substr($sql, 0, -2);
        $sql2 = substr($sql2, 0, -2);
        $sql = $sql . $sql2 . ')';
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt === false) {
            $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
            return false;
        }
        array_unshift($data, $stmt);
        $result = call_user_func_array('ibase_execute', $data);
        if ($data === false) {
            $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
            ibase_free_query($stmt);
            return false;
        } else {
            $key = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->key)));
            $key = strtolower($key[0]) . substr($key, 1);
            if (!$this->createRequiresKey && $dto->$key === null) {
                // Need some way to get the last insert id for firebird??
            }
        }
        ibase_free_result($result);
        ibase_free_query($stmt);
        return true;
    }

    public function init($dto)
    {
    }

    public function saveAttrs($dto, $data)
    {
        $zif = new Zend_Filter_Input($this->filters, null, $data);

        foreach ($dto as $attr => $val) {
            if ($attr[0] !== '_') {
                $formName = strtolower(preg_replace('/([A-Z])/', '_$1', $attr));
                if (isset($data[$formName])) {
                    $dto->$attr = $zif->$formName;
                }
            }
        }
    }

    public function validate($dto, $validators = null)
    {
        // If the developer really doesn't care then neither do I
        if ($this->validators === null) {
            return true;
        }

        // Turn the public properties of the object back into an array
        $data = array();
        foreach ($dto as $attr => $val) {
            if ($attr != '_') {
                $formName = strtolower(preg_replace('/([A-Z])/', '_$1', $attr));
                $data[$formName] = $dto->$attr;
            }
        }

        // Use Zend_Filter_Input to validate all of the array elements
        if ($validators === null) {
            $validators = $this->validators;
        }
        $zif = new Zend_Filter_Input(null, $validators, $data);
        if ($zif->hasUnknown()) {
            ob_end_clean();
            header('Content-type: text/plain');
            echo "Please provide validators for the following fields in " . get_class($dto) . ":\n";
            print_r($zif->getUnknown());
            exit(0);
        }
        if ($zif->hasInvalid() || $zif->hasMissing()) {
            $this->errors = $zif->getMessages();
            return false;
        }

        // If everything was ok then the set errors to null and return true
        $this->errors = null;
        return true;
    }

    public function autoComplete($field, $val, $params)
    {
        $funcName = 'autoComplete' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        if (method_exists($this, $funcName)) {
                    return $this->$funcName($params);
        }

        if (!$this->autoCompletes[$field]) {
            return null;
        }

        $sql = $this->autoCompletes[$field];

        $data = null;
        if (is_array($sql) && isset($sql[1])) {
            $data = array();
            foreach ($sql[1] as $v) {
                $i = strpos($v, '*');
                if ($i !== false) {
                    $data[] = str_replace('*', $val, $v);
                } else if (!isset($params[$v])) {
                    return null;
                } else {
                    $data[] = $params[$v];
                }
            }
            $sql = $sql[0];
        } else {
            $data = array($val);
        }

        $results = null;
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt !== false) {
            array_unshift($data, $stmt);
            $result = call_user_func_array('ibase_execute', $data);
            if ($result !== false) {
                $dtoName = substr(get_class($this), 0, -3);
                $results = array();
                while (($row = ibase_fetch_row($result)) !== false) {
                    $results[$row[0]] = $row[1];
                }
                ibase_free_result($result);
                ibase_free_query($stmt);
            } else {
                $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
                ibase_free_query($stmt);
                return null;
            }
        } else {
            $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
            return null;
        }
        return $results;
    }

    /**
     * This function returns an associate array (key => value pairs) of
     * possible values for the specified field. The optional params contains
     * an array of values that may be used.
     *
     * $opts = $dao->lookup('some_field');
     *
     * When called this function tries to locate a function called
     * lookupSomeField() where SomeField is the upper camel case version of
     * the field passed in. If this function is found then it is called and its
     * value returned.
     *
     * If that function is not found then the key some_field is looked up in
     * the array $this->lookups. It's value must contain an array with 1 or 2
     * elements. The first is a SQL string to execute. The second is a list of
     * parameters to pass to the with the SQL query. The values come from
     * corresponding params in $params.
     *
     * Imagine that the DAO contains
     * $lookups = array (
     *     'room_id' => array('SELECT * FROM ROOMS WHERE OFFICE_ID = ?', array('office_id)));
     *
     * It can then be called as follows to retrieve the lookup values for
     * room_id for the specified office_id.
     *
     * $params = array('office_id' => 1);
     * $dao->lookup('room_id', $params);
     *
     * @param string $field
     * @param array $params
     * @return array or null
     */
    public function lookup($field, $params = null)
    {
        $funcName = 'lookup' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        if (method_exists($this, $funcName)) {
            return $this->$funcName($params);
        }

        if (!$this->lookups[$field]) {
            return null;
        }

        $sql = $this->lookups[$field];
        $data = null;
        $style = 'L';
        if (is_array($sql) && isset($sql[1])) {
            $data = array();
            foreach ($sql[1] as $v) {
                if (!isset($params[$v])) {
                    return null;
                }
                $data[] = $params[$v];
            }
            if (isset($sql['style'])) {
                $style = $sql['style'];
            }
            $sql = $sql[0];
        }

        $results = null;
        $stmt = ibase_prepare(Minder_DAO::$_db, $sql);
        if ($stmt !== false) {
            array_unshift($data, $stmt);
            $result = call_user_func_array('ibase_execute', $data);
            if ($result === true) {
                $dtoName = substr(get_class($this), 0, -3);
                $results = array();
                while ($style != '' && $style[0] != 'L') {
                    switch($style[0]) {
                        case 'N':
                            $results['_none_'] = 'None';

                            break;
                        case 'A':
                            $results['_any_'] = 'Any';
                            break;

                        case 'B':
                            $results[''] = '';
                            break;

                        case 'P':
                            $results['_select_'] = 'Please select...';
                            break;
                    }
                    $style = substr($style, 1);
                }
                while (($result = ibase_fetch_assoc($result)) !== false) {
                    $results[$result[0]] = $result[1];
                }
                while ($style != '' && $style[0] != 'L') {
                    switch($style[0]) {
                        case 'N':
                            $results['_none_'] = 'None';

                            break;
                        case 'A':
                            $results['_any_'] = 'Any';
                            break;

                        case 'B':
                            $results[''] = '';
                            break;

                        case 'P':
                            $results['_select_'] = 'Please select...';
                            break;
                    }
                    $style = substr($style, 1);
                }
                ibase_free_result($result);
                ibase_free_query($stmt);
            } else {
                $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
                ibase_free_query($stmt);
                return null;
            }
        } else {
            $this->errors['dao'] = ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql;
            return null;
        }
        return $results;
    }
}
