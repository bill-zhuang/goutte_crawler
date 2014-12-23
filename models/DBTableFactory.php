<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 上午10:28
 */

require_once '../library/medoo.php';
class DBTableFactory
{
    /**
     * @var medoo
     */
    private $_adapter_db;
    private $_section;

    public function __construct($section = 'local')
    {
        $this->_section = $section;
        $this->_setConfig($section);
    }

    /**
     * @param $table_name
     * @param $columns
     * @param array $where format: [key[type(>/>=/</<=/!=/=/...)] => value, ...]
     * @return array|bool
     */
    public function selectAll($table_name, $columns, array $where)
    {
        return $this->_adapter_db->select($table_name, $columns, $where);
    }

    /**
     * @param $table_name
     * @param $columns
     * @param array $where format: [key[type(>/>=/</<=/!=/=/...)] => value, ...]
     * @return array|bool
     */
    public function selectOne($table_name, $columns, array $where)
    {
        return $this->_adapter_db->get($table_name, $columns, $where);
    }

    /**
     * @param $table_name
     * @param array $where format: [key[type(>/>=/</<=/!=/=/...)] => value, ...]
     * @return array|bool
     */
    public function selectCount($table_name, array $where)
    {
        return $this->_adapter_db->count($table_name, null, null, $where);
    }

    public function insert($table_name, array $data)
    {
        $affect_rows = $this->_adapter_db->insert($table_name, $data);

        return $affect_rows;
    }

    public function update($table_name, array $data, $where)
    {
        $affect_rows = $this->_adapter_db->update($table_name, $data, $where);

        return $affect_rows;
    }

    private function _setConfig($section)
    {
        $section = ($section == '') ? 'local' : $section;
        $db_configs = parse_ini_file('../config/db.ini', true);
        if (!empty($db_configs[$section]))
        {
            $this->_adapter_db = new medoo($db_configs[$section]);
        }
    }
} 