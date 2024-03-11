<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClientDetails;
use App\Models\Expenses;
use App\Models\Category;
use App\Models\Wallet;
use App\Models\ProjectDetails;
use App\Exports\ClientSummaryExport;
use App\Exports\PaymentIncomeExport;
use App\Exports\PaymentExpenseExport;
use App\Models\User;
use Excel;
use PDF;

class ReportsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function client_summary(Request $request)
    {
        $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

        $clients = ProjectDetails::leftjoin('wallet','wallet.project_id','=','project_details.id')->leftjoin('stage','stage.id','=','wallet.stage_id')->leftjoin('clientdetails','wallet.client_id','=','clientdetails.id')->leftjoin('payment','payment.id','=','wallet.payment_mode')->select('project_details.*','stage.name as stage_name','wallet.amount','payment.name as payment','wallet.current_date as currentdate','clientdetails.first_name','clientdetails.last_name');
        if($from != '' && $to_date != ''){
            $clients = $clients->whereBetween('wallet.current_date', [$from,$to_date]);
        }
        if($project_filter != 'undefined' && $project_filter != ''){
            $clients = $clients->where('wallet.project_id',$project_filter);
            //dd($clients);exit;
          }
          if($user_filter != 'undefined' && $user_filter != ''){
            $clients = $clients->where('wallet.client_id',$user_filter);
          }

        $clients = $clients->get();
       //dd($clients);
        $project = ProjectDetails::where('project_status',0)->get();
        $user = ClientDetails::where('active_status',1)->where('delete_status',0)->get();
        // dd($clients);
        // exit;
        return view('reports.clientsummary',compact('clients','user','from_date','to_date1','user_filter','project_filter','project'));
    }
    public function payment_summary(Request $request)
    {


        $expenses = ProjectDetails::where('project_details.delete_status',0)->leftjoin('wallet','wallet.project_id','=','project_details.id')->leftjoin('expenses','project_details.id', 'expenses.project_id')->whereNull('expenses.deleted_at')
                // ->where('expenses.project_id', '!=', null)
        // })
        ->selectRaw('project_details.id as projectid,project_details.name as project_name,project_details.advance_amt,SUM(expenses.amount) as paid_amt1,expenses.*')->groupBy('project_details.id')->get();
       //dd($expenses);
        return view('reports.paymentsummary',compact('expenses'));
    }
    public function payment_income(Request $request){
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      $project = Wallet::leftjoin('clientdetails','clientdetails.id','=','wallet.client_id')->leftjoin('payment','payment.id','=','wallet.payment_mode')?->leftjoin('stage','stage.id','=','wallet.stage_id')?->where('wallet.project_id',$request->id)->select('wallet.*','clientdetails.first_name','clientdetails.last_name','payment.name as payment_name','stage.name as stage_name');
      if($from != '' && $to_date != ''){
        $project = $project->whereBetween('wallet.current_date', [$from,$to_date]);
    }

      if($user_filter != 'undefined' && $user_filter != ''){
        $project = $project->where('wallet.client_id',$user_filter);
      }
      $project = $project->get();
      //dd($project);
    $user = ClientDetails::where('active_status',1)->where('delete_status',0)->get();
      return view('reports.paymentincome',['project' =>$project,'from_date' =>$from_date,'to_date1' =>$to_date1,'user' => $user,'user_filter'=>$user_filter,'id' => $request->id]);
    }
    public function payment_expenses(Request $request){
      $category_filter = $request->category_id;
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      $project = Expenses::leftjoin('users','users.id','=','expenses.user_id')->leftjoin('category','category.id','=','expenses.category_id')->leftjoin('payment','payment.id','=','expenses.payment_mode')->where('expenses.project_id',$request->id)->whereNull('expenses.deleted_at')->select('expenses.*','category.name as category_name','users.first_name','users.last_name','payment.name as payment_name');
      if($from != '' && $to_date != ''){
        $project = $project->whereBetween('wallet.current_date', [$from,$to_date]);
    }
    if($category_filter != 'undefined' && $category_filter != ''){
      $project = $project->where('expenses.category_id',$category_filter);
    }

      if($user_filter != 'undefined' && $user_filter != ''){
        $project = $project->where('expenses.user_id',$user_filter);
      }
      $project = $project->get();
      //dd($project);
      $category = Category::where('active_status',1)->where('delete_status',0)->get();
    $user = User::where('active_status',1)->where('delete_status',0)->get();
      return view('reports.paymentexpense',['project' => $project,'from_date' =>$from_date,'to_date1' =>$to_date1,'user' => $user,'user_filter'=>$user_filter,'id' => $request->id,'category_filter' => $category_filter,'category' =>$category]);
    }

    public function client_summary_export(Request $request){
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      return Excel::download((new ClientSummaryExport($project_filter, $user_filter, $from, $to_date)), 'clientsummary.xlsx');

    }
    public function client_summary_pdf(Request $request){
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

        $clients = ProjectDetails::leftjoin('wallet','wallet.project_id','=','project_details.id')->leftjoin('stage','stage.id','=','wallet.stage_id')->leftjoin('clientdetails','wallet.client_id','=','clientdetails.id')->leftjoin('payment','payment.id','=','wallet.payment_mode')->select('project_details.*','stage.name as stage_name','wallet.amount','payment.name as payment','wallet.current_date as currentdate','clientdetails.first_name','clientdetails.last_name');
        if($from != '' && $to_date != ''){
            $clients = $clients->whereBetween('wallet.current_date', [$from,$to_date]);
        }
        if($project_filter != 'undefined' && $project_filter != ''){
            $clients = $clients->where('wallet.project_id',$project_filter);
            //dd($clients);exit;
          }
          if($user_filter != 'undefined' && $user_filter != ''){
            $clients = $clients->where('wallet.client_id',$user_filter);
          }

        $clients = $clients->get();
        $pdf = PDF::loadView('reports.clientsummarypdf', compact('clients'));

        return $pdf->download('clientsummary.pdf');
    }
    public function payment_income_export(Request $request){

      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';
      return Excel::download((new PaymentIncomeExport($user_filter, $from, $to_date,$request->id)), 'payment-income.xlsx');
    }
    public function payment_income_pdf(Request $request){
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      $project = Wallet::leftjoin('clientdetails','clientdetails.id','=','wallet.client_id')->leftjoin('payment','payment.id','=','wallet.payment_mode')?->leftjoin('stage','stage.id','=','wallet.stage_id')?->where('wallet.project_id',$request->id)->select('wallet.*','clientdetails.first_name','clientdetails.last_name','payment.name as payment_name','stage.name as stage_name');
      if($from != '' && $to_date != ''){
        $project = $project->whereBetween('wallet.current_date', [$from,$to_date]);
    }

      if($user_filter != 'undefined' && $user_filter != ''){
        $project = $project->where('wallet.client_id',$user_filter);
      }
      $project = $project->get();
      //dd($project);
      $pdf = PDF::loadView('reports.paymentincomepdf', compact('project'));

        return $pdf->download('paymentincome.pdf');

    }
    public function payment_expense_export(Request $request){
      $category_filter = $request->category_id;
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';
      return Excel::download((new PaymentExpenseExport($user_filter, $from, $to_date,$request->id,$category_filter)), 'payment-expense.xlsx');

    }
    public function payment_expense_pdf(Request $request){

      $category_filter = $request->category_id;
      $project_filter = $request->project_id;
      $user_filter = $request->user_id;
      $from_date = $request->from_date;
      $to_date1 = $request->to_date;
      $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
      $to_date = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';

      $project = Expenses::leftjoin('users','users.id','=','expenses.user_id')->leftjoin('category','category.id','=','expenses.category_id')->leftjoin('payment','payment.id','=','expenses.payment_mode')->where('expenses.project_id',$request->id)->select('expenses.*','category.name as category_name','users.first_name','users.last_name','payment.name as payment_name');
      if($from != '' && $to_date != ''){
        $project = $project->whereBetween('wallet.current_date', [$from,$to_date]);
    }
    if($category_filter != 'undefined' && $category_filter != ''){
      $project = $project->where('expenses.category_id',$category_filter);
    }

      if($user_filter != 'undefined' && $user_filter != ''){
        $project = $project->where('expenses.user_id',$user_filter);
      }
      $project = $project->get();
      $pdf = PDF::loadView('reports.paymentexpensepdf', compact('project'));

        return $pdf->download('paymentexpense.pdf');
    }


}
