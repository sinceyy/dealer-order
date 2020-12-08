<?php

declare(strict_types=1);

namespace YddOrder\Model\Order;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 物流收货人信息model
 * Class OrderAddress
 * @package app\model\order\order
 */
class OrderAddress extends Model
{
    use SoftDelete;
}