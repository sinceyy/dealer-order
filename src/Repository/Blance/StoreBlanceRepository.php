<?php
declare(strict_types=1);

namespace YddOrder\Repository\Blance;

use sunshine\repository\AbstractRepository;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class StoreBlanceRepository extends BlanceAbstruct
{

    /**
     * 新增金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public function addBlance(int $data_id, int $data_type, $money = 0.00, string $mark = '业务新增金额'): bool
    {
        $model = $this->model->lock(true)->where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        $model->update(['blance' => sprintf('%.2f', $money)]);

        return self::addBlanceLog(sprintf('%.2f', $money), 1, $data_id, $data_type, $mark);
    }

    /**
     * 减少金额
     * @param int    $data_id
     * @param int    $data_type
     * @param float  $money
     * @param string $mark
     * @return bool
     */
    public function reduceBlance(int $data_id, int $data_type, float $money = 0.00, string $mark = '业务消费金额'): bool
    {
        $model = $this->model->lock(true)->where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        $model->update(['blance' => sprintf('%.2f', $money)]);

        return self::addBlanceLog(sprintf('%.2f', $money), 2, $data_id, $data_type, $mark);
    }

}