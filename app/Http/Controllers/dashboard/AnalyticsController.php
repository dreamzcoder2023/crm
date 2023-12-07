<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Attendance;
use App\Models\User;
use App\Models\ProjectDetails;
use App\Models\Expenses;
use App\Models\Transfer;
use Auth;
use Carbon\Carbon;
//use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
  public function index()
  {
    $user = User::join('model_has_roles','model_has_roles.model_id','=','users.id')->join('roles','roles.id','=','model_has_roles.role_id')->where('users.id',Auth::user()->id)->select('users.*','roles.name as role_name')->first();
   
    $checking = Attendance::where('user_id',Auth::user()->id)->whereDate('created_at','=',now())->orderBy('id','desc')->first();
    $member = User::count();
    $project_open = ProjectDetails::where('project_status',0)->count();
    $project_close = ProjectDetails::where('project_status',1)->count();
    if($user->role_name == 'Admin')
    {
        $unpaid_amt = Expenses::sum('unpaid_amt');
        $paid_amt = Expenses::sum('paid_amt');
        $monthly_transfer = Transfer::where('created_at', '>', now()->subDays(30)->endOfDay())
        ->sum('amount');
    }
    else{
      $unpaid_amt = Expenses::where('user_id',Auth::user()->id)->sum('unpaid_amt');
      $paid_amt = Expenses::where('user_id',Auth::user()->id)->sum('paid_amt');
      $monthly_transfer = Transfer::where('user_id',Auth::user()->id)->where('created_at', '>', now()->subDays(30)->endOfDay())
      ->sum('amount');
    }
    $income = ProjectDetails::where('created_at', '>', now()->subDays(30)->endOfDay())->sum('advance_amt');
    $wallet = User::where('active_status',1)->where('delete_status',0)->sum('wallet');
    //dd($checking);
    return view('content.dashboard.dashboards-analytics',['checking' =>$checking,'member' => $member,'project_open' =>$project_open ,'project_close' => $project_close,'unpaid_amt' => $unpaid_amt,'paid_amt' =>$paid_amt,'monthly_transfer' => $monthly_transfer,'income' => $income,'wallet' => $wallet]);
  }
  public function store(){
    $user_id = Auth::user()->id;
    $input['user_id'] = $user_id;
    $input['notes'] = 0;
   $checking = Attendance::create($input);
   return redirect()->route('dashboard')
        ->with('popup1', 'open');

  }
  public function update(){
    $user_id = Auth::user()->id;
    $input['user_id'] = $user_id;
    $input['notes'] = 1;

   $checking = Attendance::where('user_id',$user_id)->whereDate('created_at','=',now())->orderBy('id','desc')->first();
   $startTime = Carbon::parse($checking->created_at);
    $finishTime = Carbon::parse(now());

    $totalDuration = $finishTime->diffInSeconds($startTime);
    $hours = $this->convert_hrs($totalDuration);
    $input['duration'] = $hours;
   $checking->update($input);
   return redirect()->route('dashboard')
        ->with('popup2', 'open');
  }
  public function convert_hrs($value){
    $day = floor($value / 86400);
    $hours = floor(($value -($day*86400)) / 3600);
    $minutes = floor(($value / 60) % 60);
    $seconds = $value % 60;
//"$day:$hours:$minutes:$seconds";
    return $hours.' hours '.$minutes.' minutes ';
}
}
