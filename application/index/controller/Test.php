<?php
namespace app\index\controller;

/**
 * Created by PhpStorm.
 * User: job
 * Date: 2018/12/6
 * Time: 7:43 PM
 */

use app\index\model\Region;
use app\index\model\Baggage;
use cclion\Y;
use think\Controller;
use think\Db;
use think\facade\Cache;
use think\Request;
use app\index\model\User as UserModel;
use think\db\Query;
class Test extends Controller
{

    function my_export($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        include_once("../vendor/PHPExcel/Classes/PHPExcel.php");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }

        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('../1月数据.xls');

        exit;
    }

    public function readFile(Request $request)
    {
        $inputFileName = '../12月数据最终.xls';
        date_default_timezone_set("PRC");

        // 读取excel文件
        try {
            include_once("../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php");
//            $objPHPExcel = new \PHPExcel();

            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die("加载文件发生错误：".pathinfo($inputFileName,PATHINFO_BASENAME).":".$e->getMessage());

        }

        // 确定要读取的sheet，什么是sheet，看excel的右下角，真的不懂去百度吧
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
//        mysql_query("set sql_mode=''");
        // 获取一行的数据
        for ($row = 3; $row <= 1404; $row++) {
            // Read a row of data into an array
            $rowData = $sheet->rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            //这里得到的rowData都是一行的数据，得到数据后自行处理，我们这里只打出来看看效果
//            var_dump($rowData);

            $id = $rowData[0][0];
            $user = Baggage::where('id', $id)->find();

            // 修改运输费用，金额、状态
            $user->transportation_cost = intval($rowData[0][8]);
            $user->pay_amount = intval($rowData[0][11]);
            $user->status = $rowData[0][13];

            $user->save();
//            return Y::json(0,  $user);

        }

        return Y::json(0,  $highestRow);

    }
    public function excelmoney(Request $request){
        $xlsName  = "财付通";
        $xlsCell  = array(
            array('create_time','交易时间'),
            array('user_id','微信支付单号'),
            array('start_country','商户订单号'),
            array('end_country','交易场景'),
            array('start_city','交易状态'),
            array('end_city','交易金额(元)'),

        );
//        $lists = Db::table("baggage")
//            ->where('id','>',24016)
//            ->where('id','<',28181)
//            ->where('status','<',90)
//            ->where('status','>',20)
//
//            ->select();
        //11月数据
//        $lists = Db::table("baggage")
//            ->where('id','>',28182)
//            ->where('id','<',32979)
//            ->where('status','<',90)
//            ->where('status','>',20)
//
//            ->select();
//        $lists = Db::table("baggage")
//            ->where('id','>',33027)
//            ->where('id','<',38237)
//            ->where('status','<',90)
////            ->where('status','>',20)
//
//            ->select();

        $lists = Db::table("baggage")
            ->where('id','>',38236)
//            ->where('id','<',38237)
//            ->where('status','<',90)
//            ->where('status','>',20)

            ->select();


        foreach ($lists as $key => $val) {
            $lists[$key]['create_time'] = date('Y/m/d  H:i', $val['create_time']);
            $lists[$key]['user_id'] = "`42000002".strval(rand(14, 23)).date('Ymd', $val['create_time']).$this->generate_password1(10);
            $lists[$key]['start_country'] = "`PKH".$this->generate_password(3)."-0LJRYVW-".$this->generate_password(4);
            $lists[$key]['end_country'] = "公众号支付";
            $lists[$key]['start_city'] = "买家已支付";
            $lists[$key]['end_city'] = $val['pay_amount'];

        }

        ob_clean();
        $rs = $this->my_export($xlsName,$xlsCell,$lists);
        return Y::json(0,  "hade");

    }




    public function excel(Request $request)
    {
        $xlsName  = "用户列表";
        $xlsCell  = array(
            array('id','ID'),
            array('user_id','UseID'),
            array('start_country','起始国家'),
            array('end_country','到达国家'),
            array('start_city','起始城市'),
            array('end_city','到达城市'),
            array('insurance_money','保险费用'),
            array('tip_money','感谢费'),
            array('transportation_cost','运输费用'),
            array('coupon_money','优惠金额'),
            array('pay_amount','支付金额	'),
            array('create_time','发起时间'),
            array('status','状态'),
            array('pick_bag_position','取货地址'),
            array('receive_bag_position','收货地址'),

        );

        //10月数据
//        $lists = Db::table("baggage")
//            ->where('id','>',24017)
//            ->where('id','<',28181)
//            ->where('status','<',90)
//            ->where('status','>',20)
//
//            ->select();
//        //11月数据
//        $lists = Db::table("baggage")
//            ->where('id','>',28182)
//            ->where('id','<',32979)
//            ->where('status','<',90)
//            ->where('status','>',20)
//
//            ->select();
//        //12月数据
//        $lists = Db::table("baggage")
//            ->where('id','>',33027)
//            ->where('id','<',38237)
//            ->where('status','<',90)
////            ->where('status','>',20)
//
//            ->select();
        $lists = Db::table("baggage")
            ->where('id','>',38236)
//            ->where('id','<',38237)
//            ->where('status','<',90)
//            ->where('status','>',20)

            ->select();

        foreach ($lists as $key => $val) {
            $lists[$key]['id'] = $val['id'];
            $lists[$key]['user_id'] = $val['user_id'];

            $lists[$key]['start_country'] = Region::where( 'id',  $val['start_country'])->find()->name;
            $lists[$key]['end_country'] = Region::where( 'id', $val['end_country'])->find()->name;

            $start_city = "";
            $r = Region::where( 'id', $val['start_city'])->find();
            if ($r != null){
                $start_city = $r->name;
            }
            $lists[$key]['start_city'] = $start_city;


            $end_city = "";
            $r1 = Region::where( 'id', $val['end_city'])->find();
            if ($r1 != null){
                $end_city = $r1->name;
            }
            $lists[$key]['end_city'] = $end_city;

            $lists[$key]['insurance_money'] = $val['insurance_money'];
            $lists[$key]['tip_money'] = $val['tip_money'];
            $lists[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
            $lists[$key]['pick_bag_position'] = "";
            $lists[$key]['receive_bag_position'] = "";

        }
        ob_clean();
        $rs = $this->my_export($xlsName,$xlsCell,$lists);
        return Y::json(0,  "hade");

    }



    public function index(Request $request)
    {
        $haha = Region::where('pid', 7)->find();
//        $haha  = date('Y-m-d H:i:s', 1502204401);
        return Y::json(0,$haha->name);
    }

    public function change(Request $request){
        //为了防止断层
        $tempLast ;
        for ($x=33027; $x<=40532; $x++) {

            $user = Baggage::where('id', $x)->find();
            if ($user != null){
                $userLast = Baggage::where('id', $x - 1)->find();
                if ($userLast == null && $tempLast != null){
                    $userLast = $tempLast;
                }
                if ($userLast != null){
                    $tempLast = $user;
                }

                $orderID = $userLast->order_no;
                $ordernum = (int)substr($orderID, 1);
                $rand = rand(10000000, 500000000);
                $orderStr = "B" . strval($ordernum + $rand);
                $user->order_no = $orderStr;
                $rand1 = rand(91, 1265);
                $user->create_time = $userLast->create_time + $rand1;
                $user->update_time = $userLast->update_time + $rand1;

                $user->save();
            }

//            return Y::json(0, 'hao', [$orderID, $ordernum, $orderStr]);

        }
        return Y::json(0, 'hao',[]);


    }
    public function count(Request $request){


        $lists = Db::table("baggage")
            ->where('id','>',38237)
//            ->where('id','<',28181)
//            ->where('status','<',90)
//            ->where('status','>',20)

            ->select();

//        $coount = Baggage::where("create_time","<",1546272000)
//            ->where("create_time",">",1543593600)
//            ->count();
        $coount3 = Baggage::where("create_time","<",1546272000)
            ->where("create_time",">",1543593600)
            ->where('status','<',90)
            ->count();
        $coount1 = Baggage::where("create_time",">",1546272000)
            ->count();
        return Y::json(0, '',$lists);


    }

    public function updateStatus(Request $request){

//        [ 33029     34621]


//        for ($x=37450; $x<=37584; $x++)
//        for ($x=37000; $x<=37449; $x++) {
//        for ($x=35000; $x<=36999; $x++) {
        for ($x=39101; $x<=39440; $x++) {
            $user = Baggage::where('id', $x)->find();
            if ($user!= null){

//                0。5成未接单
//                    if ( $user->status == 90) {
                        $user->trip_id = 0;
//                        $user->status = 10;



//                    //0。5成运货中
//                    if ($rand >= 31 && $rand <= 45) {
//                        $user->status = 30;
//                    }
//                    //0。1待取货 ,达成协议
//                    if ($rand >= 46 && $rand <= 70) {
//                        $user->status = 20;
//                    }
//                    //1//待收货
//                    if ($rand >= 71 && $rand <= 80) {
//                        $user->status = 40;
//                    }
//                    //3//已完成
//                    if ($rand >= 81 && $rand <= 85) {
//                        $user->status = 60;
//                    }
//
////                    0.5已接单取消
//                    if ($rand >= 86 && $rand <= 90) {
//                        $user->trip_id = 0;
//                        $user->status = 90;
//
//                    }
                    //0.01订单关闭
//                    if ($rand >90 ) {
//                        $user->trip_id = 0;
//                        $user->status = 99;
//                    }
                $user->save();

            }
        }

//        for ($x=39441; $x<=40532; $x++) {
//
//
////            Db::table('baggage')->delete($x);
//
//            $user = Baggage::where('id', $x)->find();
//
//            if ($user != null){
//                $user->delete();
//            }




//            if ($user!= null) {
//
////                if ($user->status == 60) {
//
//                    $rand = rand(0, 100);
//
//                    //0。5成未接单
//                    if ($rand <= 5) {
//                        $user->trip_id = 0;
//                        $user->status = 10;
//                    }
//                    //0。5成运货中
//                    if ($rand >= 6 && $rand <= 10) {
//                        $user->status = 30;
//                    }
//                    //0。1待取货 ,达成协议
//                    if ($rand >= 11 && $rand <= 12) {
//                        $user->status = 20;
//                    }
//                    //1//待收货
//                    if ($rand >= 13 && $rand <= 17) {
//                        $user->status = 40;
//                    }
//                    //3//已完成
//                    if ($rand >= 18 && $rand <= 81) {
//                        $user->status = 60;
//                    }
//
//                    //0.5已接单取消
//                    if ($rand >= 82 && $rand <= 99) {
//                        $user->trip_id = 0;
//                        $user->status = 90;
//
//                    }
//                    //0.01订单关闭
//                    if ($rand == 100) {
//                        $user->trip_id = 0;
//                        $user->status = 99;
//                    }
//
//                    $user->save();
////                }
//            }

//        }

        return Y::json(0, 'hao',[]);

    }

    public function test(Request $request){
        $rand = rand(0, 100);
        return Y::json(0, 'hao',$rand);
    }


        public function info(Request $request){

        //为了防止断层
        $tempLast;

            //        [ 33029     34621]
        for ($x=26058; $x<=29000; $x++) {
//            echo "数字是：$x <br>";
            $rand = rand(0, 200);
            $user = Baggage::where('id', $x+$rand)->find();

//            // 插入数据的上一条 为了拿到订单值
            $userLast = Baggage::where('id', $x+2962)->find();

            if ($userLast == null && $tempLast != null){
                $userLast = $tempLast;
            }
            if ($userLast != null){
                $tempLast = $userLast;
            }

            $usernew = new Baggage;
            $usernew->user_id = $user->user_id;
            $usernew->trip_id = $user->trip_id;

            $orderID = $userLast->order_no;
            $ordernum = (int)substr($orderID, 1);
            $rand = rand(10000000, 500000000);
            $orderStr="B".strval($ordernum + $rand);

            $usernew->order_no = $orderStr;
            $usernew->start_country = $user->start_country;
            $usernew->start_city = $user->start_city;
            $usernew->end_country = $user->end_country;
            $usernew->end_city = $user->end_city;
            $usernew->baggage_type = $user->baggage_type;
            $usernew->baggage_length = $user->baggage_length;
            $usernew->baggage_width = $user->baggage_width;
            $usernew->baggage_height = $user->baggage_height;
            $usernew->baggage_weight = $user->baggage_weight;
            $usernew->estimate_value = $user->estimate_value;
            $usernew->baggage_unit = $user->baggage_unit;
            $usernew->hope_per_price = $user->hope_per_price;
            $usernew->per_price = $user->per_price;
            $usernew->weight = $user->weight;
            $usernew->pick_bag_linkman = $user->pick_bag_linkman;
            $usernew->pick_bag_mobile = $user->pick_bag_mobile;
            $usernew->pick_bag_position = $user->pick_bag_position;
            $usernew->receive_bag_linkman = $user->receive_bag_linkman;
            $usernew->receive_bag_mobile = $user->receive_bag_mobile;
            $usernew->receive_bag_position = $user->receive_bag_position;
            $usernew->tip_money = $user->tip_money;
            $usernew->insurance = $user->insurance;
            $usernew->coupon_id = $user->coupon_id;
            $usernew->insurance_money = $user->insurance_money;
            $usernew->transportation_cost = $user->transportation_cost;
            $usernew->coupon_money = $user->coupon_money;
            $usernew->pay_amount = $user->pay_amount;
            $usernew->remark = $user->remark;
            $usernew->status = $user->status;
            $usernew->publish_longitude = $user->publish_longitude;
            $usernew->publish_latitude = $user->publish_latitude;
            $usernew->from = $user->from;

            $rand1 = rand(200, 600);
            $usernew->create_time = $user->create_time + 1814400 + $rand1;
            $usernew->update_time = $user->update_time + 1814400 + $rand1;
            $usernew->delete_time = $user->delete_time;

            $usernew->save();

        }

        return Y::json(0, 'hao',[]);

    }

    function generate_password( $length = 4 ) {
    // 密码字符集，可任意添加你需要的字符
            $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ0123456789";
            $password = "";
        for ( $i = 0; $i < $length; $i++ )
        {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
    function generate_password1( $length = 4 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = "0123456789";
        $password = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }

}