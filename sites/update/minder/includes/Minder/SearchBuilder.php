<?php

class Minder_SearchBuilder
{
    public $condition;
    public $data;
    public $limit;
    public $offset;
    public $fields;
    public $order;

    public function __construct($criteria = null)
    {
        $this->build($criteria);
    }

    public function build($criteria)
    {
        $this->condition = '';
        $this->data = array();
        $this->limit = null;
        $this->offset = null;
        $this->order = null;

        if ($criteria !== null) {
            foreach ($criteria as $k => $v) {
                if ($v !== '') {
                    switch ($k) {
                    case '_page':
                        $this->page = (int)$v;
                        $this->fields['_page'] = (int)$v;
                        break;

                    case '_show':
                        $this->show = (int)$v;
                        $this->fields['_show'] = (int)$v;
                        break;

                    default:
                        $k = strtolower($k);
                        if (strlen($k) > 3) {
                            switch (substr($k, -3)) {
                                case '_be':   // BEGINS
                                    $this->condition .= substr($k, 0, -3) . ' LIKE ? AND ';
                                    $this->data[] = $v . '%';
                                    break;
                                case '_co':   // CONTAINS
                                    $this->condition .= substr($k, 0, -3) . ' LIKE ? AND ';
                                    $this->data[] = '%' . $v . '%';
                                    break;
                                case '_en':   // ENDS
                                    $this->condition .= substr($k, 0, -3) . ' LIKE ? AND ';
                                    $this->data[] = '%' . $v;
                                    break;
                                case '_eq':   // EQUALS
                                    $this->condition .= substr($k, 0, -3) . ' = ? AND ';
                                    $this->data[] = $v;
                                    break;
                                case '_ge':   // GREATER THAN or EQUAL TO
                                    $this->condition .= substr($k, 0, -3) . ' >= ? AND ';
                                    $this->data[] = $v;
                                    break;
                                case '_gt':   // CREATER THAN
                                    $this->condition .= substr($k, 0, -3) . ' > ? AND ';
                                    $this->data[] = $v;
                                    break;
                                case '_le':   // LESS THAN or EQUAL TO
                                    $this->condition .= substr($k, 0, -3) . ' <= ? AND ';
                                    $this->data[] = $v;
                                    break;
                                case '_lt':   // LESS THAN
                                    $this->condition .= substr($k, 0, -3) . ' < ? AND ';
                                    $this->data[] = $v;
                                    break;
                                case '_ne':   // NOT EQUALS
                                    $this->condition .= substr($k, 0, -3) . ' != ? AND ';
                                    $this->data[] = $v;
                                    break;
                                default:    // EQUALS
                                    $this->condition .= $k . ' = ? AND ';
                                    $this->data[] = $v;
                                    break;
                            }
                        }
                        $k = str_replace(' ', '', ucwords(str_replace('_', ' ', $k)));
                        $k = strtolower($k[0]) . substr($k, 1);
                        $this->fields[$k] = $v;
                    }
                }
            }
        }
        if (strlen($this->condition) > 0) {
             $this->condition = substr($this->condition, 0, -5);
        }
        if ($this->condition == '') {
            $this->condition = null;
        }
    }

    public function __get($k) {
        if (isset($this->fields[$k])) {
            return $this->fields[$k];
        }
        return null;
    }

    public function __isset($k) {
        return isset($this->fields[$k]);
    }

    public function __unset($k) {
        unset($this->fields[$k]);
    }
}
