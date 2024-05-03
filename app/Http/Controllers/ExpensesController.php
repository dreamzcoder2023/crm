<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\Payment;
use App\Models\ExpensesUnpaidDate;
use App\Models\ProjectDetails;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers;
use Illuminate\Support\Facades\DB;
use Mockery\Undefined;
use App\Exports\ExportExpenses;
use App\Exports\DeleteExpensesExport;
use App\Models\Labour;
use App\Models\Vendor;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ExpensesController extends Controller
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
    if($from != '' || $to_date != ''){
    $expenses = $expenses->orderBy('expenses.current_date', 'desc')->get();
    }
    else{
      $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
    }



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['users.active_status' => 1, 'users.delete_status' => 0])->select('users.*', 'roles.name')->get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('expenses.index', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }

  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    return view('expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment]);
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
      $extra_amt = abs($request->paid_amt - $request->amount);
    } else {
      $unpaid_amt = abs($request->amount - $request->paid_amt);
    }
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] = $unpaid_amt;
    $input['current_date'] = $request->current_date . ' ' . $request->time;
    $input['paid_amt'] = $request->paid_amt ? $request->paid_amt : 0;
    if ($image = $request->file('image')) {
       $destinationPath = public_Path('images');
      $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
      $image->move($destinationPath, $profileImage);

      $input['image'] = "$profileImage";
    }
    $expenses = Expenses::create($input);
    $project = User::find($user_id);
    $minus = abs($project->wallet - $request->paid_amt);
    $project['wallet'] = $minus;
    $project->update();
    return redirect()->route('expenses-create')
      ->with('expenses-popup', 'Expenses created successfully');
  }
  public function edit(Request $request)
  {
    $expense = Expenses::join('users', 'users.id', '=', 'expenses.user_id')->where('expenses.id', '=', $request->id)->select('expenses.*', 'users.wallet')->first();
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $datetime = explode(' ', $expense->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    return view('expenses.edit', ['expense' => $expense, 'category' => $category, 'project' => $project, 'payment' => $payment, 'current_date' => $current_date, 'current_time' => $current_time]);
  }
  public function update(Request $request)
  {
    $user_id = Auth::user()->id;
    $input = $request->all();
    //dd($input);
    $input['editedBy'] = $user_id;
    $input['current_date'] = $request->current_date . ' ' . $request->time;



    if ($image = $request->file('image')) {

      $destinationPath = public_Path('images'); 'public/images/';
      $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
      $image->move($destinationPath, $profileImage);


      $input['image'] = $profileImage;
    }
    // else{
    //   if($request->image_status == '' && $request->image_status == null){}
    //     unset($input['image']);

    // }

    $expenses = Expenses::find($request->id);

    $extra_amt = $expenses->extra_amt;
    $unpaid_amt = $expenses->unpaid_amt;

    if ($expenses->paid_amt < $request->paid_amt) {


      $project = User::find($request->user_id);

      $minus1 = abs($request->paid_amt - $expenses->paid_amt);
      $minus = abs($project->wallet - $minus1);
      $project['wallet'] = $minus;
      $project->update();
      $input['paid_amt'] = $expenses->paid_amt + $minus1;
      if (($request->paid_amt != $expenses->paid_amt) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = abs($request->paid_amt - $request->amount);
      }
      if (($request->paid_amt != $expenses->paid_amt) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
      }
    } else {


      $project = User::find($request->user_id);

      $minus1 = abs($expenses->paid_amt - $request->paid_amt);
      $minus = abs($project->wallet + $minus1);
      $project['wallet'] = $minus;

      $project->update();
      $input['paid_amt'] = abs($expenses->paid_amt - $minus1);
      if (($request->paid_amt != $expenses->paid_amt) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = abs($request->paid_amt - $request->amount);
      }
      if (($request->paid_amt != $expenses->paid_amt) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
      }
      if (($request->amount != $expenses->amount) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = abs($request->paid_amt - $request->amount);
      }
      if (($request->amount != $expenses->amount) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
      }
    }

    // exit;
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] =  $unpaid_amt;
    // print_r($input);exit;
    $expenses->update($input);
    return redirect()->route('expenses-history')
      ->with('expenses-popup', 'Expenses updated successfully');
  }
  public function insufficientamt(Request $request)
  {
    $wallet = User::where('id', $request->user_id)->first();
    $amount = $request->amount;

    $response = true;
    if (($wallet->wallet >= 0) && ($amount <= $wallet->wallet)) {
      $response = false;
    }
    return response()->json($response);
  }
  public function unpaid_create(Request $request)
  {
    $unpaid = Expenses::where('id', $request->id)->first();
    $datetime = explode(' ', $unpaid->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    return view('expenses.form', ['unpaid' => $unpaid, 'current_date' => $current_date, 'current_time' => $current_time]);
  }
  public function unpaid_store(Request $request)
  {
    $user_id = Auth::user()->id;
    $input = $request->all();
    $extra_amt = 0;
    $unpaid_amt = 0;
    $input['current_date'] = $request->current_date.' '.$request->time;
    $expenses_date = ExpensesUnpaidDate::create($input);
    // wallet minus
    $user = User::find($user_id);
    $minus = abs($user->wallet - $request->unpaid_amt);
    $user['wallet'] = $minus;
    $user->update();
    // expense minus
    $expenses = Expenses::where('id',$request->expense_id)->first();


    $unpaid = abs($expenses->unpaid_amt - $request->unpaid_amt);
    $expenses['paid_amt'] = abs($expenses->paid_amt + $request->unpaid_amt);

    if($expenses->amount <= $request->unpaid_amt){
      $extra_amt = abs($request->unpaid_amt  - $expenses->amount);

    }
    if($request->unpaid_amt < $expenses->amount){
      $unpaid_amt = abs($expenses->amount - $expenses->paid_amt);
    }
    $expenses['extra_amt'] = $extra_amt;
    $expenses['unpaid_amt'] = $unpaid_amt;
    //dd($expenses);
    $expenses->update();
    return redirect()->route('expenses-history')
      ->with('expenses-popup', 'Unpaid amount updated successfully');
  }

  public function expensedelete(Request $request)
  {
    $expense = Expenses::find($request->id);
    $expense['reason'] = $request->reason;
    $expense->update();
    $wallet = User::find($request->user);
    $wallet['wallet'] = $wallet->wallet + $expense->paid_amt;
    $wallet->update();
    $expense->delete();
    return redirect()->route('expenses-history')
      ->with('message', 'Expenses Deleted Successfully');
  }
  public function image_delete(Request $request)
  {
    $image = Expenses::find($request->id);
    $image['image'] = "";
    $image->update();
    return redirect()->route('expenses-history')
      ->with('message', 'Image Deleted Successfully');
  }
  public function new_category(Request $request)
  {
    $input['name'] = $request->name;
    $value = "";
    $cat = Category::where([
      'active_status' => 1,
      'delete_status' => 0,
      'name' => $request->name
    ])->first();
    if (!empty($cat)) {
      $value = false;
    } else {
      $category = Category::create($input);
      $value = true;
    }
    return response()->json($value);
  }
  public function delete_record(Request $request)
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
    if($to_date !=''){
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
    if($from != '' || $to_date != ''){
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.current_date', 'desc')->get();
      }
      else{
      

    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
      }



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['users.active_status' => 1, 'users.delete_status' => 0])->select('users.*', 'roles.name')->get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    return view('expenses.recorddelete', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new ExportExpenses($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'expenses.xlsx');
  }
  public function expense_pdf(Request $request)
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
    if ($from != '' && $to_date != '') {
      $expenses = $expenses->whereBetween('current_date', [$from, $to_date]);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

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
    if($from != '' || $to_date != ''){
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->get();
      }
      else{
        $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
      }

   

    $pdf = PDF::loadView('expenses.expensepdf', compact('expenses'));

    return $pdf->download('expenses.pdf');
  }
  public function delete_expense_pdf(Request $request)
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
    if ($from != '' && $to_date != '') {
      $expenses = $expenses->whereBetween('current_date', [$from, $to_date]);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

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
    if($from != '' || $to_date != ''){
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.current_date', 'desc')->get();
      }
      else{
    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
      }
    $customPaper = array(0, 0, 567.00, 283.80);
    $pdf = PDF::loadView('expenses.deleteexpensepdf', compact('expenses'));

    return $pdf->download('delete-expenses.pdf');
  }
  public function delete_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';
    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new DeleteExpensesExport($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'delete-expenses.xlsx');
  }
  public function expense_delete_all(Request $request){
   // dd($request->all());
    $expense_id = $request->id;
    $expense = [];
    foreach($expense_id as $id){
      $expense = Expenses::where('id',$id)->first();
      if(!empty($expense->labour_id)){
        $labour = Labour::where('id',$expense->labour_id)->first();
        $labour['advance_amt'] = $labour->advance_amt - $expense->extra_amt;
        $labour->update();
      }
      if(!empty($expense->vendor_id)){
        $vendor = Vendor::where('id',$expense->vendor_id)->first();
        $vendor['advance_amt'] = $vendor->advance_amt - $expense->extra_amt;
        $vendor->update();
      }
      $wallet = User::where('id',$expense->user_id)->first();
      $wallet['wallet'] = $wallet->wallet + $expense->paid_amt;
      $wallet->update();
      $expense->delete();
    }
    return response()->json($expense);
  }
}
