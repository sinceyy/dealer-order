<?php
declare(strict_types=1);

namespace YddOrder\Service\Order;

use YddOrder\OrderField\OrderFieldConstant;
use YddOrder\Repository\Dealer\DealerRepository;
use YddOrder\Repository\Order\OrderRepository;
use YddOrder\Repository\Store\StoreRepository;
use YddOrder\Repository\Order\OrderWriteRespository;
use YddOrder\Repository\Order\OrderSettlementRepository;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Db;
use YddOrder\Service\ServiceAbstruct;

class OrderWriteService extends ServiceAbstruct
{

    /**
     * 订单核销（核销码）
     * @param string $code
     * @param int    $user_id   （经销商用户id/门店用户id）
     * @param int    $user_type (1经销商用户2门店用户)
     * @return bool
     * @throws \Exception
     */
    public function writeOff(string $code, int $user_id, int $user_type): array
    {
        //获取订单信息
        $order = OrderRepository::getInfoByWhere(
            [
                'write_code'   => str_replace(' ', '', $code),
                'order_status' => OrderFieldConstant::SUCCESS,
                'pay_status'   => OrderFieldConstant::PAY_SUCCESS
            ]);

        //check 检查
        if (!$order) return self::returnErrorData('暂未查询到此待核销订单');
        //检查是否自己订单
        $error = $this->checkOrderIsMe($order, $user_id, $user_type);
        if (isset($error['error'])) return self::returnErrorData($error['error']);

        try {
            //开启事物
            Db::startTrans();
            //记录核销记录
            $write = OrderWriteRespository::setWriteLog($order, $user_id, $user_type);
            //更改订单核销状态
            $upOrderStatus = OrderWriteRespository::upOrderWriteStatus($order->id);
            //进行结算
            $settlement = new OrderSettlementRepository;
            //操作成功则开始结算
            $settlementOrder = $settlement($order, $user_id, $user_type)->handle()->upSettlementStatus()->addUserMoney()->settlementLog();
            //结算成功，提交事物
            if ($write && $upOrderStatus && $settlementOrder) Db::commit();

            throw new \Exception('核销失败!');
        } catch (DbException $dbe) {
            Db::rollback();
            return self::returnErrorData($dbe->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            return self::returnErrorData($e->getMessage());
        }
        return self::returnData([]);
    }

    /**
     * 检查是否所属自己或下级订单
     * @param     $order
     * @param int $user_id
     * @param int $user_type
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function checkOrderIsMe($order, int $user_id, int $user_type)
    {
        //经销商处理订单
        if ($user_type == 1) {
            //订单所属经销商门店
            if ($order->source_type == 1) {
                //检查订单是否是该经销商门店订单，或是该经销商旗下门店订单
                $info = DealerRepository::getInfoById($order->source_id);
                if (!$info) return ['error' => '暂未查询到该待核销订单所属门店信息'];
                if ($info->parent_dealer_id != $user_id || $order->source_id != $user_id) {
                    return ['error' => '暂未查询到该待核销订单所属经销商信息'];
                }
            } else if ($order->source_type == 2) { //订单所属经销商下门店
                $storeInfo = StoreRepository::getInfoById($order->source_id);
                if ($storeInfo->pid != $user_id) {
                    return ['error' => '暂未查询到该待核销订单所属经销商信息'];
                }
            } else {
                return ['error' => '订单信息错误，无法查询到此单，请确定是否属于当前账户下'];
            }
        } else if ($user_type == 2) {
            //订单所属经销商门店
            if ($order->source_type == 1) return ['error' => '暂未查询到该待核销订单所属门店信息'];
            if ($order->source_type == 2 && $order->source_id != $user_id) return ['error' => '暂未查询到该待核销订单所属门店信息'];
        }

        return true;
    }
}