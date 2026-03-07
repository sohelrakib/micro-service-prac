<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sohelrakib\SafeExecutor\SafeExecutor;

class CustomerController extends Controller
{
    public function index()
    {
        // $result = SafeExecutor::run(function () {
        //     return 10 / 0;
        // }, 'division', 'error', 'date');

        // $result = SafeExecutor::run(function () {
        //     return 10 / 0;
        // }, 'division', 'error', 'division-file-log');

        // $result = SafeExecutor::run(function () {
        //     return 10 / 0;
        // }, 'division', 'error');

        $result = SafeExecutor::run(function () {
            return 10 / 2;
        }, 'division', 'error', 'date');

        if ($result instanceof \Throwable) {
            return "Error: " . $result->getMessage();
        }

        return "Success: " . $result;
    }
}
