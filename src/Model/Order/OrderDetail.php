<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

/**
 * 订单商品model
 * Class OrderDetail
 * @package YddOrder\Model\Order
 */
class OrderDetail extends Model
{
    use SoftDelete;

    protected $connection = 'order';

    protected $autoWriteTimestamp = true;
}