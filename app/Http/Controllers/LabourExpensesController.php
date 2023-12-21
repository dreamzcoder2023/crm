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
use Illuminate\Support\Carbon;

class LabourExpensesController extends Controller
{
  public function index(Request $request)
  {
    $currentYear = Carbon::now()->year;
    $startOfWeek = Carbon::createFromDate($currentYear, 1, 1)->startOfWeek();
    $endOfWeek = Carbon::createFromDate($currentYear, 12, 31)->endOfWeek();
    $weekStartDates = [];
    $start_labour_date = [];
    $currentDate = $startOfWeek->copy();

    while ($currentDate->lte($endOfWeek)) {
        $weekStartDates[] = $currentDate->copy();
        $currentDate->addWeek();
    }

    foreach ($weekStartDates as $weekStartDate) {
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
          ->select('p.*')
          ->leftJoin('project_details as p', 'p.id', '=', 'w.project_id')
          ->whereBetween('w.current_date', [$weekStartDate->format('Y-m-d'), $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d')])
          ->groupBy('project_id')
          ->get();

      if (!$records->isEmpty()) {
          $start_labour_date[] = ['week_data' => ['records' => $records, 'project' => $project]];
      }
  }

    dd($start_labour_date);
    //  // $labour = Expenses::whereBetween('current_date', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SATURDAY)])->get();

    $view = view('labour-expenses.index',['start_labour_date' => $start_labour_date])->render();
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
    $labour = Labour::find($request->labour_id);
    $labour['advance_amt'] = $labour->advance_amt + $extra_amt;
    $labour->update();
    return redirect()->route('expenses-history')
      ->with('expenses-popup', 'open');
  }
  public function labour_salary(Request $request)
  {
    $labour = Labour::where('id', $request->id)->select('salary', 'advance_amt')->first();
    return response()->json($labour);
  }
}
