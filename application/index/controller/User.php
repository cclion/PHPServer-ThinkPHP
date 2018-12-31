<?php
namespace app\index\controller;

/**
 * Created by PhpStorm.
 * User: job
 * Date: 2018/12/6
 * Time: 7:43 PM
 */

use cclion\Y;
use think\Controller;
use think\facade\Cache;
use think\Request;
use app\index\model\User as UserModel;
use think\db\Query;
class User extends Controller {

    public function index()
    {
        return Y::json(0, 'user成功');
    }

    public function info(Request $request){

        $token = $request->header("Authorization");

        if (!$token){
            return Y::json(101, '请携带token');
        }

        $userID = Cache::get($token);

        if (!$userID){
            return Y::json(101, 'token未找到');
        }

        $user = UserModel::where('id', $userID)->find();

        return Y::json(0, '',$user);
        
    }

    public function login(Request $request){

        $phone = $request->post("phone");
        $password = $request->post("password");
        if (!$phone){
            return Y::json(101, '请输入手机号');
        }
        if (!$password){
            return Y::json(101, '请输入密码');
        }
        if (!is_phone($phone)){
            return Y::json(101, '手机号格式错误');
        }
        $user = UserModel::where('phone', $phone)->find();
        if (!$user){
            return Y::json(101, '手机号未注册');
        }
        if ($user->password != $password){
            return Y::json(101, '密码错误');
        }
        $token = $request->token("token","sha12");

        $userID = $user->id;

        Cache::set($token,$userID,0);

        return Y::json(0, '登录成功',$token);

    }

    public  function regist(Request $request){

        $phone = $request->post("phone");
        $password = $request->post("password");
        if (!$phone){
            return Y::json(101, '请输入手机号');
        }
        if (!$password){
            return Y::json(101, '请输入密码');
        }
        if (!is_phone($phone)){
            return Y::json(101, '手机号格式错误');
        }
        $user = UserModel::where('phone', $phone)->find();
        if ($user){
            return Y::json(101, '手机号已注册');
        }

        $user           = new UserModel;
        $user->phone     = $phone;
        $user->password    = $password;
        $user->save();

        return Y::json(0, '注册成功');
    }


}