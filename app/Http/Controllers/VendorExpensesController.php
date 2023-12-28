<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\Payment;
use App\Models\ProjectDetails;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorExpensesController extends Controller
{
    public function index(Request $request){
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

      $expenses = Expenses::whereNotNull('expenses.labour_id')->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l','l.id','=','expenses.labour_id')
        ->leftJoin('project_details', function ($join) {
          $join->on('project_details.id', 'expenses.project_id')
            ->where('expenses.project_id', '!=', null);
        });


      $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
        ->where(['category.active_status' => 1, 'category.delete_status' => 0]);

        $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad','labour_ad.id','=','expenses.is_advance');
        $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last','l.name as vendor_name','labour_ad.first_name as labour_first','labour_ad.last_name as labour_last');
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

      return view('vendor-expenses.index', ['expenses' => $expenses, 'category' => $category, 'category_filter' => $category_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'project' => $project, 'user' => $user, 'project_filter' => $project_filter, 'user_filter' => $user_filter, 'sum' => $sum, 'paid_amt' => $paid_amt, 'unpaid_amt' => $unpaid_amt, 'amount' => $request->amount, 'advanced_amt' => $advanced_amt]);

    }
    public function create(Request $request){
      $category = Category::where(['active_status' => 1, 'delete_status' => 0])->get();
      $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
      $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
      $vendors = Vendor::get();
      return view('vendor-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment, 'vendors' => $vendors]);
    }
    public function store(Request $request){
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
      $minus = $project->wallet - $request->paid_amt;
      $project['wallet'] = $minus;
      $project->update();
      $labour = Vendor::find($request->vendor_id);
      $labour['advance_amt'] = $labour->advance_amt + $extra_amt;
      $labour->update();
      return redirect()->route('vendor-expenses-index')
        ->with('expenses-popup', 'Vendor Expenses Added Successfully');
    }
    public function vendor_salary(Request $request){
      $labour = Vendor::where('id', $request->id)->first();
      return response()->json($labour);
    }
}
