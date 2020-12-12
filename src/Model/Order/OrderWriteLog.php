<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 订单model
 * Class Order
 * @package YddOrder\Model\Order
 */
class OrderWriteLog extends Model
{
    use SoftDelete;

    protected $connection = 'order';

}