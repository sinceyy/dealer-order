<?php

declare(strict_types=1);

namespace YddOrder\Model\Member;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 订单model
 * Class Order
 * @package YddOrder\Model\Order
 */
class Member extends Model
{
    use SoftDelete;
}