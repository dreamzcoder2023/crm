<?php

namespace App\Http\Controllers;

use App\Exports\LabourDeleteExpensesExport;
use App\Exports\LabourExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\AdvanceHistory;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\Labour;
use App\Models\Payment;
use App\Models\ProjectDetails;
use App\Models\User;
use App\Models\Wallet;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class LabourExpensesController extends Controller
{
  public function index(Request $request)
  {
    //
    $currentYear = $request->year ? $request->year : Carbon::now()->year;

    $startOfWeek = Carbon::createFromDate($currentYear, 1, 1)->startOfWeek();
    $endOfWeek = Carbon::createFromDate($currentYear, 12, 31)->endOfWeek();
    $weekStartDates = [];
    $start_labour_date = [];
    $currentDate = $startOfWeek->copy();

    while ($currentDate->lte($endOfWeek)) {
      $weekStartDates[] = $currentDate->copy();
      $currentDate->addWeek();
    }
    $recordsData = [];
    foreach ($weekStartDates as $weekStartDate) {
      $records = [];
      $records = DB::table('expenses as w')->whereNotNull('labour_id')
        ->select([
          DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
          DB::Raw('SUM(w.extra_amt) as advance_amt'),
          DB::Raw("'{$weekStartDate->format('Y-m-d')}' as week_start_date"),
          DB::Raw("'{$weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d')}' as week_end_date"),
        ])
        ->whereBetween('w.current_date', [$weekStartDate->format('Y-m-d'), $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d')])
        ->groupBy('week_start_date')
        ->get();

      $project = DB::table('expenses as w')->whereNotNull('w.labour_id')
        ->select([
          DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
          DB::Raw('SUM(w.extra_amt) as advance_amt'),
          DB::Raw('p.name as project_name'),
          DB::Raw('p.id as project_id'),
        ])
        ->leftJoin('project_details as p', 'p.id', '=', 'w.project_id')
        ->whereBetween('w.current_date', [$weekStartDate->format('Y-m-d'), $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d')])
        ->groupBy('project_id')
        ->get();
      if (!$records->isEmpty()) {
        $start_labour_date[] = ['records' => $records, 'project' => $project];
      }
    }

    // dd($recordsData);
    //  // $labour = Expenses::whereBetween('current_date', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SATURDAY)])->get();

    $view = view('labour-expenses.index', ['start_labour_date' => $start_labour_date, 'current_year' => $currentYear])->render();
    return response()->json($view);
  }

  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0, 'name' => 'salary'])->first();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $labours = Labour::get();
    return view('labour-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment, 'labours' => $labours]);
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
    $extra_amt = 0;
    $unpaid_amt = 0;
    if ($request->amount < $request->paid_amt) {
      $extra_amt = $request->paid_amt - $request->amount;
    } else {
      $unpaid_amt = $request->amount - $request->paid_amt;
    }
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] = $unpaid_amt;
    $input['labour_id'] = $request->labour_id;
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
    $minus = $project->wallet - $request->paid_amt;
    $project['wallet'] = $minus;
    $project->update();
    $labour = Labour::find($request->labour_id);
    $labour['advance_amt'] = $labour->advance_amt + $extra_amt;
    $labour->update();
    return redirect()->route('labour-expenses-history')
      ->with('expenses-popup', 'Labour Detail Added Successfully');
  }
  public function labour_salary(Request $request)
  {
    $labour = Labour::where('id', $request->id)->select('salary', 'advance_amt')->first();
    return response()->json($labour);
  }
  public function labour_expense_project(Request $request)
  {
    $project = DB::table('expenses as e')->leftjoin('project_details as p', 'p.id', '=', 'e.project_id')->where('e.project_id', $request->project_id)->whereBetween('e.current_date', [$request->start_date, $request->end_date])->select([
      DB::Raw('SUM(e.unpaid_amt) as unpaid'),
      DB::Raw('SUM(e.extra_amt) as advance_amt'),
      DB::Raw('e.*'),
      DB::Raw('p.name as project_name')
    ])->groupBy('e.project_id')->first();
    //dd($project);
    $labour = Expenses::leftJoin('labour_details', 'labour_details.id', '=', 'expenses.labour_id')
      ->where('expenses.project_id', $request->project_id)
      ->whereNotNull('expenses.labour_id')
      ->whereBetween('expenses.current_date', [$request->start_date, $request->end_date])
      ->groupBy('expenses.labour_id')
      ->select([
        DB::Raw('SUM(expenses.unpaid_amt) as unpaid_amt'),
        DB::Raw('SUM(expenses.extra_amt) as advance_amt'),
        DB::Raw('expenses.amount'),
        // Add other columns you need, aggregating where necessary
        DB::Raw('labour_details.name as labour_name'),
        DB::Raw('labour_details.id as labour_id'),
      ])->get();
    return view('labour-expenses.projectindex', ['project' => $project, 'labour' => $labour, 'start_date' => $request->start_date, 'end_date' => $request->end_date]);
  }
  public function labour_expenses_details(Request $request)
  {
    $weekSummary = DB::table('expenses as w')->leftjoin('labour_details as l', 'l.id', '=', 'w.labour_id')
      ->whereNotNull('w.labour_id')
      ->select([
        DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
        DB::Raw('SUM(w.extra_amt) as advance_amt'),
        DB::Raw("(SELECT DAYNAME(w.current_date)) as day_of_week"),
        DB::Raw('l.name as labour_name'),
        DB::Raw('w.amount as amount'),
        DB::Raw('w.project_id'),
      ])
      ->whereBetween('w.current_date', [$request->start_date, $request->end_date])
      ->groupBy('day_of_week') // Change the grouping to use the alias
      ->get();
    $view = view('labour-expenses.labourdetails', ['labour' => $weekSummary, 'start_date' => $request->start_date, 'end_date' => $request->end_date])->render();
    // dd($view);
    return response()->json($view);
  }
  public function labour_expenses_store(Request $request)
  {
    //dd($request->all());
    $labours = '';
    foreach ($request->labour_id as $labour) {
      $labours = Expenses::where(['labour_id' => $labour, 'project_id' => $request->project_id])->whereBetween('current_date', [$request->start_date, $request->end_date])->get();
      foreach ($labours as $labours) {
        if ($labours->unpaid_amt > 0) {
          $labours['paid_amt'] = $labours->unpaid_amt + $labours->paid_amt;
          $wallet = User::find(Auth::user()->id);
          $wallet['wallet'] = $wallet->wallet - $labours->unpaid_amt;
          $wallet->update();
          $labours['unpaid_amt'] = 0;

          $labours->update();
        }
      }
    }
    return response()->json($labours);
  }
  public function labour_expenses_history(Request $request)
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

    $expenses = Expenses::whereNotNull('expenses.labour_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')
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
      $expenses = $expenses->where('expenses.labour_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->orderBy('expenses.id', 'desc')->get();



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Labour::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('labour-expenses.expensesindex', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function labour_advance(Request $request)
  {
    $users = Labour::latest()->get();
    return view('labour-expenses.labourindex', ['users' => $users]);
  }
  public function advance_form($id)
  {
    $labour = Labour::find($id);
    $project = Expenses::where('labour_id', $id)->where(function ($query) {
      $query->where('extra_amt', '>', 0)
        ->orWhere('unpaid_amt', '>', 0);
    })->leftjoin('project_details', 'project_details.id', '=', 'expenses.project_id')->select('project_details.*')->groupBy('project_details.id')->get();
    return view('labour-expenses.advanceform', ['labour' => $labour, 'project' => $project]);
  }
  public function labour_project_amount(Request $request)
  {
    $amount =  Expenses::where('labour_id', $request->labour_id)->where('project_id', $request->project_id)->get();
    // dd($amount);
    $advance = $amount->sum('extra_amt');
    $unpaid_amt = $amount->sum('unpaid_amt');
    // dd($amount);
    return response()->json(['advance' => $advance, 'unpaid_amt' => $unpaid_amt]);
  }
  public function labour_advance_store(Request $request)
  {
   // dd($request->all());
    $project = Expenses::where(['labour_id' => $request->labour_id, 'project_id' => $request->project_id])->get();
    $labour = Labour::where('id', $request->labour_id)->first();
    $labour['advance_amt'] = abs($labour->advance_amt - $request->extra_amt);
    // dd($labour);
    //  $labour['date'] = $request->current_date . ' ' . $request->time;
    $labour->update();
    $amount = $request->extra_amt;
    foreach ($project as $project) {

      $amount = abs($amount - $project->extra_amt);
      $input['labour_id'] = $request->labour_id;
      $input['expense_id'] = $project->id;
      $input['amount'] = $project->extra_amt;
      AdvanceHistory::create($input);
      if ($request->gender == 1) {
        if ($request->extra_amt <= $project->extra_amt) {
          $project['extra_amt'] = abs($request->extra_amt - $project->extra_amt);
        } else {
          $project['extra_amt'] = 0;
        }
      }
      if($request->gender == 2){
        if ($request->extra_amt <= $project->unpaid_amt) {
          $project['unpaid_amt'] = abs($request->extra_amt - $project->unpaid_amt);
        } else {
          $project['unpaid_amt'] = 0;
        }
      }
      $project['is_advance'] = Auth::user()->id;
      $project->update();
    }
    return redirect()->route('labour-expenses-advance')->with('popup', 'open');
  }
  public function edit(Request $request)
  {
    $expense = Expenses::leftjoin('labour_details', 'labour_details.id', '=', 'expenses.labour_id')->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('expenses.id', '=', $request->id)->select('expenses.*', 'users.wallet', 'labour_details.advance_amt')->first();
    $category = Category::where(['active_status' => 1, 'delete_status' => 0, 'name' => 'salary'])->first();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $datetime = explode(' ', $expense->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    $labour = Labour::latest()->get();
    return view('labour-expenses.edit', ['expense' => $expense, 'category' => $category, 'project' => $project, 'payment' => $payment, 'current_date' => $current_date, 'current_time' => $current_time, 'labours' => $labour]);
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

      $minus1 = $request->paid_amt - $expenses->paid_amt;
      $minus = $project->wallet - $minus1;
      $project['wallet'] = $minus;
      $project->update();
      $input['paid_amt'] = $expenses->paid_amt + $minus1;
      if (($request->paid_amt != $expenses->paid_amt) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = $request->paid_amt - $request->amount;
      }
      if (($request->paid_amt != $expenses->paid_amt) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = $request->amount - $request->paid_amt;
      }
    } else {


      $project = User::find($request->user_id);

      $minus1 = $expenses->paid_amt - $request->paid_amt;
      $minus = $project->wallet + $minus1;
      $project['wallet'] = $minus;

      $project->update();
      $input['paid_amt'] = $expenses->paid_amt - $minus1;
      if (($request->paid_amt != $expenses->paid_amt) && ($request->amount <= $request->paid_amt)) {
        $extra_amt = $request->paid_amt - $request->amount;
      }
      if (($request->paid_amt != $expenses->paid_amt) && ($request->paid_amt < $request->amount)) {
        $unpaid_amt = $request->amount - $request->paid_amt;
      }
    }

    // exit;
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] =  $unpaid_amt;
    // print_r($input);exit;
    $expenses->update($input);
    return redirect()->route('labour-expenses-history')
      ->with('expenses-popup', 'Labour Detail Updated Successfully');
  }
  public function labourdelete(Request $request)
  {
    $expense = Expenses::find($request->id);
    $expense['reason'] = $request->reason;
    $expense->update();
    $wallet = User::find($request->user);
    $wallet['wallet'] = $wallet->wallet + $expense->paid_amt;
    $wallet->update();
    $expense->delete();
    return redirect()->route('labour-expenses-history')
      ->with('expenses-popup', 'Labour Detail Deleted Successfully');
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
      ->whereNotNull('expenses.labour_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')->leftJoin('project_details', function ($join) {
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

    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Labour::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    return view('labour-expenses.labourdeletedrecord', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);
  }
  public function labour_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new LabourExpensesExport($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'labour-expenses.xlsx');
  }
  public function labour_expense_pdf(Request $request)
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

    $expenses = Expenses::whereNotNull('expenses.labour_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')
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
      $expenses = $expenses->where('expenses.labour_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->orderBy('expenses.id', 'desc')->get();

    $pdf = PDF::loadView('labour-expenses.expensepdf', compact('expenses'));

    return $pdf->download('labour-expenses.pdf');
  }
  public function labour_delete_expense_pdf(Request $request)
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
      ->whereNotNull('expenses.labour_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')->leftJoin('project_details', function ($join) {
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
      $expenses = $expenses->where('expenses.labour_id', $user_filter);
    }

    //dd($expenses);
    if ($request->amount != '' && $request->amount != 'undefined') {
      $expenses = $expenses->orderBy('expenses.amount', $request->amount)->get();
    }

    $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
    $pdf = PDF::loadView('labour-expenses.deletepdf', compact('expenses'));

    return $pdf->download('labour-delete-expenses.pdf');
  }
  public function labour_delete_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date . ' ' . '00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date . ' ' . '23:59:59') : '';




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new LabourDeleteExpensesExport($category_filter, $project_filter, $user_filter, $from, $to_date, $auth, $role)), 'labour-delete-expenses.xlsx');
  }
}
