<?php
/**
 * Created by PhpStorm.
 * User: YONGMAN LEE
 * Date: 2020-07-17
 * Time: 오후 11:13
 */

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Resources;

class EventController extends Controller
{
    public function index(){
        //dd(Carbon::now());
        /*echo "현재 날짜 : ". date("Y-m-d")."<br/>";
        echo "현재 시간 : ". date("H:i:s")."<br/>";
        echo "현재 일시 : ". date("Y-m-d H:i:s")."<br/>";
        echo date_default_timezone_get();
        exit;*/


        
        
		$tableData = Resources::where(['category'=>'event'])->orderBy('id','desc')->paginate(1);
        return view('events.index',['tableData'=>$tableData]);
    }
}