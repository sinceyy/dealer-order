<?php

declare(strict_types=1);

namespace YddOrder\Model\Blance;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 物流公司model
 * Class Express
 * @package app\model\express
 */
class DealerBlance extends Model
{
    use SoftDelete;

    protected $connection = 'brand';

    protected $autoWriteTimestamp = true;
}