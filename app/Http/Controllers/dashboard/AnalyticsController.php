<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Attendance;
use App\Models\User;
use App\Models\ProjectDetails;
use App\Models\Expenses;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
  public function index()
  {
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where('users.id', Auth::user()->id)->select('users.*', 'roles.name as role_name')->first();
    if ($user->role_name == 'Admin') {
      $checking = Attendance::where('user_id', Auth::user()->id)->whereDate('created_at', '=', now())->orderBy('id', 'desc')->first();
      $member = User::count();
      $project_open = ProjectDetails::where('active_status', 1)->where('delete_status', 0)->where('project_status', 0)->count();
      $project_close = ProjectDetails::where('active_status', 1)->where('delete_status', 0)->where('project_status', 1)->count();
      if ($user->role_name == 'Admin') {
        $unpaid_amt = Expenses::sum('unpaid_amt');
        $paid_amt = Expenses::sum('paid_amt');
        $monthly_transfer = Transfer::where('created_at', '>', now()->subDays(30)->endOfDay())
          ->sum('amount');
      } else {
        $unpaid_amt = Expenses::where('user_id', Auth::user()->id)->sum('unpaid_amt');
        $paid_amt = Expenses::where('user_id', Auth::user()->id)->sum('paid_amt');
        $monthly_transfer = Transfer::where('user_id', Auth::user()->id)->where('created_at', '>', now()->subDays(30)->endOfDay())
          ->sum('amount');
      }
      $allMonths = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];

      $income = ProjectDetails::selectRaw('DATE_FORMAT(start_date, "%b") as month, SUM(advance_amt) as total')
        ->where('start_date', '>', now()->subMonths(12)->endOfDay())
        ->groupBy('month')
        ->orderByRaw('FIELD(month, ?)', [implode(',', $allMonths)])
        ->get();
      // dd($income);
      $totalIncome = $income->sum('total');

      // Calculate the overall percentage for each month
      $incomeWithPercentage = $income->map(function ($item) use ($totalIncome) {
      if($totalIncome!= 0)
        $percentage = ($item->total / $totalIncome) * 100;
      else
      $percentage = 0;
        return [
          'percentage' => round($percentage, 2) // Round to two decimal places
        ];
      });

      // Calculate the percentage for the current week
      $currentWeekIncome = ProjectDetails::whereBetween('start_date', [
        now()->startOfWeek(),
        now()->endOfWeek()
      ])->sum('advance_amt');
      if($currentWeekIncome != 0 && $totalIncome != 0 ){
      $currentWeekPercentage = ($currentWeekIncome / $totalIncome) * 100;
      }
      else{
      $currentWeekPercentage = 0;
      }

      //expenses
      $expense = Expenses::selectRaw('DATE_FORMAT(current_date, "%b") as month, SUM(paid_amt) as total')
        ->where('current_date', '>', now()->subMonths(12)->endOfDay())
        ->groupBy('month')
        ->orderByRaw('FIELD(month, ?)', [implode(',', $allMonths)])
        ->get();
      $totalExpense = $expense->sum('total');

      // Calculate the overall percentage for each month
      $expenseWithPercentage = $expense->map(function ($item) use ($totalExpense) {
        $percentage = ($item->total / $totalExpense) * 100;
        return [
          'percentage' => round($percentage, 2) // Round to two decimal places
        ];
      });

      // Calculate the percentage for the current week
      $currentWeekExpense = Expenses::whereBetween('current_date', [
        now()->startOfWeek(),
        now()->endOfWeek()
      ])->sum('paid_amt');
 if($currentWeekExpense != 0 && $totalExpense!= 0 ){
      $currentWeekExpensePercentage = ($currentWeekExpense / $totalExpense) * 100;
      }
      else{
      $currentWeekExpensePercentage = 0;
      }
      //dd($income);
      $wallet = User::where('active_status', 1)->where('delete_status', 0)->sum('wallet');

      $transfer_history = User::select('users.id as member_id', 'users.first_name as first_name', 'users.last_name as last_name', DB::raw('SUM(transferdetails.amount) as total_amount'))
        ->leftJoin('transferdetails', 'users.id', '=', 'transferdetails.member_id')
        ->groupBy('users.id')
        ->get();
      //  dd($transfer_history);
      return view('content.dashboard.dashboards-analytics', ['checking' => $checking, 'member' => $member, 'project_open' => $project_open, 'project_close' => $project_close, 'unpaid_amt' => $unpaid_amt, 'paid_amt' => $paid_amt, 'monthly_transfer' => $monthly_transfer, 'income' => $income, 'wallet' => $wallet, 'incomeWithPercentage' => $incomeWithPercentage, 'currentWeekPercentage' => $currentWeekPercentage, 'expenseWithPercentage' => $expenseWithPercentage, 'currentWeekExpensePercentage' => $currentWeekExpensePercentage, 'expense' => $expense, 'transfer_history' => $transfer_history]);
    }else{
      $checking = Attendance::where('user_id', Auth::user()->id)->whereDate('created_at', '=', now())->orderBy('id', 'desc')->first();

      if ($user->role_name == 'Admin') {
        $unpaid_amt = Expenses::sum('unpaid_amt');
        $paid_amt = Expenses::sum('paid_amt');
        $monthly_transfer = Transfer::where('created_at', '>', now()->subDays(30)->endOfDay())
          ->sum('amount');
      } else {
        $unpaid_amt = Expenses::where('user_id', Auth::user()->id)->sum('unpaid_amt');
        $paid_amt = Expenses::where('user_id', Auth::user()->id)->sum('paid_amt');
        $monthly_transfer = Transfer::where('user_id', Auth::user()->id)->where('created_at', '>', now()->subDays(30)->endOfDay())
          ->sum('amount');
      }
      $allMonths = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];


      $expense = Expenses::selectRaw('DATE_FORMAT(current_date, "%b") as month, SUM(paid_amt) as total')
      ->where('user_id',Auth::user()->id)
        ->where('current_date', '>', now()->subMonths(12)->endOfDay())
        ->groupBy('month')
        ->orderByRaw('FIELD(month, ?)', [implode(',', $allMonths)])
        ->get();
        //dd($expense);
      $totalExpense = $expense->sum('total');

      // Calculate the overall percentage for each month
      $expenseWithPercentage = $expense->map(function ($item) use ($totalExpense) {
       if($totalExpense!= 0)
        $percentage = ($item->total / $totalExpense) * 100;
        else
        $percentage = 0;
        return [
          'percentage' => round($percentage, 2) // Round to two decimal places
        ];
      });

      // Calculate the percentage for the current week
      $currentWeekExpense = Expenses::where('user_id',Auth::user()->id)->whereBetween('current_date', [
        now()->startOfWeek(),
        now()->endOfWeek()
      ])->sum('paid_amt');

      if($currentWeekExpense != 0 && $totalExpense!= 0 ){
      $currentWeekExpensePercentage = ($currentWeekExpense / $totalExpense) * 100;
      }
      else{
      $currentWeekExpensePercentage = 0;
      }
      //dd($income);
      $wallet = User::where('active_status', 1)->where('delete_status', 0)->where('id',Auth::user()->id)->sum('wallet');

      $transfer_history = Transfer::select('users.id as member_id', 'users.first_name as first_name', 'users.last_name as last_name', DB::raw('SUM(transferdetails.amount) as total_amount'))
        ->leftJoin('users', 'users.id', '=', 'transferdetails.member_id')
        ->where('users.id',Auth::user()->id)
        ->groupBy('users.id')
        ->get();
      //  dd($transfer_history);
      return view('content.dashboard.userdashboard', ['checking' => $checking,  'unpaid_amt' => $unpaid_amt, 'paid_amt' => $paid_amt, 'monthly_transfer' => $monthly_transfer,  'expenseWithPercentage' => $expenseWithPercentage, 'currentWeekExpensePercentage' => $currentWeekExpensePercentage, 'expense' => $expense, 'transfer_history' => $transfer_history,'wallet' => $wallet]);
    }
  }
  public function store()
  {
    $user_id = Auth::user()->id;
    $input['user_id'] = $user_id;
    $input['notes'] = 0;
    $checking = Attendance::create($input);
    return redirect()->route('dashboard')
      ->with('popup1', 'open');
  }
  public function update()
  {
    $user_id = Auth::user()->id;
    $input['user_id'] = $user_id;
    $input['notes'] = 1;

    $checking = Attendance::where('user_id', $user_id)->whereDate('created_at', '=', now())->orderBy('id', 'desc')->first();
    $startTime = Carbon::parse($checking->created_at);
    $finishTime = Carbon::parse(now());

    $totalDuration = $finishTime->diffInSeconds($startTime);
    $hours = $this->convert_hrs($totalDuration);
    $input['duration'] = $hours;
    $checking->update($input);
    return redirect()->route('dashboard')
      ->with('popup2', 'open');
  }
  public function convert_hrs($value)
  {
    $day = floor($value / 86400);
    $hours = floor(($value - ($day * 86400)) / 3600);
    $minutes = floor(($value / 60) % 60);
    $seconds = $value % 60;
    //"$day:$hours:$minutes:$seconds";
    return $hours . ' hours ' . $minutes . ' minutes ';
  }
}
