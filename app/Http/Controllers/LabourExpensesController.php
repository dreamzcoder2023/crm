<?php

namespace App\Http\Controllers;

use App\Exports\LabourDeleteExpensesExport;
use App\Exports\LabourExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\AdvanceHistory;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\Labour;
use App\Models\MainCategory;
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
      $start_date = $weekStartDate->format('Y-m-d') . ' ' . '00:00:00';
      $end_date = $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d') . ' ' . '23:59:59';
      //  dd($end_date);
      $records = DB::table('expenses as w')->whereNotNull('labour_id')
        ->select([
          DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
          DB::Raw('SUM(w.extra_amt) as advance_amt'),
          DB::Raw("'{$weekStartDate->format('Y-m-d')}' as week_start_date"),
          DB::Raw("'{$weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d')}' as week_end_date"),
        ])
        ->whereNull('w.deleted_at')
        ->whereBetween('w.current_date', [$start_date, $end_date])
        ->groupBy('week_start_date')
        ->get();

      $project = DB::table('expenses as w')->whereNotNull('w.labour_id')
        ->select([
          DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
          DB::Raw('SUM(w.extra_amt) as advance_amt'),
          DB::Raw('p.name as labour_name'),
          DB::Raw('p.id as labour_id'),
        ])
        ->whereNull('w.deleted_at')
        ->leftJoin('labour_details as p', 'p.id', '=', 'w.labour_id')
        ->whereBetween('w.current_date', [$start_date, $end_date])
        ->groupBy('w.labour_id')
        ->get();
      if (!$records->isEmpty()) {
        $start_labour_date[] = ['records' => $records, 'labour' => $project];
      }
    }

    // dd($recordsData);
    //  // $labour = Expenses::whereBetween('current_date', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SATURDAY)])->get();

    $view = view('labour-expenses.index', ['start_labour_date' => $start_labour_date, 'current_year' => $currentYear])->render();
    return response()->json($view);
  }

  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $labours = Labour::get();
    $main_category = MainCategory::where('status',1)->where('expenses_id',2)->latest()->get();
    return view('labour-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment, 'labours' => $labours,'main_category' => $main_category]);
  }
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // dd($request->all());
    $user_id = Auth::user()->id;
    $input = $request->all();
     $category_name = Category::where('id',$request->category_id)->first()->name;
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
    $input['labour_id'] = $request->labour_id;
    $input['current_date'] = $request->current_date . ' ' . $request->time;
    $input['paid_amt'] = $request->paid_amt ? $request->paid_amt : 0;
    if ($image = $request->file('image')) {
      $destinationPath = public_Path('images/'.$category_name);
      $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
      $image->move($destinationPath, $profileImage);

      $input['image'] = "$profileImage";
    }
    $expenses = Expenses::create($input);
    $project = User::find($user_id);
    $minus = abs($project->wallet - $request->paid_amt);
    $project['wallet'] = $minus;
    $project->update();
    $labour = Labour::find($request->labour_id);
    $labour['advance_amt'] = abs($labour->advance_amt + $extra_amt);
    $labour->update();
    return redirect()->route('labour-expenses-create')
      ->with('expenses-popup', 'Labour Detail Added Successfully');
  }
  public function labour_salary(Request $request)
  {
    $labour = Labour::where('id', $request->id)->select('salary', 'advance_amt')->first();
    return response()->json($labour);
  }
  public function labour_expense_project(Request $request)
  {
   // dd($request->all());
    $paginate = $request->paginate??15;
    $start_date = (isset($request->start_date) && $request->start_date != 'undefined') ? ($request->start_date . ' ' . '00:00:00') : '';
    $end_date = (isset($request->end_date) && $request->end_date != 'undefined') ? ($request->end_date . ' ' . '23:59:59') : '';

    $labour = DB::table('expenses as e')->leftjoin('labour_details as p', 'p.id', '=', 'e.labour_id')->where('e.labour_id', $request->labour_id)->wheredate('e.current_date', '>=', $start_date)->wheredate('e.current_date', '<=', $end_date)->select([
      DB::Raw('SUM(e.unpaid_amt) as unpaid'),
      DB::Raw('SUM(e.extra_amt) as advance_amt'),
      DB::Raw('e.*'),
      DB::Raw('p.name as labour_name')
    ])->whereNull('e.deleted_at')->first();
    // dd($project);
    $project = Expenses::leftJoin('project_details', 'project_details.id', '=', 'expenses.project_id')
      ->where('expenses.labour_id', $request->labour_id)
      ->whereNotNull('expenses.labour_id')
      ->whereNull('expenses.deleted_at')
      ->whereBetween('expenses.current_date', [$start_date, $end_date])
      ->groupBy('expenses.project_id')
      ->when(request('search'),function($query,$search){
        $query->where(function ($q) use ($search) {
          $q->where('project_details.name','like',"%$search%")
          ->orWhere('expenses.amount','like',"%$search%")
          ->orWhere('expenses.unpaid_amt','like',"%$search%")
          ->orWhere('expenses.extra_amt','like',"%$search%");
        });
      })
      ->select([
        DB::Raw('SUM(expenses.unpaid_amt) as unpaid_amt'),
        DB::Raw('SUM(expenses.extra_amt) as advance_amt'),
        DB::Raw('expenses.amount'),
        // Add other columns you need, aggregating where necessary
        DB::Raw('project_details.name as project_name'),
        DB::Raw('project_details.id as project_id'),
      ])->paginate($paginate)->withQueryString();
    //dd($project);
    $labour_disable = $project->where('unpaid_amt', '>', 0)->count();
    // dd($labour_disable);
    return view('labour-expenses.projectindex', ['projects' => $project, 'labour' => $labour, 'start_date' => $request->start_date, 'end_date' => $request->end_date, 'labour_disable' => $labour_disable]);
  }
  public function labour_expenses_details(Request $request)
  {
    //dd($request->all());
    $start_date = (isset($request->start_date) && $request->start_date != 'undefined') ? ($request->start_date . ' ' . '00:00:00') : '';
    $end_date = (isset($request->end_date) && $request->end_date != 'undefined') ? ($request->end_date . ' ' . '23:59:59') : '';
    $weekSummary = DB::table('expenses as w')->leftjoin('labour_details as l', 'l.id', '=', 'w.labour_id')
      ->where('w.labour_id', $request->labour_id)
      ->where('w.project_id', $request->project_id)
      ->select([
        DB::Raw('SUM(w.unpaid_amt) as unpaid_amt'),
        DB::Raw('SUM(w.extra_amt) as advance_amt'),
        DB::Raw("(SELECT DAYNAME(w.current_date)) as day_of_week"),
        DB::Raw('l.name as labour_name'),
        DB::Raw('w.amount as amount'),
        DB::Raw('w.project_id'),
        DB::Raw('w.description'),
      ])
      ->whereBetween('w.current_date', [$start_date, $end_date])
      ->groupBy('day_of_week') // Change the grouping to use the alias
      ->get();
    $view = view('labour-expenses.labourdetails', ['labour' => $weekSummary, 'start_date' => $request->start_date, 'end_date' => $request->end_date])->render();
    // dd($view);
    return response()->json($view);
  }
  public function labour_expenses_store(Request $request)
  {
    // dd($request->all());
    $start_date = (isset($request->start_date) && $request->start_date != 'undefined') ? ($request->start_date . ' ' . '00:00:00') : '';
    $end_date = (isset($request->end_date) && $request->end_date != 'undefined') ? ($request->end_date . ' ' . '23:59:59') : '';
    $user = Auth::user()->wallet;
    $projects = '';
    foreach ($request->project_id as $project) {
      $projects = Expenses::where(['project_id' => $project, 'labour_id' => $request->labour_id])->whereBetween('current_date', [$start_date, $end_date])->get();
      //  dd($labours);
      foreach ($projects as $projects) {
        if ($user > 0) {
          if ($projects->unpaid_amt > 0) {
            $projects['paid_amt'] = abs($projects->unpaid_amt + $projects->paid_amt);
            $wallet = User::find(Auth::user()->id);
            $wallet['wallet'] = abs($wallet->wallet - $projects->unpaid_amt);
            $wallet->update();
            $projects['unpaid_amt'] = 0;

            $projects->update();
          }
        } else {
          return response()->json('error');
        }
      }
    }
    return response()->json('success');
  }
  public function labour_expenses_history(Request $request)
  {
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    $paginate = $request->paginate ?? 10;

    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::whereNotNull('expenses.labour_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->select(
        'expenses.*',
        'category.name as category_name',
        'project_details.name as project_name',
        'payment.name as payment_name',
        'users.first_name',
        'users.last_name',
        'users_add.first_name as first',
        'users_add.last_name as last',
        'l.name as labour_name',
        'labour_ad.first_name as labour_first',
        'labour_ad.last_name as labour_last',
        'main_category.name as main_category_name'
      )
      ->when($from, function ($query, $from) {
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to, function ($query, $to) {
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category){
        $query->where('expenses.main_category_id',$main_category);
      })
      ->when(request('category_id'), function ($query, $category_id) {
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'), function ($query, $project_id) {
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'), function ($query, $user_id) {
        $query->where('expenses.labour_id', $user_id);
      })
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });
    if ($from != '' && $to != '') {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->paginate($paginate);
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->paginate($paginate);
    }
    //dd($expenses);


    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $main_category = MainCategory::where('status',1)->where('expenses_id',2)->latest()->get();
    $user = Labour::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('labour-expenses.expensesindex', ['expenses' => $expenses, 'category' => $category,  'project' => $project, 'user' => $user,  'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'main_category' => $main_category]);
  }
  public function labour_advance(Request $request)
  {
    $paginate = $request->paginate??15;
    $users = Labour::leftjoin('labour_role as l','l.id','=','labour_details.labour_role')
              ->select('labour_details.*','l.name as labour_role')
              ->when(request('search'),function($query,$search){
              $query->where(function ($q) use ($search) {
                $q->where('labour_details.name','like',"%$search%")
                ->orWhere('labour_details.job_title','like',"%$search%")
                ->orWhere('labour_details.salary','like',"%$search%")
                ->orWhere('l.name','like',"%$search%")
                ->orWhere('labour_details.advance_amt','like',"%$search%");
              });
            })->latest()->paginate($paginate);
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
    //  dd($request->all());
    $project = Expenses::where(['labour_id' => $request->labour_id, 'project_id' => $request->project_id])->get();
    $project_advance = Expenses::where('labour_id', $request->labour_id)->where('extra_amt', '>', 0)->get();
    $labour = Labour::where('id', $request->labour_id)->first();
    $labour['advance_amt'] = abs($labour->advance_amt - $request->extra_amt);
    // dd($labour);
    //  $labour['date'] = $request->current_date . ' ' . $request->time;

    $subamount = $request->extra_amt;
    foreach ($project as $project) {
      if ($subamount > 0) {
        if ($subamount <= $project->unpaid_amt) {
          $amount = 0;
        } else {
          $amount = abs($subamount - $project->unpaid_amt);
        }
        $input['labour_id'] = $request->labour_id;
        $input['expense_id'] = $project->id;
        $input['amount'] = $amount;
        AdvanceHistory::create($input);
        if ($subamount <= $project->unpaid_amt) {
          $project['unpaid_amt'] = abs($subamount - $project->unpaid_amt);
        } else {
          $project['unpaid_amt'] = 0;
        }
        $subamount = $amount;
        // }
        $project['is_advance'] = Auth::user()->id;
        // dd($project);
        $project->update();
      }
    }
    foreach ($project_advance as $project) {

      if ($project->extra_amt > 0) {
        if ($request->extra_amt <= $project->extra_amt) {
          $project['extra_amt'] = abs($request->extra_amt - $project->extra_amt);
        } else {
          $project['extra_amt'] = 0;
        }
      }
      // dd($project);
      $project->update();
    }
    $labour->update();
    return redirect()->route('labour-expenses-advance')->with('popup', 'open');
  }
  public function edit(Request $request)
  {
    $expense = Expenses::leftjoin('labour_details', 'labour_details.id', '=', 'expenses.labour_id')->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('expenses.id', '=', $request->id)->select('expenses.*', 'users.wallet', 'labour_details.advance_amt')->first();
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $datetime = explode(' ', $expense->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    $labour = Labour::latest()->get();
    $main_category = MainCategory::where('status',1)->where('expenses_id',2)->latest()->get();
    return view('labour-expenses.edit', ['expense' => $expense, 'category' => $category, 'project' => $project, 'payment' => $payment, 'current_date' => $current_date, 'current_time' => $current_time, 'labours' => $labour,'main_category' => $main_category]);
  }
  public function update(Request $request)
  {
    $user_id = Auth::user()->id;
    $input = $request->all();
    //dd($input);
    $category_name = Category::where('id',$request->category_id)->first()->name;
    $input['editedBy'] = $user_id;
    $input['current_date'] = $request->current_date . ' ' . $request->time;



    if ($image = $request->file('image')) {

      $destinationPath = public_Path('images/'.$category_name);
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
    //  dd('if');
      $project = User::find($request->user_id);

      $minus1 = abs($request->paid_amt - $expenses->paid_amt);
      $minus = abs($project->wallet - $minus1);
      $project['wallet'] = $minus;
      $project->update();
      $input['paid_amt'] = abs($expenses->paid_amt + $minus1);
    
      if ($request->amount < $request->paid_amt) {
        $extra_amt = abs($request->paid_amt - $request->amount);
        $unpaid_amt = 0;
      } else {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
        $extra_amt = 0;
      }
      $labour = Labour::where('id', $expenses->labour_id)->first();
      $labour['advance_amt'] = $labour->advance_amt + $extra_amt;
    } else {
     // dd('else');
      $project = User::find($request->user_id);

      $minus1 = abs($expenses->paid_amt - $request->paid_amt);
      $minus = $project->wallet + $minus1;
      $project['wallet'] = $minus;

      $project->update();
      $input['paid_amt'] = $expenses->paid_amt - $minus1;
     
      if ($request->amount < $request->paid_amt) {
        $extra_amt = abs($request->paid_amt - $request->amount);
        $unpaid_amt = 0;
      } else {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
        $extra_amt = 0;
      }
      $labour = Labour::where('id', $expenses->labour_id)->first();
      $labour['advance_amt'] = $labour->advance_amt - $extra_amt;
    }
   // dd($labour,$extra_amt);
    // exit;
    $input['extra_amt'] = $extra_amt;
    $input['unpaid_amt'] =  $unpaid_amt;
  //  dd($input);
    $labour->update();
    $expenses->update($input);
    return redirect()->route('labour-expenses-history')
      ->with('expenses-popup', 'Labour Detail Updated Successfully');
  }
  public function labourdelete(Request $request)
  {
    $expense = Expenses::find($request->id);
    $expense['reason'] = $request->reason;
    $expense->update();
    if (!empty($expense->labour_id)) {
      $labour = Labour::where('id', $expense->labour_id)->first();
      $labour['advance_amt'] = $labour->advance_amt - $expense->extra_amt;
      $labour->update();
    }
    $wallet = User::find($request->user);
    $wallet['wallet'] = $wallet->wallet + $expense->paid_amt;
    $wallet->update();
    $expense->delete();
    return redirect()->route('labour-expenses-history')
      ->with('expenses-popup', 'Labour Detail Deleted Successfully');
  }
  public function delete_record(Request $request)
  {

    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    $paginate = $request->paginate ?? 10;

    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::join('category', 'category.id', '=', 'expenses.category_id')
      ->whereNotNull('expenses.labour_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as labour_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last','main_category.name as main_category_name')
      ->when($from, function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to, function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category){
        $query->where('expenses.main_category_id',$main_category);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id',$category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.user_id', $user_id);
      })
      ->when(request('search'),function($query,$search){
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
          ->orWhere('project_details.name', 'like', "%$search%")
          ->orWhere('payment.name', 'like', "%$search%")
          ->orWhere('users.first_name', 'like', "%$search%")
          ->orWhere('users.last_name', 'like', "%$search%")
          ->orWhere('users_add.first_name', 'like', "%$search%")
          ->orWhere('users_add.last_name', 'like', "%$search%")
          ->orWhere('l.name', 'like', "%$search%")
          ->orWhere('labour_ad.first_name', 'like', "%$search%")
          ->orWhere('labour_ad.last_name', 'like', "%$search%")
          ->orWhere('expenses.amount', 'like', "%$search")
          ->orWhere('expenses.paid_amt', 'like', "%$search")
          ->orWhere('expenses.unpaid_amt', 'like', "%$search")
          ->orWhere('expenses.extra_amt', 'like', "%$search")
          ->orWhere('expenses.reason','like',"%$search%")
          ->orWhere('main_category.name','like',"%$search%")
          ->orWhere('expenses.description', 'like', "%$search%");

        });
      });
    if ($from != '' && $to != '') {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.current_date', 'desc')->paginate($paginate);
    } else {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->paginate($paginate);
    }
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Labour::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    $main_category = MainCategory::where('status',1)->where('expenses_id',2)->latest()->get();
    return view('labour-expenses.labourdeletedrecord', ['expenses' => $expenses, 'category' => $category,'project' => $project, 'user' => $user, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'main_category' => $main_category]);
  }
  public function labour_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $search = $request->search;
    $main_category_id = $request->main_category_id;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }

    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();
    return Excel::download((new LabourExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role, $search,$main_category_id)), 'labour-expenses.xlsx');
  }
  public function labour_expense_pdf(Request $request)
  {
   $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    $paginate = $request->paginate ?? 10;

    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::whereNotNull('expenses.labour_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name',
      'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first',
      'users_add.last_name as last', 'l.name as labour_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last','main_category.name as main_category_name')
      ->when($from, function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to, function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category_id){
        $query->where('expenses.main_category_id',$main_category_id);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.labour_id', $user_id);
      })
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });

    if ($from != '' && $to != '') {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->get();
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
    }

    // $expenses = $expenses->orderBy('expenses.id', 'desc')->get();

    $pdf = PDF::loadView('labour-expenses.expensepdf', compact('expenses'));

    return $pdf->download('labour-expenses.pdf');
  }
  public function labour_delete_expense_pdf(Request $request)
  {
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    $paginate = $request->paginate ?? 10;

    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::join('category', 'category.id', '=', 'expenses.category_id')
      ->whereNotNull('expenses.labour_id')->leftjoin('labour_details as l', 'l.id', '=', 'expenses.labour_id')->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as labour_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last','main_category.name as main_category_name')
      ->when($from, function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to,function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category_id){
        $query->where('expenses.main_category_id', $main_category_id);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.labour_id', $user_id);
      })
      ->when(request('search'),function($query,$search){
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
          ->orWhere('project_details.name', 'like', "%$search%")
          ->orWhere('payment.name', 'like', "%$search%")
          ->orWhere('users.first_name', 'like', "%$search%")
          ->orWhere('users.last_name', 'like', "%$search%")
          ->orWhere('users_add.first_name', 'like', "%$search%")
          ->orWhere('users_add.last_name', 'like', "%$search%")
          ->orWhere('l.name', 'like', "%$search%")
          ->orWhere('labour_ad.first_name', 'like', "%$search%")
          ->orWhere('labour_ad.last_name', 'like', "%$search%")
          ->orWhere('expenses.amount', 'like', "%$search")
          ->orWhere('expenses.paid_amt', 'like', "%$search")
          ->orWhere('expenses.unpaid_amt', 'like', "%$search")
          ->orWhere('expenses.extra_amt', 'like', "%$search")
          ->orWhere('expenses.reason','like',"%$search%")
          ->orWhere('main_category.name','like',"%$search%")
          ->orWhere('expenses.description', 'like', "%$search%");

        });
      });
    if ($from != '' && $to != '') {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.current_date', 'desc')->get();
    } else {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
    }
    // $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
    $pdf = PDF::loadView('labour-expenses.deletepdf', compact('expenses'));

    return $pdf->download('labour-delete-expenses.pdf');
  }
  public function labour_delete_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $from = null;
    $to = null;
    $search = $request->search;
    $main_category = $request->main_category_id;
    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }



    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new LabourDeleteExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role, $search,$main_category)), 'labour-delete-expenses.xlsx');
  }
  public function labour_total_records(Request $request)
  {
    $labour_name= Labour::where('id',$request->labour_id)->first();
    $labours = Expenses::where('labour_id', $request->labour_id)->leftjoin('project_details', 'project_details.id', '=', 'expenses.project_id')->leftjoin('labour_details','expenses.labour_id','=','labour_details.id')->select('expenses.*', 'project_details.name as project_name','labour_details.name as labour_name')->get();
    // dd($labours);
    $view = view('labour-expenses.labourtotalexpenses', ['labours' => $labours,'labour_name' => $labour_name])->render();
    return response()->json($view);
  }
}
