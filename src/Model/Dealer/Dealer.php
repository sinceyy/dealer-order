<?php

declare(strict_types=1);

namespace YddOrder\Model\Dealer;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 经销商model
 * Class Express
 * @package app\model\express
 */
class Dealer extends Model
{
    use SoftDelete;

    protected $connection = 'brand';

    protected $autoWriteTimestamp = true;
}