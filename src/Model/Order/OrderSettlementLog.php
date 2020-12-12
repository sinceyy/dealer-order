<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\relation\BelongsTo;
use think\model\concern\SoftDelete;

/**
 * 结算管理model
 * Class OrderRefund
 * @package YddOrder\Model\Order
 */
class OrderSettlementLog extends Model
{
    use SoftDelete;

    protected $connection = 'order';

    protected $autoWriteTimestamp = true;

    /**
     * 关联订单主表
     * @return BelongsTo
     */
    public function orderMaster(): BelongsTo
    {
        return $this->belongsTo('Order');
    }

}