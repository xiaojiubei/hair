<?php

namespace app\admin\model\user;

use addons\epay\library\Service;
use app\common\model\User;
use think\Exception;
use think\Model;
use Yansongda\Pay\Pay;

class Withdraw extends Model
{


    // 表名
    protected $name = 'withdraw';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'settledmoney'
    ];

    public static function init()
    {
        self::afterInsert(function ($row) {
            $changedData = $row->getChangedData();
            if (isset($changedData['status']) && $changedData['status'] == 'agreed') {
                User::money(-$row['money'], $row['user_id'], '提现');
            }
        });
        self::beforeWrite(function ($row) {
            if (!isset($row['orderid']) || !$row['orderid']) {
                $row['orderid'] = date("YmdHis") . sprintf("%08d", $row['user_id']) . mt_rand(1000, 9999);
            }
        });
        self::afterWrite(function ($row) {
            $changedData = $row->getChangedData();
            if (isset($changedData['status'])) {
                if ($changedData['status'] == 'successed') {
                    $info = get_addon_info('epay');
                    if (!$info || !$info['state']) {
                        throw new Exception("请在插件市场安装微信支付宝整合插件并启用");
                    }
                    if ($row['settledmoney'] < 0.1) {
                        throw new Exception("最终提现金额最少0.1元");
                    }
                    if (isset($row['transactionid']) && $row['transactionid']) {
                        throw new Exception("无法进行重复提现");
                    }
                    try {
                        $order = [
                            'out_biz_no'   => $row['orderid'],
                            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                            'biz_scene'    => 'DIRECT_TRANSFER',
                            'payee_info'   => [
                                'identity'      => $row['account'],
                                'identity_type' => 'ALIPAY_LOGON_ID',
                                'name'          => $row['name'],
                            ],
                            'trans_amount' => $row['settledmoney'],
                        ];
                        $config = Service::getConfig('alipay');
                        $pay = Pay::alipay($config);
                        $result = $pay->transfer($order);
                        if ($result && isset($result['code']) && $result['code'] == 10000) {
                            \think\Db::name("withdraw")->where('id', $row['id'])->update(['transactionid' => $result['order_id'], 'transfertime' => strtotime($result['pay_date'])]);
                        } else {
                            exception('转账失败！');
                        }
                    } catch (\Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                } elseif ($changedData['status'] == 'rejected') {
                    User::money($row['money'], $row['user_id'], '提现退回');
                }
            }
        });
        self::beforeDelete(function ($row) {
            if ($row['transactionid']) {
                throw new Exception("该记录无法删除");
            }
        });
    }

    public function getSettledmoneyAttr($value, $data)
    {
        return max(0, sprintf("%.2f", $data['money'] - $data['handingfee'] - $data['taxes']));
    }

    public function getStatusList()
    {
        return ['created' => __('Status created'), 'successed' => __('Status successed'), 'rejected' => __('Status rejected')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function user()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "id")->setEagerlyType(0);
    }
}
