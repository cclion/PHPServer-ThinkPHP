<?php
/**
 * Created by PhpStorm.
 * User: job
 * Date: 2019-01-04
 * Time: 12:16
 */

namespace app\admin\controller;
use cclion\Y;
use think\Controller;
use think\Request;

class Menu extends Controller
{

    public function getMenuList(Request $request){

        $data = array(
            array(
                "title"=>"haha",
                "icon"=>"layui-icon-home",
                "list"=>array(
                    array(
                        "title"=>"控制台",
                        "jump"=>"/"
                    ),
                    array(
                        "name"=>"homepage1",
                        "title"=>"主页一",
                        "jump"=>"home/homepage1"
                    )
                )
            ),
            array(
                "name"=>"user",
                "title"=>"用户",
                "icon"=>"layui-icon-user",
                "list"=>array(
                    array(
                        "name"=>"user",
                        "title"=>"网站用户",
                        "jump"=>"user/user/list"
                    ),
                    array(
                        "name"=>"administrators-list",
                        "title"=>"后台管理员",
                        "jump"=>"user/administrators/list"
                    )
                )
            )
        );

        return Y::json(0, '',$data);

        return $data;
    }


}


