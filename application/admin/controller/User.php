<?php
/**
 * Created by PhpStorm.
 * User: job
 * Date: 2019-01-08
 * Time: 16:52
 */
namespace app\admin\controller;

use think\Controller;
use cclion\Y;
use think\Db;
use think\Request;

class User extends Controller
{
    public function getUserList(Request $request){

        $page = $request->get("page");
        $limit = $request->get("limit");

        $data = Db::table("User")
            ->limit($limit)
            ->page($page)
            ->select();
        $count = Db::table("User")
            ->count();

        return Y::jsonList(0, '',$count,$data);
    }
}