<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    public function dataFromCache()
    {
        // REDIS_HOST=common-redis
        // REDIS_PASSWORD=redispassword
        // REDIS_PORT=6379
        // REDIS_PREFIX=

        $data = [];
        $status = 500;

        try {
            $keys = Redis::keys('*');
        
            foreach ($keys as $key) {
                $data[$key] = Redis::get($key);
            }

            $status = 200;
        } catch (\Exception $e) {
            $data['msg'] = 'Error connecting to Redis: ' . $e->getMessage();
        }
        
        return response()->json([
            'status' => $status,
            'data' => $data
        ]);
    }
}
