<?php

namespace App\Http\Controllers;

use App\Exports\VendorDeleteExpensesExport;
use App\Exports\VendorExpensesExport;
use App\Exports\VendorUnpaidExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\AdvanceHistory;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\ExpensesUnpaidDate;
use App\Models\MainCategory;
use App\Models\Payment;
use App\Models\ProjectDetails;
use App\Models\Transfer;
use App\Models\User;
use PDF;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VendorExpensesController extends Controller
{
  public function index(Request $request)
  {
    //dd($request->search);
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }

    $paginate = $request->paginate ?? 10;
    $auth = Auth::user()->id;

    $role = DB::table('model_has_roles')
      ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
      ->where('model_has_roles.model_id', $auth)
      ->pluck('roles.id')
      ->first();

    $expenses = Expenses::whereNotNull('expenses.vendor_id')
      ->leftJoin('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', '=', 'expenses.project_id')
          ->whereNotNull('expenses.project_id');
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftJoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->leftJoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftJoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftJoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->select(
        'expenses.*',
        'category.name as category_name',
        'project_details.name as project_name',
        'payment.name as payment_name',
        'users.first_name',
        'users.last_name',
        'users_add.first_name as first',
        'users_add.last_name as last',
        'l.name as vendor_name',
        'labour_ad.first_name as labour_first',
        'labour_ad.last_name as labour_last',
        'main_category.name as main_category_name'
      )
      ->when($from, function ($query, $from) {
        $query->whereDate('expenses.current_date', '>=', $from);
      })
      ->when($to, function ($query, $to) {
        $query->whereDate('expenses.current_date', '<=', $to);
      })
      ->when(request('main_category_id'), function($query,$main_category_id){
        $query->where('expenses.main_category_id',$main_category_id);
      })
      ->when(request('category_id'), function ($query, $category_id) {
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'), function ($query, $project_id) {
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'), function ($query, $user_id) {
        $query->where('expenses.vendor_id', $user_id);
      })
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });


    if ($from && $to) {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->paginate($paginate)->withQueryString();
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->paginate($paginate)->withQueryString();
    }



    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Vendor::get();
    $main_category = MainCategory::where('status',1)->latest()->get();
    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    //dd($advanced_amt);

    return view('vendor-expenses.index', ['expenses' => $expenses, 'category' => $category,  'project' => $project, 'user' => $user,  'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'main_category' =>$main_category]);
  }
  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $vendors = Vendor::get();
    $main_category = MainCategory::where('status',1)->latest()->get();
    return view('vendor-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment, 'vendors' => $vendors,'main_category' =>$main_category]);
  }
  public function store(Request $request)
  {
    //dd($request->all());
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
    $input['extra_amt'] = 0;
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
    $vendor = Vendor::find($request->vendor_id);
    //dd($vendor);
    $minus = abs($vendor->advance_amt - $request->paid_amt);
    $vendor['advance_amt'] = $minus;
    $vendor->update();
    // $labour = Vendor::find($request->vendor_id);
    // $labour['advance_amt'] = abs($labour->advance_amt + $extra_amt);
    // $labour->update();
    return redirect()->route('vendor-expenses-create')
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
    $main_category = MainCategory::where('status',1)->latest()->get();
    return view('vendor-expenses.edit', ['expense' => $expense, 'category' => $category, 'project' => $project, 'payment' => $payment, 'current_date' => $current_date, 'current_time' => $current_time, 'vendors' => $vendor,'main_category' => $main_category]);
  }
  public function update(Request $request)
  {
   // dd($request->all());
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
    $expenses = Expenses::find($request->id);

    $extra_amt = $expenses->extra_amt;
    $unpaid_amt = $expenses->unpaid_amt;

    if ($expenses->paid_amt < $request->paid_amt) {

//dd('if');
      $project = Vendor::find($request->user_id);

      $minus1 = abs($request->paid_amt - $expenses->paid_amt);
      $minus = abs($project->advance_amt - $minus1);
      $project['advance_amt'] = $minus;
      $project->update();
      $input['paid_amt'] = abs($expenses->paid_amt + $minus1);

      if ($request->amount < $request->paid_amt) {
        $extra_amt = abs($request->paid_amt - $request->amount);
        $unpaid_amt = 0;
      } else {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
        $extra_amt = 0;
      }
    } else {

// dd('else');
      $project = Vendor::find($request->user_id);

      $minus1 = abs($expenses->paid_amt - $request->paid_amt);
      $minus = abs($project->advance_amt + $minus1);
      $project['advance_amt'] = $minus;

      $project->update();
      $input['paid_amt'] = abs($expenses->paid_amt - $minus1);

      if ($request->amount < $request->paid_amt) {
        $extra_amt = abs($request->paid_amt - $request->amount);
        $unpaid_amt = 0;
      } else {
        $unpaid_amt = abs($request->amount - $request->paid_amt);
        $extra_amt = 0;
      }
    }
    // exit;
    $input['extra_amt'] = 0;
    $input['unpaid_amt'] =  $unpaid_amt;

    $expenses->update($input);
    return redirect()->route('vendor-expenses-index')
      ->with('expenses-popup', 'Vendor Detail Updated Successfully');
  }
  public function vendordelete(Request $request)
  {
//    dd($request->all());
    $expense = Expenses::find($request->id);
    $expense['reason'] = $request->reason;
    $expense->update();
   // dd($expense);
    if (!empty($expense->vendor_id)) {
     // dd('if');
      $labour = Vendor::where('id', $expense->vendor_id)->first();
      $labour['advance_amt'] = $labour->advance_amt + $expense->paid_amt;
   //   dd($labour);
      $labour->update();
    }
    $expense->delete();
    return redirect()->route('vendor-expenses-index')
      ->with('expenses-popup', 'Vendor Detail Deleted Successfully');
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

    $role = DB::table('model_has_roles')
      ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
      ->where('model_has_roles.model_id', $auth)
      ->pluck('roles.id')
      ->first();

    $expenses = Expenses::join('category', 'category.id', '=', 'expenses.category_id')
      ->whereNotNull('expenses.vendor_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')->leftJoin('project_details', function ($join) {
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
      ->when($from,function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to,function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category){
        $query->where('expenses.main_category_id',$main_category);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.vendor_id', $user_id);
      })
      ->when(request('search'),function($query,$search){
             $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
            ->orWhere('expenses.reason','like',"%$search%")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });
    if ($from != '' && $to != '') {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.current_date', 'desc')->paginate($paginate)->withQueryString();
    } else {
      $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->paginate($paginate)->withQueryString();
    }
  
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Vendor::get();

    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');
    $main_category = MainCategory::where('status',1)->latest()->get();
    return view('vendor-expenses.vendordeletedrecord', ['expenses' => $expenses, 'category' => $category,  'project' => $project, 'user' => $user,  'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'main_category' => $main_category]);
  }
  public function unpaid_expenses(Request $request)
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

    $expenses = Expenses::whereNotNull('vendor_id')->where('expenses.unpaid_amt', '!=', 0)->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')
      ->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')
      ->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as vendor_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last','main_category.name as main_category_name')
      ->when($from,function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to,function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category){
        $query->where('expenses.main_category_id',$main_category);
      })
      ->when(request('category_id'),function($query,$category_id){
        $query->where('expenses.category_id', $category_id);
      })
      ->when(request('project_id'),function($query,$project_id){
        $query->where('expenses.project_id', $project_id);
      })
      ->when(request('user_id'),function($query,$user_id){
        $query->where('expenses.vendor_id', $user_id);
      })
     ->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
            ->orWhere('main_category.name', 'like', "%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });


    if ($from && $to) {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->paginate($paginate)->withQueryString();
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->paginate($paginate)->withQueryString();
    }
    $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
    $user = Vendor::get();
    $main_category = MainCategory::where('status',1)->latest()->get();
    $sum = $expenses->sum('amount');
    $paid_amt = $expenses->sum('paid_amt');
    $unpaid_amt = $expenses->sum('unpaid_amt');
    $advanced_amt = $expenses->sum('extra_amt');

    return view('vendor-expenses.unpaidexpenses', ['expenses' => $expenses, 'category' => $category, 'project' => $project, 'user' => $user,  'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt,'main_category' =>$main_category]);
  }
  public function unpaid_edit(Request $request)
  {
    $unpaid = Expenses::where('id', $request->id)->first();
    $datetime = explode(' ', $unpaid->current_date);
    $current_date = $datetime[0];
    $current_time = $datetime[1];
    return view('vendor-expenses.unpaidform', ['unpaid' => $unpaid, 'current_date' => $current_date, 'current_time' => $current_time]);
  }
  public function unpaid_store(Request $request)
  {
    // dd($request->all());
    $user_id = Auth::user()->id;
    $input = $request->all();
    $extra_amt = 0;
    $unpaid_amt = 0;
    $input['current_date'] = $request->current_date . ' ' . $request->time;
    $expenses_date = ExpensesUnpaidDate::create($input);
    // wallet minus
    $user = Vendor::find($request->user_id);
    $minus = abs($user->advance_amt - $request->unpaid_amt);
    $user['advance_amt'] = $minus;
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
    return redirect()->route('vendor-expenses-unpaid-history')
      ->with('expenses-popup', 'Vendor Unpaid Amount Updated Successfully');
  }
  public function advance_expenses(Request $request)
  {
    $paginate = $request->paginate ?? 15;

    $users = Vendor::when(request('search'), function ($query, $search) {
      $query->where('name', 'like', "%$search%")
        ->orWhere('phone', 'like', "%$search%")
        ->orWhere('address', 'like', "%$search%")
        ->orWhere('advance_amt', 'like', "%$search%");
    })->latest()->paginate($paginate);
    return view('vendor-expenses.advanceexpense', ['users' => $users]);
  }
  public function advance_form($id)
  {
    $labour = Vendor::find($id);
    $project = Expenses::where('vendor_id', $id)->where(function ($query) {
      $query->where('extra_amt', '>', 0)
        ->orWhere('unpaid_amt', '>', 0);
    })->leftjoin('project_details', 'project_details.id', '=', 'expenses.project_id')->select('project_details.*')->groupBy('project_details.id')->get();
    return view('vendor-expenses.advanceform', ['labour' => $labour, 'project' => $project]);
  }
  public function advance_store(Request $request)
  {
    // dd($request->all());
    $project = Expenses::where(['vendor_id' => $request->labour_id, 'project_id' => $request->project_id])->get();
    $labour = Vendor::where('id', $request->labour_id)->first();
    $labour['advance_amt'] = abs($labour->advance_amt - $request->extra_amt);
    //  $labour['date'] = $request->current_date . ' ' . $request->time;

    $subamount = $request->extra_amt;
    // dd($project);
    if ($request->gender == 2) {
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
      $project_advance = Expenses::where(['vendor_id' => $request->labour_id])->get();
      // dd($project_advance);
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
    }
    if ($request->gender == 1) {
      $project_advance = Expenses::where(['vendor_id' => $request->labour_id, 'project_id' => $request->project_id])->get();
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
    }
    $labour->update();
    return redirect()->route('vendor-expenses-advance-history')->with('popup', 'open');
  }
  public function vendor_project_amount(Request $request)
  {
    $amount =  Expenses::where('vendor_id', $request->labour_id)->where('project_id', $request->project_id)->get();
    // dd($amount);
    $advance = $amount->sum('extra_amt');
    $unpaid_amt = $amount->sum('unpaid_amt');
    // dd($amount);
    return response()->json(['advance' => $advance, 'unpaid_amt' => $unpaid_amt]);
  }
  public function vendor_expense_pdf(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::whereNotNull('expenses.vendor_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')
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
        'l.name as vendor_name',
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
      ->when(request('main_category_id'), function($query,$main_category_id){
        $query->where('expenses.main_category_id',$main_category_id);
      })
      ->when($category_filter, function ($query, $category_filter) {
        $query->where('expenses.category_id', $category_filter);
      })
      ->when($project_filter, function ($query, $project_filter) {
        $query->where('expenses.project_id', $project_filter);
        //dd($expenses);exit;
      })
      ->when($user_filter, function ($query, $user_filter) {
        $query->where('expenses.vendor_id', $user_filter);
      })->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
            ->orWhere('main_category.name','like',"%$search%")
            ->orWhere('expenses.description', 'like', "%$search%");
        });
      });

    //dd($expenses);
    if ($from != '' && $to != '') {
      $expenses = $expenses->orderBy('expenses.current_date', 'desc')->get();
    } else {
      $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
    }
    // $expenses = $expenses->orderBy('expenses.id', 'desc')->get();
    $pdf = PDF::loadView('vendor-expenses.vendorpdf', compact('expenses'));

    return $pdf->download('vendor-expenses.pdf');
  }
  public function vendor_expense_export(Request $request)
  {
    //  dd($request->all());
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $search = $request->search;
    $main_category = $request->main_category_id;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }





    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new VendorExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role, $search, $main_category)), 'vendor-expenses.xlsx');
  }
  public function vendor_delete_expense_pdf(Request $request)
  {
    
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::join('category', 'category.id', '=', 'expenses.category_id')
      ->whereNotNull('expenses.vendor_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.labour_id')->leftJoin('project_details', function ($join) {
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
      ->when($from,function($query,$from){
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
        $query->where('expenses.vendor_id', $user_id);
      })
      ->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
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
//dd($expenses);
    // $expenses = $expenses->onlyTrashed()->orderBy('expenses.id', 'desc')->get();
    $pdf = PDF::loadView('vendor-expenses.deletepdf', compact('expenses'));

    return $pdf->download('vendor-delete-expenses.pdf');
  }
  public function vendor_delete_expense_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $main_category = $request->main_category_id;
    $search = $request->search;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    return Excel::download((new VendorDeleteExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role,$search,$main_category)), 'vendor-delete-expenses.xlsx');
  }
  public function unpaid_expenses_export(Request $request)
  {
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $search = $request->search;
    $main_category = $request->main_category_id;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }




    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();
    return Excel::download((new VendorUnpaidExpensesExport($category_filter, $project_filter, $user_filter, $from, $to, $auth, $role,$search,$main_category)), 'vendor-unpaid-expenses.xlsx');
  }
  public function unpaid_expenses_pdf(Request $request)
  {
   $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;
    $search = $request->search;
    $from = null;
    $to = null;

    if (!empty($request->date_range)) {
      [$from, $to] = array_map('trim', explode('-', $request->date_range));

      $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
      $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }



    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')->where('users.id', $auth)->pluck('roles.id')->first();

    $expenses = Expenses::whereNotNull('expenses.vendor_id')->where('expenses.unpaid_amt', '!=', 0)->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l', 'l.id', '=', 'expenses.vendor_id')
      ->leftJoin('project_details', function ($join) {
        $join->on('project_details.id', 'expenses.project_id')
          ->where('expenses.project_id', '!=', null);
      })
      ->leftjoin('main_category','main_category.id','=','expenses.main_category_id')
      ->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0])
      ->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad', 'labour_ad.id', '=', 'expenses.is_advance')
      ->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last', 'l.name as vendor_name', 'labour_ad.first_name as labour_first', 'labour_ad.last_name as labour_last','main_category.name as main_category_name')
      ->when($from,function($query,$from){
        $query->wheredate('current_date', '>=', $from);
      })
      ->when($to,function($query,$to){
        $query->wheredate('current_date', '<=', $to);
      })
      ->when(request('main_category_id'),function($query,$main_category){
        $query->where('expenses.main_category_id',$main_category);
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
      ->when(request('search'), function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('category.name', 'like', "%$search%")
            ->orWhere('project_details.name', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(users_add.first_name, ' ', users_add.last_name)"), 'like', "%$search%")
            ->orWhere(DB::raw("CONCAT(labour_ad.first_name, ' ', labour_ad.last_name)"), 'like', "%$search%")
            ->orWhere('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('users_add.first_name', 'like', "%$search%")
            ->orWhere('users_add.last_name', 'like', "%$search%")
            ->orWhere('l.name', 'like', "%$search%")
            ->orWhere('labour_ad.first_name', 'like', "%$search%")
            ->orWhere('labour_ad.last_name', 'like', "%$search%")
            ->orWhere('expenses.amount', 'like', "%$search%")
            ->orWhere('expenses.paid_amt', 'like', "%$search%")
            ->orWhere('expenses.unpaid_amt', 'like', "%$search%")
            ->orWhere('expenses.extra_amt', 'like', "%$search%")
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
    $pdf = PDF::loadView('vendor-expenses.vendorpdf', compact('expenses'));

    return $pdf->download('vendor-unpaid-expenses.pdf');
  }
  public function vendor_insufficant(Request $request)
  {
    $vendor = Vendor::where('id', $request->vendor_id)->first();
    //  dd($vendor);
    $amount = $request->amount;
    $wal_amt = (int)$vendor->advance_amt;
    $response = true;
    if (($wal_amt >= 0) && ($amount <= $wal_amt)) {
      $response = false;
    }
    return response()->json($response);
  }
  public function vendor_history(Request $request, $id)
  {
    // dd($request->member_id);
    $paginate = $request->paginate;
    $vendor = Transfer::leftJoin('users', 'users.id', '=', 'transferdetails.user_id')
      ->leftJoin('vendor_details', 'vendor_details.id', '=', 'transferdetails.vendor_id')
      ->leftJoin('payment', 'payment.id', '=', 'transferdetails.payment_mode')
      ->where('transferdetails.vendor_id', '=', $id)
      ->select(
        'transferdetails.*',
        'vendor_details.name as name',
        'payment.name as payment_mode',
        'users.first_name',
        'users.last_name'
      )
      ->where('transferdetails.is_vendor', 1)
      ->when($request->from_date, function ($query, $from_date) {
        $query->whereDate('current_date', '>=', $from_date);
      })
      ->when($request->to_date, function ($query, $to_date) {
        $query->whereDate('current_date', '<=', $to_date);
      })
      ->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('users.first_name', 'like', "%$search%")
            ->orWhere('users.last_name', 'like', "%$search%")
            ->orWhere('transferdetails.amount', 'like', "%$search%")
            ->orWhere('payment.name', 'like', "%$search%")
            ->orWhere('transferdetails.description', 'like', "%$search%");
        });
      })->when($request->member_id, function ($query, $member_id) {
        $query->where('users.id', $member_id);
      });

  //  if (Auth::user()->hasRole('Admin')) {
      $vendor = $vendor->orderBy('transferdetails.id', 'DESC')
        ->paginate($paginate)->withQueryString();
    // } else {
    //   $vendor = $vendor->where('user_id', Auth::user()->id)
    //     ->orderBy('transferdetails.id', 'DESC')
    //     ->paginate($paginate)->withQueryString();
    // }
    $sum = $vendor->sum('amount');
    $user_list = User::latest()->get();
    return view('vendor-expenses.vendorhistory', ['vendor' => $vendor, 'user_list' => $user_list, 'sum' => $sum, 'id' => $id]);
  }
  public function withdraw(Request $request){
   $vendor_id = $request->id;
   $member = User::where('active_status',1)->get();
   $view = view('vendor-expenses.amount_reduction',['member' => $member,'vendor_id' => $vendor_id])->render();
   return response()->json($view);
  }
  public function withdraw_save(Request $request){
   // dd($request->all());
   $vendor = Vendor::where('id',$request->vendor_id)->first();
   if($request->amount <= $vendor->advance_amt){
     //dd('hiiiiii');
     $vendor->advance_amt = $vendor->advance_amt - $request->amount;
     $vendor->update();
     $user = User::where('id',$request->member_id)->first();
     $user->wallet = $user->wallet + $request->amount;
     $user->update();
      return redirect()->route('vendor-expenses-advance-history')->with('message', 'Amount is successfully reduce');
   }else{
    return redirect()->route('vendor-expenses-advance-history')->with('error_sweet', 'Insufficient amount');
   }
  }
}
