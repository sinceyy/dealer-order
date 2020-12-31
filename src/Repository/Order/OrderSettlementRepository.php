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
    //门店id
    private $store_id = 0;
    //品牌商id
    private $brand_id = 0;
    //需要记录的结算日志
    private $logData = [];
    //操作的金额数据
    private $moneyData = [];

    public function __invoke(Order $order, int $user_id, int $store_id): OrderSettlementRepository
    {
        $this->order    = $order;
        $this->user_id  = $user_id;
        $this->store_id = $store_id;
        $this->brand_id = $order->brand_id;
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
        $this->moneyData = OrderSettlementCalculation::orderSettlement($this->order);
        return $this;
    }

    /**
     * 更改订单结算状态
     * @return OrderSettlementRepository
     */
    public function upSettlementStatus(): OrderSettlementRepository
    {
        //修改订单状态
        $up = Order::where(['id' => $this->order->id])->update([
            'settled_time' => time(),
            'is_settled'   => OrderFieldConstant::SETTLED_SUCCESS,
            'order_status' => OrderFieldConstant::RECEIPT
        ]);

        if (!$up) {
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
        $order = OrderSettlementLog::where(['order_id' => $this->moneyData['order_id'], 'order_no' => $this->moneyData['order_no']])->find();

        if (!$order) {
            //增加结算记录
            $ad = (new OrderSettlementLog)->insert([
                'order_id'     => $this->moneyData['order_id'],
                'order_no'     => $this->moneyData['order_no'],
                'price'        => $this->moneyData['price'],
                'change_price' => $this->moneyData['change_price'],
                'mark'         => $this->moneyData['mark']
            ]);

            return $ad === false ? false : true;
        }

        return true;
    }

    /**
     * 获取当前品牌商对应的所属结算比例
     * @param int $brand_id
     * @return float
     */
    public static function getDealerConversionRatio(int $brand_id): float
    {
        return 0.006;
    }

    /**
     * 增加用户金额
     * @return $this
     * @throws \think\Exception
     */
    public function addUserMoney(): OrderSettlementRepository
    {
        $nowMoney = 0;
        //应打款的金额
        $user_money   = sprintf("%.2f", bcadd(sprintf("%.2f", $nowMoney), sprintf("%.2f", $this->moneyData['price']), 2));
        $addBlanceLog = StoreBlanceRepository::addBlance($this->user_id, $this->store_id, $this->brand_id, sprintf('%.2f', $user_money), $this->moneyData['mark']);
        if (!$addBlanceLog) throw new Exception('结算失败，增加用户金额失败！');
        return $this;
    }
}