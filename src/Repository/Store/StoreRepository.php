<?php
declare(strict_types=1);

namespace YddOrder\Repository\Store;

use YddOrder\Model\Store\Store;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class StoreRepository
{
    /**
     * 根据id获取
     * @param int $id
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getInfoById(int $id = 0)
    {
        return Store::where(['id' => $id])->find();
    }

}