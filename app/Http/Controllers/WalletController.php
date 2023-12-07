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


class WalletController extends Controller
{

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
        $project_detail['advance_amt'] = $project_detail->advance_amt + $request->amount;
        $project_detail['profit'] = $project_detail->profit - $request->amount;
        $project_detail->update();
        $project = User::find($user_id);
       // print_r($project_detail);exit;
        $sum = $project->wallet + $request->amount;
        $project['wallet'] = $sum;
       // print_r($project['wallet']);exit;
        $project->update();
        return redirect()->route('dashboard')
        ->with('popup', 'open');
    
    }
   
}
