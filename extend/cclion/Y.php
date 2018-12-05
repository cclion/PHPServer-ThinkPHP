<?php

namespace cclion;

use think\Response;

class Y
{

    //MARK: 封装接口返回格式
    public static function json($code = 0, $message = '成功', $data = []){

        $json = ['code' => $code, 'message' => $message, 'data' =>$data];
        return Response::create($json, 'json', 200);
    }

}