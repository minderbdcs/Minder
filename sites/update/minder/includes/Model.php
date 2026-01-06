<?php
class Model
{
    public function save($data)
    {
        $objMinder = Minder::getInstance();
        foreach ($data as $key => $value) {
            if($objMinder->isValidDate($value)){
                $data[$key] = $objMinder->getFormatedDateToDb($value, "d.m.Y");
            }
        } 
        //echo "<pre>"; die(print_r($data));

        $rc = new ReflectionClass(get_class($this));

        foreach ($rc->getProperties() as $prop) {
            if ($prop->isPublic()) {
                $propName = $prop->getName();
                $formName = strtolower(preg_replace('/([A-Z])/', '_$1', $propName));
                if (isset($data[$formName])) {
                    if (gettype($data[$formName]) == 'string') {
                        $this->$propName = trim($data[$formName]);
                    } else {
                        $this->$propName = $data[$formName];
                    }
                }
            }
        }
        return $this->validate($data);
    }

    public function validate($data)
    {
        return true;
    }
}
