<?php

namespace App\Http\Controllers;

use App\Exports\VendorDeleteExpensesExport;
use App\Exports\VendorExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\AdvanceHistory;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\ExpensesUnpaidDate;
use App\Models\Payment;
use App\Models\ProjectDetails;
use App\Models\User;
use PDF;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VendorExpensesController extends Controller
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

    $expenses = Expenses::whereNotNull('expenses.vendor_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);

    $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance');
    $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as vendor_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last');
    if ($from != '') {
      $expenses = $expenses->wheredate('current_date', '>=', $from);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

    }
    if ($to_date != '') {
      $expenses = $expenses->wheredate('current_date', '<=', $to_date);
    }
    if ($category_filter != 'undefined' && $category_filter != '') {
      $expenses = $expenses->where('expenses.category_id', $category_filter);
    }
    if ($project_filter != 'undefined' && $project_filter != '') {
      $expenses = $expenses->where('expenses.project_id', $project_filter);
      //dd($expenses);exit;
    }
    if ($user_filter != 'undefined' && $user_filter != '') {
      $expenses = $expenses->where('expenses.vendor_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->orderBy('expenses.id', 'desc')->get();



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Vendor::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('vendor-expenses.index', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $vendors = Vendor::get();
    return view('vendor-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment, 'vendors' => $vendors]);
  }
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
    $input['vendor_id'] = $request->vendor_id;
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
    $labour = Vendor::find($request->vendor_id);
    $labour['advance_amt'] = abs($labour->advance_amt + $extra_amt);
    $labour->update();
    return redirect()->route('vendor-expenses-index')
      ->with('expenses-popup', 'Vendor Expenses Added Successfully');
  }
  public function vendor_salary(Request $request)
  {
    $labour = Vendor::where('id', $request->id)->first();
    return response()->json($labour);
  }
  public function edit(Request $request)
  {
    $expense = Expenses::leftjoin('vendor_details', 'vendor_details.id', '=', 'expenses.vendor_id')->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('expenses.id', '=', $request->id)->select('expenses.*', 'users.wallet', 'vendor_details.advance_amt')->first();
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $datetime = explode(' ', $expense->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    $vendor = Vendor::latest()->get();
    return view('vendor-expenses.edit', ['expense' => $expense, 'category' => $category, 'project' => $project, 'payment' => $payment, 'current_date' => $current_date, 'current_time' => $current_time, 'vendors' => $vendor]);
  }
  public function update(Request $request)
  {
    $user_id = Auth::user()->id;
    $input = $request->all();
    //dd($input);
    $input['editedBy'] = $user_id;
    $input['current_date'] = $request->current_date . ' ' . $request->time;



    if ($image = $request->file('image')) {

      $destinationPath = public_Path('images');
      'public/images/';
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
      $input['paid_amt'] = abs($expenses->paid_amt + $minus1);
      if (($request->paid_amt != $expenses->paid_amt) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = abs($request->paid_amt - $request->amount);
      }
      if (($request->paid_amt != $expenses->paid_amt) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
      }
    } else {


      $project = User::find($request->user_id);

      $minus1 = abs($expenses->paid_amt - $request->paid_amt);
      $minus =abs($project->wallet + $minus1);
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
    return redirect()->route('vendor-expenses-index')
      ->with('expenses-popup', 'Vendor Detail Updated Successfully');
  }
  public function vendordelete(Request $request)
  {
    $expense = Expenses::find($request->id);
    $expense['reason'] = $request->reason;
    $expense->update();
    if(!empty($expense->vendor_id)){
      $labour = Vendor::where('id',$expense->vendor_id)->first();
        $labour['advance_amt'] = $labour->advance_amt - $expense->extra_amt;
        $labour->update();
    }
    $wallet = User::find($request->user);
    $wallet['wallet'] = $wallet->wallet + $expense->paid_amt;
    $wallet->update();
    $expense->delete();
    return redirect()->route('vendor-expenses-index')
      ->with('expenses-popup', 'Vendor Detail Deleted Successfully');
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
      ->whereNotNull('expenses.vendor_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
    $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance');
    $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as labour_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last');
    if ($from != '') {
      $expenses = $expenses->wheredate('current_date', '>=', $from);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

    }
    if ($to_date != '') {
      $expenses = $expenses->wheredate('current_date', '<=', $to_date);
    }
    if ($category_filter != 'undefined' && $category_filter != '') {
      $expenses = $expenses->where('expenses.category_id', $category_filter);
    }
    if ($project_filter != 'undefined' && $project_filter != '') {
      $expenses = $expenses->where('expenses.project_id', $project_filter);
      //dd($expenses);exit;
    }
    if ($user_filter != 'undefined' && $user_filter != '') {
      $expenses = $expenses->where('expenses.vendor_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user =Vendor::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    return view('vendor-expenses.vendordeletedrecord', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function unpaid_expenses(Request $request)
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

    $expenses = Expenses::whereNotNull('vendor_id')->where('expenses.unpaid_amt', '!=', 0)->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
    $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance');
    $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as labour_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last');
    if ($from != '') {
      $expenses = $expenses->wheredate('current_date', '>=', $from);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

    }
    if ($to_date != '') {
      $expenses = $expenses->wheredate('current_date', '<=', $to_date);
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

    return view('vendor-expenses.unpaidexpenses', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function unpaid_edit(Request $request){
    $unpaid = Expenses::where('id',$request->id)->first();
    $datetime = explode(' ',$unpaid->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
   return view('vendor-expenses.unpaidform',['unpaid' => $unpaid,'current_date' => $current_date,'current_time' => $current_time]);
  }
  public function unpaid_store(Request $request){

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
    return redirect()->route('vendor-expenses-unpaid-history')
    ->with('expenses-popup', 'Vendor Unpaid Amount Updated Successfully');
  }
  public function advance_expenses(Request $request){
    $users = Vendor::latest()->get();
    return view('vendor-expenses.advanceexpense',['users' => $users]);
  }
  public function advance_form($id){
    $labour = Vendor::find($id);
    $project = Expenses::where('vendor_id', $id)->where(function ($query) {
      $query->where('extra_amt', '>', 0)
        ->orWhere('unpaid_amt', '>', 0);
    })->leftjoin('project_details', 'project_details.id', '=', 'expenses.project_id')->select('project_details.*')->groupBy('project_details.id')->get();
    return view('vendor-expenses.advanceform',['labour' => $labour,'project' => $project]);
  }
  public function advance_store(Request $request){
      //dd($request->all());
     $project = Expenses::where(['vendor_id' => $request->labour_id,'project_id' => $request->project_id ])->get();
     $labour = Vendor::where('id',$request->labour_id)->first();
     $labour['advance_amt'] = abs($labour->advance_amt - $request->extra_amt);
   //  $labour['date'] = $request->current_date . ' ' . $request->time;
     $labour->update();
     $amount = $request->extra_amt;
     foreach($project as $project){

       $amount = abs($amount-$project->extra_amt);
       $input['vendor_id'] = $request->labour_id;
       $input['expense_id'] = $project->id;
       $input['amount'] = $project->extra_amt;
       AdvanceHistory::create($input);
       if($request->extra_amt <= $project->extra_amt){
       // dd($request->extra_amt);
         $project['extra_amt'] = abs($request->extra_amt - $project->extra_amt);
       }
       else{
         $project['extra_amt'] = 0;
       }
       $project['is_advance'] = Auth::user()->id;
       $project->update();
     }
    return redirect()->route('vendor-expenses-advance-history')->with('popup','open');
  }
  public function vendor_project_amount(Request $request){
    $amount =  Expenses::where('vendor_id', $request->labour_id)->where('project_id', $request->project_id)->get();
    // dd($amount);
    $advance = $amount->sum('extra_amt');
    $unpaid_amt = $amount->sum('unpaid_amt');
    // dd($amount);
    return response()->json(['advance' => $advance, 'unpaid_amt' => $unpaid_amt]);
  }
  public function vendor_expense_pdf(Request $request){
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

    $expenses = Expenses::whereNotNull('expenses.vendor_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);

    $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance');
    $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as vendor_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last');
    if ($from != '') {
      $expenses = $expenses->wheredate('current_date', '>=', $from);
      //   ->toSql();
      //  // $bindings = $expenses->getBindings();
      //   print_r($expenses);
      //  exit;

    }
    if ($to_date != '') {
      $expenses = $expenses->wheredate('current_date', '<=', $to_date);
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
    $pdf = PDF::loadView('vendor-expense.vendorpdf', compact('expenses'));

    return $pdf->download('vendor-expenses.pdf');
  }
  public function vendor_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new VendorExpensesExport($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'vendor-expenses.xlsx');
  }
  public function vendor_delete_expense_pdf(Request $request){
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
      ->whereNotNull('expenses.vendor_id')->leftjoin('vendor_details as l','l.id','=','expenses.labour_id')->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      });


    $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad','labour_ad.id','=','expenses.is_advance');
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last','l.name as labour_name','labour_ad.first_name as labour_first','labour_ad.last_name as labour_last');
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
      $expenses = $expenses->where('expenses.vendor_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
    $pdf = PDF::loadView('vendor-expenses.deletepdf', compact('expenses'));

    return $pdf->download('vendor-delete-expenses.pdf');

  }
  public function vendor_delete_expense_export(Request $request){
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new VendorDeleteExpensesExport($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'vendor-delete-expenses.xlsx');
  }
}
