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
            $url = Url::build('paycode', [
                'a' => $account,
                'r' => $randomCodeSafe,
            ], 'html', true);

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
        $user = Db::name('hair_user')->where('md5(account)', $account)->find();
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
