<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class TransferController extends Controller
{
  public function index(Request $request)
  {
    $auth = Auth::user()->id;
   
    $from = null;
    $to = null;
    
    if (!empty($request->date_range)) {
        [$from, $to] = array_map('trim', explode('-', $request->date_range));
    
        $from = Carbon::createFromFormat('m/d/Y', $from)->format('Y-m-d');
        $to = Carbon::createFromFormat('m/d/Y', $to)->format('Y-m-d');
    }
    
    $paginate = $request->paginate ?? 10;

    if (Auth::user()->hasRole('Admin')) {
      $transfers = Transfer::leftjoin('users', 'users.id', '=', 'transferdetails.member_id')->leftjoin('users as user_table', 'user_table.id', '=', 'transferdetails.user_id')->leftjoin('payment', 'payment.id', '=', 'transferdetails.payment_mode')->where('transferdetails.is_vendor',0)->select('transferdetails.*', 'users.first_name', 'users.last_name', 'user_table.first_name as firstname', 'user_table.last_name as lastname', 'payment.name as payment_name')
      ->when($from, function($query, $from){
        $query->whereDate('transferdetails.current_date','>=',$from);
      })
      ->when($to, function($query, $to){
        $query->whereDate('transferdetails.current_date','<=',$to);
      })
      ->latest()->paginate($paginate);
    } else {
      $transfers = Transfer::leftjoin('users', 'users.id', '=', 'transferdetails.member_id')->leftjoin('users as to', 'to.id', '=', 'transferdetails.user_id')->where('users.id', $auth)->orWhere('to.id', $auth)->select('transferdetails.*', 'to.id as from_id', 'users.first_name', 'users.last_name', 'to.first_name as firstname', 'to.last_name as lastname');
      if ($from != '' && $to_date != '') {
        $transfers = $transfers->whereBetween('current_date', [$from, $to_date]);
      }
      $transfers = $transfers->orderBy('transferdetails.id', 'desc')->get();
    }
    $user = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['users.active_status' => 1, 'users.delete_status' => 0])->select('users.*', 'roles.name')->get();
    $sum = $transfers->sum('amount');
    return view('transfer.index', ['transfers' => $transfers, 'role' => $role, 'user' => $user, 'user_filter' => $user_filter, 'from_date' => $request->from_date, 'to_date1' => $request->to_date, 'sum' => $sum]);
  }

  public function create(Request $request)
  {
    $id = Auth::user()->id;
    $member = User::where(['active_status' => 1, 'delete_status' => 0])->where('id', '!=', $id)->select('*')->get();
    $payment = Payment::where(['active_status' => 1, 'delete_status' => 0])->get();
    return view('transfer.create', ['member' => $member, 'payment' => $payment]);
    //return response()->json($view);
  }
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //   dd($request->all());
    $user_id = Auth::user()->id;
    $input = $request->all();
    if ($request->user_type == 0) {

      $input['user_id'] = $user_id;
      $input['member_id'] = $request->member_id;
      $input['is_vendor'] = $request->user_type;
      $input['current_date'] = $request->current_date . ' ' . $request->time;
      $transfer = Transfer::create($input);
      $project = User::find($user_id);
      $minus = $project->wallet - $request->amount;
      $project['wallet'] = $minus;
      $project->update();
      $user = User::find($request->member_id);
      $add = $user->wallet + $request->amount;
      $user['wallet'] = $add;
      $user->update();
      return redirect()->route('transfer-history')
        ->with('success', 'Transfer Successfully');
    } else {

      $input['user_id'] = $user_id;
      $input['vendor_id'] = $request->member_id;
      $input['is_vendor'] = $request->user_type;
      $input['current_date'] = $request->current_date . ' ' . $request->time;
      $transfer = Transfer::create($input);
      $project = User::find($user_id);
      $minus = $project->wallet - $request->amount;
      $project['wallet'] = $minus;
      $project->update();
      $user = Vendor::find($request->member_id);
      $add = $user->advance_amt + $request->amount;
      $user['advance_amt'] = $add;
      $user->update();
      return redirect()->route('transfer.vendor.history')
        ->with('success', 'Transfer Successfully');
    }
  }
  public function insufficientamt(Request $request)
  {

    $wallet = Auth::user()->wallet;
    // $wallet = User::where('id', $request->user_id)->first();
    $amount = $request->amount;
    $wal_amt = (int)$wallet;
    $response = true;
    if (($wal_amt >= 0) && ($amount <= $wallet)) {
      $response = false;
    }
    return response()->json($response);
  }
  public function userDetail(Request $request)
  {
    $id = $request->id;
    if ($id == 0) {
      $user =  User::where(['active_status' => 1, 'delete_status' => 0])->where('id', '!=', Auth::user()->id)->select('*')->get();
    } else {
      $user = Vendor::latest()->get();
    }
    return response()->json($user);
  }
  public function vendor_history(Request $request)
  {
    $paginate = $request->paginate??10;
    $vendor = Transfer::leftJoin('users', 'users.id', '=', 'transferdetails.user_id')
    ->leftJoin('vendor_details', 'vendor_details.id', '=', 'transferdetails.vendor_id')
    ->leftJoin('payment', 'payment.id', '=', 'transferdetails.payment_mode')
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
    })->when($request->member_id, function($query, $member_id){
      $query->where('vendor_details.id',$member_id);
    });

    if (Auth::user()->hasRole('Admin')) {
      $vendor = $vendor->orderBy('transferdetails.id','DESC')
      ->paginate($paginate)->withQueryString();
    } else {
      $vendor = $vendor->where('transferdetails.is_vendor', 1)
      ->where('user_id', Auth::user()->id)
      ->orderBy('transferdetails.id', 'DESC')
      ->paginate($paginate)->withQueryString();
    }
    $sum = $vendor->sum('amount');
    $vendor_list = Vendor::latest()->get();
    return view('transfer.vendor_history', ['vendor' => $vendor, 'vendor_list' => $vendor_list, 'sum' => $sum]);
  }
}
