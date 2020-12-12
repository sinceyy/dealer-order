<?php
declare(strict_types=1);

namespace YddOrder\Repository\Order;

use YddOrder\Calculation\OrderSettlementCalculation;
use YddOrder\Model\Order\Order;
use YddOrder\Model\Order\OrderSettlementLog;
use YddOrder\OrderField\OrderFieldConstant;
use YddOrder\Repository\Blance\StoreBlanceRepository;

class OrderSettlementRepository
{
    //订单数据
    private $order;
    //用户id
    private $user_id = 0;
    //用户类型（1经销商用户2门店用户）
    private $user_type = 0;
    //需要记录的结算日志
    private $logData = [];
    //操作的金额数据
    private $moneyData = [];

    public function __invoke(Order $order, int $user_id, int $user_type): OrderSettlementRepository
    {
        $this->order = $order;
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        return $this;
    }

    /**
     * 开始执行结算
     * @return OrderSettlementRepository
     * @throws \Exception
     */
    public function handle(): OrderSettlementRepository
    {
        //进行结算计算
        $this->moneyData = OrderSettlementCalculation::orderSettlement($this->order, $this->user_id, $this->user_type);
        return $this;
    }

    /**
     * 更改订单结算状态
     * @return OrderSettlementRepository
     */
    public function upSettlementStatus(): OrderSettlementRepository
    {
        $info = (new Order)->lock(true)->find($this->order_id);

        //修改订单状态
        $info->settled_time = time();
        $info->is_settled = OrderFieldConstant::SETTLED_SUCCESS;
        $info->order_status = OrderFieldConstant::RECEIPT;

        if (!$info->allowField(['settled_time', 'is_settled', 'order_status'])->save()) {
            throw new Exception('结算失败，订单数据更新失败！');
        }

        return $this;
    }

    /**
     * 订单结算增加记录
     * @return bool
     */
    public function settlementLog(): bool
    {
        //增加结算记录
        $ad = OrderSettlementLog::create([
            'order_id'     => $this->logData['order_id'],
            'order_no'     => $this->logData['order_no'],
            'price'        => $this->logData['price'],
            'change_price' => $this->logData['change_price'],
            'mark'         => $this->logData['mark']
        ]);
        return $ad === false ? false : true;
    }

    /**
     * 获取当前经销商/门店对应的所属结算比例
     * @param int $user_id
     * @param int $user_type
     * @return float
     */
    public static function getDealerConversionRatio(int $user_id, int $user_type): float
    {
        return 0.006;
    }

    /**
     * 增加用户金额
     * @return $this
     */
    public function addUserMoney(): OrderSettlementRepository
    {
        $nowMoney = 0;
        //应打款的金额
        $user_money = sprintf("%.2f", bcadd(sprintf("%.2f", $nowMoney), sprintf("%.2f", $this->moneyData['price']), 2));
        (new StoreBlanceRepository())->addBlance($this->user_id, $this->user_type, sprintf('%.2f', $user_money), $this->moneyData['mark']);
        return $this;
    }
}