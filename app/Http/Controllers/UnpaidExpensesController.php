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
use Illuminate\Support\Facades\DB;

class UnpaidExpensesController extends Controller
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

      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';


    // print_r($from);
    // print_r($to_date);
    // exit;



      $auth = Auth::user()->id;
      $role = DB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->join('users','users.id','=','model_has_roles.model_id')->where('users.id',$auth)->pluck('roles.id')->first();

        $expenses = Expenses::where('expenses.unpaid_amt','!=',0)->join('category','category.id','=','expenses.category_id')
        ->leftJoin('project_details', function ($join){
            $join->on('project_details.id', 'expenses.project_id')
                ->where('expenses.project_id', '!=', null);
        });


        $expenses = $expenses->leftjoin('payment','payment.id','=','expenses.payment_mode')
        ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
        if($role != 1){
          $expenses = $expenses->leftjoin('users','users.id','=','expenses.user_id')->where('users.id',$auth);
          $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name');
    }
    else{
      $expenses = $expenses->leftjoin('users','users.id','=','expenses.editedBy')->leftjoin('users as users_add','users_add.id','=','expenses.user_id');
      $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name','users_add.first_name as first','users_add.last_name as last');
    }
        if($from != '' ){
          $expenses = $expenses->wheredate('current_date', '>=',$from);
        //   ->toSql();
        //  // $bindings = $expenses->getBindings();
        //   print_r($expenses);
        //  exit;

        }
        if($to_date !=''){
          $expenses = $expenses->wheredate('current_date', '<=',$to_date);
        }
        if($category_filter != 'undefined' && $category_filter != ''){
          $expenses = $expenses->where('expenses.category_id',$category_filter);
        }
        if($project_filter != 'undefined' && $project_filter != ''){
          $expenses = $expenses->where('expenses.project_id',$project_filter);
          //dd($expenses);exit;
        }
        if($user_filter != 'undefined' && $user_filter != ''){
          $expenses = $expenses->where('expenses.user_id',$user_filter);
        }

        //dd($expenses);
if($request->amount != '' && $request->amount != 'undefined'){
       $expenses = $expenses->orderBy('expenses.amount',$request->amount)->get();
}

  $expenses = $expenses->orderBy('expenses.id','desc')->get();



        $category = Category::where(['active_status' => 1,'delete_status' => 0])->get();
        $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
        $user = User::join('model_has_roles','model_has_roles.model_id','=','users.id')->join('roles','roles.id','=','model_has_roles.role_id')->where(['users.active_status' => 1 ,'users.delete_status' => 0])->select('users.*','roles.name')->get();

      $sum = $expenses->sum('amount');
      $paid_amt = $expenses->sum('paid_amt');
      $unpaid_amt = $expenses->sum('unpaid_amt');
      $advanced_amt = $expenses->sum('extra_amt');

        return view('unpaid_expenses.index',['expenses' => $expenses,'category' => $category,'category_filter' => $category_filter,'from_date' => $request->from_date ,'to_date1' => $request->to_date,'project' =>$project , 'user' =>$user,'project_filter' =>$project_filter, 'user_filter' => $user_filter,'sum' =>$sum,'paid_amt' => $paid_amt,'unpaid_amt' =>$unpaid_amt,'amount' =>$request->amount,'advanced_amt' => $advanced_amt]);

    }
    /**
     * Store a newly created resource in storage.
     */
    public function unpaid_create(Request $request){
      $unpaid = Expenses::where('id',$request->id)->first();
      $datetime = explode(' ',$unpaid->current_date);
      $current_date = $datetime[0];
      $current_time = $datetime[1];
     return view('unpaid_expenses.form',['unpaid' => $unpaid,'current_date' => $current_date,'current_time' => $current_time]);
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
      $minus = $user->wallet - $request->unpaid_amt;
      $user['wallet'] = $minus;
      $user->update();
      // expense minus
      $expenses = Expenses::find($request->expense_id);
      $minus = $expenses->paid_amt + $request->unpaid_amt;

      $unpaid = $expenses->unpaid_amt - $request->unpaid_amt;
      $expenses['paid_amt'] = $minus;
      if($request->amount <= $request->paid_amt){
        $extra_amt = $minus - $expenses->amount;

      }
      if($request->paid_amt < $request->amount){
        $unpaid_amt = $request->amount - $request->paid_amt;
      }
      $expenses['extra_amt'] = $extra_amt;
      $expenses['unpaid_amt'] = $unpaid_amt;
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
     ->with('message','Expenses Deleted Successfully');
  }
  public function image_delete(Request $request)
  {
     $image = Expenses::find($request->id);
     $image['image'] = "";
     $image->update();
     return redirect()->route('expenses-history')
     ->with('message','Image Deleted Successfully');
  }
  public function unpaid_expense_export(Request $request){
    $category_filter = $request->category_id;
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;

      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      $auth = Auth::user()->id;
      $role = DB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->join('users','users.id','=','model_has_roles.model_id')->where('users.id',$auth)->pluck('roles.id')->first();

    return Excel::download((new UnpaidExpensesExport($category_filter , $project_filter, $user_filter, $from, $to_date,$auth,$role)), 'unpaid-expenses.xlsx');

  }
  public function unpaid_expense_pdf(Request $request){
    $category_filter = $request->category_id;
    $project_filter = $request->project_id;
    $user_filter = $request->user_id;


    $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
    $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';


    $auth = Auth::user()->id;
    $role = DB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->join('users','users.id','=','model_has_roles.model_id')->where('users.id',$auth)->pluck('roles.id')->first();

      $expenses = Expenses::where('expenses.unpaid_amt','!=',0)->join('category','category.id','=','expenses.category_id')
      ->leftJoin('project_details', function ($join){
          $join->on('project_details.id', 'expenses.project_id')
              ->where('expenses.project_id', '!=', null);
      });


      $expenses = $expenses->leftjoin('payment','payment.id','=','expenses.payment_mode')
      ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
      if($role != 1){
        $expenses = $expenses->leftjoin('users','users.id','=','expenses.user_id')->where('users.id',$auth);
        $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name');
  }
  else{
    $expenses = $expenses->leftjoin('users','users.id','=','expenses.editedBy')->leftjoin('users as users_add','users_add.id','=','expenses.user_id');
    $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name','users_add.first_name as first','users_add.last_name as last');
  }
      if($from != '' && $to_date != ''){
        $expenses = $expenses->whereBetween('current_date', [$from,$to_date]);


      }
      if($category_filter != 'undefined' && $category_filter != ''){
        $expenses = $expenses->where('expenses.category_id',$category_filter);
      }
      if($project_filter != 'undefined' && $project_filter != ''){
        $expenses = $expenses->where('expenses.project_id',$project_filter);
        //dd($expenses);exit;
      }
      if($user_filter != 'undefined' && $user_filter != ''){
        $expenses = $expenses->where('expenses.user_id',$user_filter);
      }


            $expenses = $expenses->orderBy('expenses.id','desc')->get();
            $pdf = PDF::loadView('unpaid_expenses.unpaidpdf', compact('expenses'));

            return $pdf->download('unpaid-expenses.pdf');

  }
}
