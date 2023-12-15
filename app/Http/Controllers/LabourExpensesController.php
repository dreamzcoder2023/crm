<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\Labour;
use App\Models\Payment;
use App\Models\ProjectDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabourExpensesController extends Controller
{
  public function index(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    //$from1 = now()->format('Y-m-d');

    // if($request->from_date != ''){
    //   $from = $request->from_date.' '.'00:00:00';
    // }
    // else{
    //   $from = $from1.' '.'00:00:00';
    // }

    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';


    // print_r($from);
    // print_r($to_date);
    // exit;



    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::join('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
    if ($role != 1) {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('users.id', $auth);
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name');
    } else {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id');
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last');
    }
    if ($from != '' ) {
      $expenses = $expenses->wheredate('current_date', '>=',$from);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

    }
    if($to_date != ''){
      $expenses = $expenses->wheredate('current_date', '<=',$to_date);
    }
    if ($category_filter != 'undefined' && $category_filter != '') {
      $expenses = $expenses->where('expenses.category_id', $category_filter);
    }
    if ($project_filter != 'undefined' && $project_filter != '') {
      $expenses = $expenses->where('expenses.project_id', $project_filter);
      //dd($expenses);exit;
    }
    if ($user_filter != 'undefined' && $user_filter != '') {
      $expenses = $expenses->where('expenses.user_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->orderBy('expenses.id', 'desc')->get();



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['users.active_status' => 1, 'users.delete_status' => 0])->select('users.*', 'roles.name')->get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('labour-expenses.index', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }

  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $labours = Labour::get();
    return view('labour-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment,'labours' => $labours]);
  }
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    $user_id = Auth::user()->id;
    $input = $request->all();
    $input['user_id'] = $user_id;
    $extra_amt = 0;
    $unpaid_amt = 0;
    if ($request->amount < $request->paid_amt) {
      $extra_amt = $request->paid_amt - $request->amount;
    } else {
      $unpaid_amt = $request->amount - $request->paid_amt;
    }
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] = $unpaid_amt;
    $input['current_date'] = $request->current_date . ' ' . $request->time;
    $input['paid_amt'] = $request->paid_amt ? $request->paid_amt : 0;
    if ($image = $request->file('image')) {
      $destinationPath = 'public/images/';
      $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
      $image->move($destinationPath, $profileImage);

      $input['image'] = "$profileImage";
    }
    $expenses = Expenses::create($input);
    $project = User::find($user_id);
    $minus = $project->wallet - $request->paid_amt;
    $project['wallet'] = $minus;
    $project->update();
    return redirect()->route('expenses-history')
      ->with('expenses-popup', 'open');
  }
  public function labour_salary(Request $request){
    $labour = Labour::where('id',$request->id)->select('salary','advance_amt')->first();
    return response()->json($labour);
  }
}
