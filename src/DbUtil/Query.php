<?php
declare( strict_types=1 );


namespace YddOrder\DbUtil;

use think\db\BaseQuery;
use think\db\Query as ThinkQuery;

class Query extends  ThinkQuery
{
    /**
     * 执行数据库Xa事务
     * @access public
     * @param  callable $callback 数据操作方法回调
     * @param  array    $dbs      多个查询对象或者连接对象
     * @return mixed
     * @throws PDOException
     * @throws \Exception
     * @throws \Throwable
     */
    public function transactionXa($callback, array $dbs = [])
    {
        if (empty($dbs)) {
            $dbs[] = $this->getConnection();
        }
        $xid_data = [];
        //根据事务中数据库个数生成xid
        foreach ($dbs as $key => $db) {
            $xid_data[$key] = uniqid('xa');
        }

        $xid_data[$key];

        foreach ($dbs as $key => $db) {
            if ($db instanceof BaseQuery) {
                $db = $db->getConnection();

                $dbs[$key] = $db;
            }

            $db->startTransXa($xid_data[$key]);
        }

        try {
            $result = null;
            if (is_callable($callback)) {
                $result = call_user_func_array($callback, [$this]);
            }

            foreach ($dbs as $db) {
                $db->prepareXa($xid_data[$key]);
            }

            foreach ($dbs as $db) {
                $db->commitXa($xid_data[$key]);
            }

            return $result;
        } catch (\Exception $e) {
            //执行prepareXa方法改变事务状态
            foreach ($dbs as $key => $db) {
                $db->prepareXa($xid_data[$key]);
            }

            foreach ($dbs as $key => $db) {
                //每个事务操作使用自己的xid
                $db->rollbackXa($xid_data[$key]);
            }
            throw $e;
        } catch (\Throwable $e) {
            //执行prepareXa方法改变事务状态
            foreach ($dbs as $key => $db) {
                $db->prepareXa($xid_data[$key]);
            }

            foreach ($dbs as $key => $db) {
                //每个事务操作使用自己的xid
                $db->rollbackXa($xid_data[$key]);
            }
            throw $e;
        }
    }
}