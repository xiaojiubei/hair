<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Url;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class Hair extends Controller
{
    // 划卡
    public function dopay()
    {
        $params = $this->request->param();
        if (!empty($params['a']) && !empty($params['r'])) {
            if ($this->request->isPost()) {
                // $this->success('刷卡成功');
                // halt($params);
                if (!empty($params['s'])) {
                    $shoper = Db::name('hair_shoper')->where('safe_code', $params['s'])->find();
                    if ($shoper) {
                        $user = Db::name('hair_user')->where('md5(account)', $params['a'])->find();
                        if ($user) {
                            $safeCode = Db::name('hair_code')->where('md5(random_code)', $params['r'])->find();
                            if ($safeCode) {
                                $log = Db::name('hair_log')->where('md5(random_code)', $params['r'])
                                    ->where('user_id', $user['id'])
                                    ->find();
                                if (!$log) {
                                    Db::transaction(function () use ($user, $shoper, $safeCode) {
                                        Db::name('hair_user')->where('id', $user['id'])->setDec('balance');
                                        Db::name('hair_log')->insert([
                                            'shoper_id'   => $shoper['id'],
                                            'user_id'     => $user['id'],
                                            'random_code' => $safeCode['random_code'],
                                        ]);
                                    });
                                }
                                $msg = '请刷新付款码重试';
                            }
                            $msg = '非法付款码';
                        }
                        $msg = '无此会员';
                        return json([
                            'code' => 1,
                            'msg'  => '刷卡成功',
                        ]);
                    }
                    $msg = '无此商家';
                }
                return json([
                    'code' => 0,
                    'msg'  => $msg ?? 'failed',
                ]);
            }
            return $this->fetch();
        }
        return json([
            'code' => 0,
            'msg'  => 'failed',
        ]);
    }

    // 商家端
    public function shoper()
    {
        //
        $safeCode = $this->request->param('c');
        $shoper = Db::name('hair_shoper')->where('md5(safe_code)', $safeCode)
            // ->fetchSql(true)
            ->find();
        $shoper = Db::query('select * from fa_hair_shoper where md5(safe_code) = "' . $safeCode . '" limit 1');
        if ($shoper) {
            $shoper = $shoper[0];
        }
        // halt($shoper);
        if ($shoper) {
            $log = Db::name('hair_log')->alias('l')
                ->join('__HAIR_USER__ u', 'l.user_id = u.id', 'LEFT')
                ->field('l.*,u.name')
                ->where('l.shoper_id', $shoper['id'])
                ->select();
        }
        return $this->fetch('shoper', [
            'shoper' => $shoper,
            'log'    => $log ?? [],
        ]);
    }

    // 付款码
    public function paycode()
    {
        $account = $this->request->param('a');
        $user = Db::name('hair_user')->where('md5(account)', $account)->find();
        if ($user) {
            $randomCode = Db::name('hair_code')->alias('c')
                ->whereNotExists(function ($query) use ($user) {
                    $query->name('hair_log')->alias('l')
                        ->where('l.random_code', Db::raw('c.random_code'))
                        ->where('l.user_id', $user['id']);
                })
                ->value('random_code');
            $randomCodeSafe = md5($randomCode);
            $url = Url::build('dopay', [
                'a' => $account,
                'r' => $randomCodeSafe,
            ], 'html', true);
            // halt($url);

            $result = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(300)
                ->margin(10)
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                // ->logoPath(__DIR__.'/assets/symfony.png')
                // ->labelText('This is the label')
                // ->labelFont(new NotoSans(20))
                // ->labelAlignment(new LabelAlignmentCenter())
                ->validateResult(false)
                ->build();

            // Directly output the QR code
            // header('Content-Type: ' . $result->getMimeType());
            // echo $result->getString();

            // Save it to a file
            // $result->saveToFile(__DIR__ . '/qrcode.png');

            // Generate a data URI to include image data inline (i.e. inside an <img> tag)
            $dataUri = $result->getDataUri();
            // halt($dataUri);

            return $this->fetch('paycode', [
                'user' => $user,
                'src'  => $dataUri,
            ]);
        }
        return json([
            'code' => 0,
            'msg'  => 'failed',
        ]);
    }

    // 划卡

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $account = $this->request->param('a');
        $user = Db::name('hair_user')->where('md5(account)', $account)
            // ->fetchSql(true)
            ->find();
        // halt($user);
        if ($user) {
            $log = Db::name('hair_log')->alias('l')
                ->join('__HAIR_SHOPER__ s', 'l.shoper_id = s.id', 'LEFT')
                ->field('l.*,s.name')
                ->where('l.user_id', $user['id'])
                ->select();
        }
        return $this->fetch('index', [
            'user' => $user,
            'log'  => $log ?? [],
        ]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
