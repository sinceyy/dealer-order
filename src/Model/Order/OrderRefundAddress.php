<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 退换货地址model
 * Class OrderRefundAddress
 * @package app\model\order\order
 */
class OrderRefundAddress extends Model
{
    use SoftDelete;

    protected $connection = 'brand';
}