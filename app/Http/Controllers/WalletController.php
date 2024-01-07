<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClientDetails;
use App\Models\ProjectDetails;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Payment;
use App\Models\Stage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class WalletController extends Controller
{
  public function index(){
    $user_id = Auth::user()->id;
    $role = Role::join('model_has_roles','model_has_roles.role_id','=','roles.id')->where('roles.id',$user_id)->pluck('model_has_roles.model_id')->first();
   // dd($role);
   if($role == 1){
    $wallet = Wallet::leftjoin('users','users.id','=','wallet.user_id')->leftjoin('clientdetails','clientdetails.id','=','wallet.client_id')->leftjoin('project_details','project_details.id','=','wallet.project_id')->leftjoin('payment','payment.id','=','wallet.payment_mode')->leftjoin('stage','stage.id','=','wallet.stage_id')->select('wallet.*','clientdetails.first_name as client_first','clientdetails.last_name as client_last','payment.name as payment_name','users.first_name as first_name','users.last_name as last_name','stage.name as stage_name','project_details.name as project_name')->latest()->get();
   }
   else{
    $wallet = Wallet::leftjoin('users','users.id','=','wallet.user_id')->leftjoin('clientdetails','clientdetails.id','=','wallet.client_id')->leftjoin('project_details','project_details.id','=','wallet.project_id')->leftjoin('payment','payment.id','=','wallet.payment_mode')->leftjoin('stage','stage.id','=','wallet.stage_id')->where('wallet.user_id',$user_id)->select('wallet.*','clientdetails.first_name as client_first','clientdetails.last_name as client_last','payment.name as payment_name','users.first_name as first_name','users.last_name as last_name','stage.name as stage_name','project_details.name as project_name')->latest()->get();
   }
   $sum = $wallet->sum('amount');
    return view('wallet.index',['wallet' => $wallet,'sum' =>$sum]);
  }

    public function create(Request $request){
        $client = ClientDetails::where(['active_status' => 1,'delete_status' => 0])->select('*')->get();
        $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0,'project_status' => 0])->select('*')->get();
        $payment_mode = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
        $stages = Stage::where('active_status',1)->where('delete_status',0)->get();
        return View('wallet.form',['client' => $client,'project' => $project,'payment' =>$payment_mode,'stages' =>$stages]);
        //return response()->json($view);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     // dd($request->all());
        $user_id = Auth::user()->id;
      $input = $request->all();
      $input['user_id'] = $user_id;
      $input['current_date'] = $request->current_date.' '.$request->time;
      $project_id = $request->project_id;
    //   print_r($input);
    //     exit;
      //$input['current_date'] =date('Y-m-d', strtotime($request->date));
      //print_r($input);exit;
        $wallet = Wallet::create($input);
        $project_detail = ProjectDetails::find($project_id);
        if($request->transfer_type == 0){
        $project_detail['advance_amt'] = abs($project_detail->advance_amt + $request->amount);
        $project_detail['profit'] = abs($project_detail->profit - $request->amount);
        $project_detail->update();
        $project = User::find($user_id);
       // print_r($project_detail);exit;
        $sum = abs($project->wallet + $request->amount);
        $project['wallet'] = $sum;
       // print_r($project['wallet']);exit;
        $project->update();
        }
        else{
          $project_detail['advance_amt'] = abs($project_detail->advance_amt - $request->amount);
          $project_detail['profit'] = abs($project_detail->profit + $request->amount);
          $project_detail->update();
          $project = User::find($user_id);
         // print_r($project_detail);exit;
          $sum = abs($project->wallet - $request->amount);
          $project['wallet'] = $sum;
         // print_r($project['wallet']);exit;
          $project->update();
        }
        return redirect()->route('dashboard')
        ->with('popup', 'open');

    }

}
