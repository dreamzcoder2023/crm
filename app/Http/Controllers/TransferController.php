<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;


class TransferController extends Controller
{
    public function index(Request $request){
      $auth = Auth::user()->id;
      $user_filter = $request->user_id;
      $role = DB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->join('users','users.id','=','model_has_roles.model_id')->where('users.id',$auth)->pluck('roles.id')->first();

      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      if($role == 1){
        $transfers = Transfer::leftjoin('users','users.id','=','transferdetails.member_id')->leftjoin('users as user_table','user_table.id','=','transferdetails.user_id')->leftjoin('payment','payment.id','=','transferdetails.payment_mode')->select('transferdetails.*','users.first_name','users.last_name','user_table.first_name as firstname','user_table.last_name as lastname','payment.name as payment_name');

        if($from != '' && $to_date != ''){
          $transfers = $transfers->whereBetween('current_date', [$from,$to_date]);

        }
        if($user_filter != 'undefined' && $user_filter != ''){
          $transfers = $transfers->where('transferdetails.user_id',$user_filter);
        }
        $transfers = $transfers->orderBy('transferdetails.id','desc')->get();
      }
      else{
        $transfers = Transfer::leftjoin('users','users.id','=','transferdetails.member_id')->leftjoin('users as to','to.id','=','transferdetails.user_id')->where('users.id',$auth)->orWhere('to.id',$auth)->select('transferdetails.*','to.id as from_id','users.first_name','users.last_name','to.first_name as firstname','to.last_name as lastname');
        if($from != '' && $to_date != ''){
          $transfers = $transfers->whereBetween('current_date', [$from,$to_date]);

        }
        $transfers = $transfers->orderBy('transferdetails.id','desc')->get();
      }
      $user = User::join('model_has_roles','model_has_roles.model_id','=','users.id')->join('roles','roles.id','=','model_has_roles.role_id')->where(['users.active_status' => 1 ,'users.delete_status' => 0])->select('users.*','roles.name')->get();
      $sum = $transfers->sum('amount');
        return view('transfer.index',['transfers' => $transfers,'role' => $role,'user' =>$user,'user_filter' => $user_filter,'from_date' => $request->from_date ,'to_date1' => $request->to_date,'sum' => $sum]);
    }

    public function create(Request $request){
        $id = Auth::user()->id;
         $member = User::where(['active_status' => 1,'delete_status' => 0])->where('id','!=',$id)->select('*')->get();
         $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
         return view('transfer.create',['member' => $member,'payment' => $payment]);
        //return response()->json($view);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
      $input = $request->all();
      $input['user_id'] = $user_id;
      $input['current_date'] = $request->current_date.' '.$request->time;
        $transfer = Transfer::create($input);
        $project = User::find($user_id);
        $minus = $project->wallet - $request->amount;
        $project['wallet'] = $minus;
        $project->update();
        $user = User::find($request->member_id);
        $add = $user->wallet + $request->amount;
        $user['wallet'] = $add;
        $user->update();
        return redirect()->route('transfer-history')
        ->with('transfer-popup', 'open');

    }
    public function insufficientamt(Request $request){

       $wallet = Auth::user()->wallet;
      // $wallet = User::where('id', $request->user_id)->first();
       $amount = $request->amount;
       $wal_amt = (int)$wallet;
       $response = true;
       if (($wal_amt >= 0) && ($amount <= $wallet)) {
         $response = false;
       }
       return response()->json($response);
      }
}

