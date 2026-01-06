<?php

class Minder_SysScreen_View_RelationBuilder {

    protected function _getViewTables($screenName) {
        $tableBuilder = new Minder_SysScreen_PartBuilder_Table($screenName);
        return $tableBuilder->build();
    }

    protected function _getSubViewScreenNames($tables) {
        return array_unique(
            array_filter(
                array_map(
                    function($table){
                        $parts = explode(':', $table['SST_TABLE']);

                        return count($parts) > 1 ? (string)$parts[1] : '';
                    },
                    $tables
                ),
                function($screenName){
                    return strlen($screenName) > 0;
                }
            )
        );
    }

    protected function _getBaseScreen($screenName, $visited) {
        if (in_array($screenName, $visited)) {
            throw new Minder_SysScreen_View_BuilderException('Union cycle detected wile processing screen: '. $screenName);
        }

        $visited[] = $screenName;

        $builder = new Minder_SysScreen_PartBuilder_BaseScreen($screenName);
        $screens = $builder->build();

        if (count($screens) > 0) {
            $candidateScreen = current($screens);
            return $this->_getBaseScreen($candidateScreen['SS_NAME'], $visited);
        }
        else {
            return $screenName;
        }
    }

    protected function _getScreenRelations($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_SlaveRelations($screenName);

        $result = array();
        $relations = $builder->build();

        foreach ($relations as &$relation) {
            $relation['MASTER_SCREEN'] = $screenName;
            $slaveScreenName = $this->_getBaseScreen($relation['SS_NAME'], array());
            $relation['SLAVE_SCREEN'] = $slaveScreenName;

            $result[$slaveScreenName] = isset($result[$slaveScreenName]) ? $result[$slaveScreenName] : array();
            $result[$slaveScreenName][] = $relation;
        }

        return $result;
    }

    protected function _getUnionRelations($screenName, $visited, $subScreenNames) {
        $result = array();

        foreach ($subScreenNames as $subScreenName) {
            $result = array_merge($result, $this->_getRelations($subScreenName, $visited));
        }

        foreach ($result as &$relation) {
            $relation['MASTER_SCREEN'] = $screenName;
        }

        return $result;
    }

    protected function _getRelations($screenName, $visited) {
        if (in_array($screenName, $visited)) {
            throw new Minder_SysScreen_View_BuilderException('External Key cycle detected wile processing screen: '. $screenName);
        }

        $visited[] = $screenName;

        $subScreenNames = $this->_getSubViewScreenNames($this->_getViewTables($screenName));

        if (count($subScreenNames) > 0) {
            return $this->_getUnionRelations($screenName, $visited, $subScreenNames);
        } else {
            return $this->_getScreenRelations($screenName, $visited);
        }
    }

    public function getSlaveSysScreens($screenName) {
        return $this->_getRelations($screenName, array());
    }
}