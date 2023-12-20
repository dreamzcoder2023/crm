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
$currentDate = $startOfWeek->copy();

while ($currentDate->lte($endOfWeek)) {
    $weekStartDates[] = $currentDate->copy();
    $currentDate->addWeek();
}

// Print or use the week start dates as needed
foreach ($weekStartDates as $weekStartDate) {
    echo $weekStartDate->format('Y-m-d') . ' - ' . $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d') . '<br>';
}
//dd($weekStartDates);

$labour = DB::table('expenses as w')
    ->select([
        DB::Raw('SUM(w.unpaid_amt) as Week_total'),
        DB::Raw('YEAR(w.current_date) as Year'),
        DB::Raw('WEEK(w.current_date) as Week'),
        DB::Raw('MIN(w.current_date) as Week_start_date'),
        DB::Raw('MAX(w.current_date) as Week_end_date'),
    ])
    ->whereBetween('w.current_date', [$startOfWeek, $endOfWeek])
    ->groupBy('Year', 'Week')
    ->orderBy('Year')
    ->orderBy('Week')
    ->get();
    dd($labour);

  //  // $labour = Expenses::whereBetween('current_date', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SATURDAY)])->get();
     dd($labour);
  exit;
   $view = view('labour-expenses.index')->render();
   return response()->json($view);
  }

  public function create(Request $request)
  {
    $category = Category::where(['active_status' => 1, 'delete_status' => 0,'name' => 'salary'])->first();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0, "project_status" => 0])->get();
    $labours = Labour::get();
    return view('labour-expenses.create', ['category' => $category, 'project' => $project, 'payment' => $payment,'labours' => $labours]);
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
  public function labour_salary(Request $request){
    $labour = Labour::where('id',$request->id)->select('salary','advance_amt')->first();
    return response()->json($labour);
  }
}
