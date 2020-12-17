<?php
declare(strict_types=1);

namespace YddOrder\Repository\Blance;

use sunshine\repository\AbstractRepository;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\InvalidArgumentException;
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
        $model = $this->model::where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        if ($model) {
            $up = $model::where(['data_id' => $data_id, 'data_type' => $data_type])->update(['blance' => sprintf('%.2f', $money)]);
            if ($up === false) throw new Exception('新增金额失败');
        } else {
            $ad = $this->model::create([
                'data_id'   => $data_id,
                'data_type' => $data_type,
                'blance'    => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('新增金额失败');
        }
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
        $model = $this->model::where(['data_id' => $data_id, 'data_type' => $data_type])->find();

        if ($model) {
            $up = $this->model::where(['data_id' => $data_id, 'data_type' => $data_type])->update(['blance' => sprintf('%.2f', $money)]);
            if ($up === false) throw new Exception('减少金额失败');
        } else {
            $ad = $this->model::create([
                'data_id'   => $data_id,
                'data_type' => $data_type,
                'blance'    => sprintf('%.2f', $money)
            ]);
            if ($ad === false) throw new Exception('减少金额失败');
        }

        return self::addBlanceLog(sprintf('%.2f', $money), 2, $data_id, $data_type, $mark);
    }

}