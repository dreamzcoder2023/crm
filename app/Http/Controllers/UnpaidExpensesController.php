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
use Excel;
use PDF;
use App\Exports\UnpaidExpensesExport;
use App\Models\MainCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UnpaidExpensesController extends Controller
{
  public function index(Request $request)
  {
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    //dd($from,$to);
    $paginate = $request->paginate ?? 10;
    $auth = Auth::user()->id;
    $role =  Auth::user()->roles[0]['name'];

    $expenses = Expenses::where('expenses.unpaid_amt', '!=', 0)->join('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->when($from, function ($query, $from) {
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to, function ($query, $to) {
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_id){
        $query->where('expenses.main_category_id',$main_id);
      })
      ->when(request('category_id'), function ($query, $category_id) {
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'), function ($query, $project_id) {
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'), function ($query, $user_id) {
        $query->where('expenses.user_id', $user_id);
      })
      ->whereNull('expenses.labour_id')->whereNull('expenses.vendor_id');
    if ($role != 'Admin') {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('users.id', $auth);
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name','main_category.name as main_category_name')
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });
    } else {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id');
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last','main_category.name as main_category_name')
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });
    }


    $expenses->orderBy($from || $to ? 'expenses.current_date' : 'expenses.id', 'desc');

    // Get paginated result
    $expenses = $expenses->paginate($paginate)->withQueryString();
    $maincategory = MainCategory::latest()->get();
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['users.active_status' => 1, 'users.delete_status' => 0])->select('users.*', 'roles.name')->get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');

    return view('unpaid_expenses.index', ['expenses' => $expenses, 'category' => $category,  'project' => $project, 'user' => $user,  'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'maincategory' => $maincategory]);
  }
  /**
   * Store a newly created resource in storage.
   */
  public function unpaid_create(Request $request)
  {
    $unpaid = Expenses::where('id', $request->id)->first();
    $datetime = explode(' ', $unpaid->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    return view('unpaid_expenses.form', ['unpaid' => $unpaid, 'current_date' => $current_date, 'current_time' => $current_time]);
  }
  public function unpaid_store(Request $request)
  {

    $user_id = Auth::user()->id;
    $input = $request->all();
    $extra_amt = 0;
    $unpaid_amt = 0;
    $input['current_date'] = $request->current_date . ' ' . $request->time;
    $expenses_date = ExpensesUnpaidDate::create($input);
    // wallet minus
    $user = User::find($user_id);
    $minus = abs($user->wallet - $request->unpaid_amt);
    $user['wallet'] = $minus;
    $user->update();
    // expense minus
    $expenses = Expenses::where('id', $request->expense_id)->first();


    $unpaid = abs($expenses->unpaid_amt - $request->unpaid_amt);
    $expenses['paid_amt'] = abs($expenses->paid_amt + $request->unpaid_amt);

    if ($expenses->amount <= $request->unpaid_amt) {
      $extra_amt = abs($request->unpaid_amt  - $expenses->amount);
    }
    if ($request->unpaid_amt < $expenses->amount) {
      $unpaid_amt = abs($expenses->amount - $expenses->paid_amt);
    }
    $expenses['extra_amt'] = $extra_amt;
    $expenses['unpaid_amt'] = $unpaid_amt;
    //dd($expenses);
    $expenses->update();
    return redirect()->route('unpaid-history')
      ->with('expenses-popup', 'open');
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
    return redirect()->route('unpaid-history')
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
  public function unpaid_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $search = $request->search;
    $main_id = $request->main_id;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }



    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new UnpaidExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role,$search,$main_id)), 'unpaid-expenses.xlsx');
  }
  public function unpaid_expense_pdf(Request $request)
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
    $role =  Auth::user()->roles[0]['name'];

    $expenses = Expenses::where('expenses.unpaid_amt', '!=', 0)->join('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->when($from,function($query,$from){
        $query->whereDate('current_date','>=',$from);
      })
      ->when($to,function($query,$to){
        $query->whereDate('current_date','<=',$to);
      })
      ->when(request('main_id'),function($query,$main_id){
        $query->where('expenses.main_category_id',$main_id);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.user_id', $user_id);
      })
      ->whereNull('expenses.labour_id')->whereNull('expenses.vendor_id');
    if ($role != 'Admin') {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.user_id')->where('users.id', $auth);
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name','main_category.name as main_category_name')
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });

    } else {
      $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id');
      $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last','main_category.name as main_category_name')
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search")
            ->orWhere('expenses.paid_amt', 'like', "%$search")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search")
            ->orWhere('expenses.extra_amt', 'like', "%$search")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });
    }
    if ($from != '' && $to != '') {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->get();
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
    }
    //  $expenses = $expenses->orderBy('expenses.id','desc')->get();
    $pdf = PDF::loadView('unpaid_expenses.unpaidpdf', compact('expenses'));

    return $pdf->download('unpaid-expenses.pdf');
  }
}
