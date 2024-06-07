<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Expenses;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Category;
use App\Models\ExpensesUnpaidDate;
use App\Models\ProjectDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //return view('roles.index');
        $id = Auth::user()->id;
        $users = User::where('active_status',1)->where('delete_status',0)->where('id','!=',$id)->orderBy('id','desc')->get();
        $unpaid = Expenses::all();
        $role = Role::join('model_has_roles','model_has_roles.role_id','=','roles.id')->where('roles.name','Admin')->select('model_has_roles.model_id')->first();
        //dd($role);
        return view('user.index',compact('users','unpaid','role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = Role::all();
        return view('user.create',['role'=>$role]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['confirm_password'] = Hash::make($request->confirm_password);
       // dd($input);
        if ($image =$request->file('image')) {

            $destinationPath = public_Path('images'); 'public/images/';
                $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $profileImage);


            $input['image'] = $profileImage;

        }
        //print_r($request->file('image'));exit;
            $user = User::create($input);
            $user->assignRole($request->input('roles'));
            return redirect()->route('user-edit',['id' => $user->id,'tab'=> 'job-info']);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = User::join('model_has_roles','model_has_roles.model_id','=','users.id')->join('roles','roles.id','=','model_has_roles.role_id')->where('users.id',$request->id)->select('users.*','roles.name as role_name')->first();
        //print_r($user);exit;
       $time =  Attendance::where('user_id',$request->id)
       ->where('created_at', '>', now()->subDays(30)->endOfDay())
       ->sum(FacadesDB::raw('TIMESTAMPDIFF(SECOND, created_at, updated_at)'));
       $hours = $this->convert_hrs($time);
       //dd($hours);
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
       $role = FacadesDB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->join('users','users.id','=','model_has_roles.model_id')->where('users.id',$auth)->pluck('roles.id')->first();

         $expenses = Expenses::where('expenses.user_id',$request->id)->join('category','category.id','=','expenses.category_id')
         ->leftJoin('project_details', function ($join){
             $join->on('project_details.id', 'expenses.project_id')
                 ->where('expenses.project_id', '!=', null);
         });


         $expenses = $expenses->leftjoin('payment','payment.id','=','expenses.payment_mode')
         ->where(['category.active_status' => 1, 'category.delete_status' => 0]);
         if($role != 1){
           $expenses = $expenses->leftjoin('users','users.id','=','expenses.user_id');
           $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name');
     }
     else{
       $expenses = $expenses->leftjoin('users','users.id','=','expenses.editedBy')->leftjoin('users as users_add','users_add.id','=','expenses.user_id');
       $expenses= $expenses->select('expenses.*','category.name as category_name','project_details.name as project_name','payment.name as payment_name','users.first_name','users.last_name','users_add.first_name as first','users_add.last_name as last');
     }
         if($from != '' && $to_date != ''){
           $expenses = $expenses->whereBetween('current_date', [$from,$to_date]);
         //   ->toSql();
         //  // $bindings = $expenses->getBindings();
         //   print_r($expenses);
         //  exit;

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


         $unpaid_date = ExpensesUnpaidDate::select('expense_id','updated_at')->orderBy('id','desc')->first();
         $category = Category::where(['active_status' => 1,'delete_status' => 0])->get();
         $project = ProjectDetails::where(['active_status' => 1, 'delete_status' => 0])->get();
         $user1 = User::join('model_has_roles','model_has_roles.model_id','=','users.id')->join('roles','roles.id','=','model_has_roles.role_id')->where(['users.active_status' => 1 ,'users.delete_status' => 0])->select('users.*','roles.name')->get();

       $sum = $expenses->sum('amount');
       $paid_amt = $expenses->sum('paid_amt');
       $unpaid_amt = $expenses->sum('unpaid_amt');
       $attendance = Attendance::where('user_id',$request->id)->get();


      // dd($expenses);
        return view('user.view',['user' =>$user,'hours' => $hours,'expenses' => $expenses,'unpaid_date' =>$unpaid_date,'category' => $category,'category_filter' => $category_filter,'from_date' => $request->from_date ,'to_date1' => $request->to_date,'project' =>$project , 'user1' =>$user1,'project_filter' =>$project_filter, 'user_filter' => $user_filter,'sum' =>$sum,'paid_amt' => $paid_amt,'unpaid_amt' =>$unpaid_amt,'amount' =>$request->amount,'attendance' => $attendance]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        $tab = 'general-info';
        if($request->tab != ''){
            $tab = $request->tab;
        }
        $user = User::where('id',$id)->first();
        $roles = Role::all();
        $modeluser = FacadesDB::table('model_has_roles')->where('model_id',$id)->first();
        return view('user.edit',["user"=>$user,'role' =>$roles,'modeluser' => $modeluser,'tab' => $tab]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       //dd($request->all());
        $input =$request->all();
        //dd($input);
        $user = User::find($id);
        if ($image =$request->file('image')) {

            $destinationPath = public_Path('images'); 'public/images/';
                $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $profileImage);


            $input['image'] = $profileImage;

        }
        // print_r($user);
        // exit;
        $user->update($input);
        $value = FacadesDB::table('model_has_roles')->where('model_id',$id)->delete();

        $user->assignRole($request->input('roles'));

        return redirect()->route('user-edit',['id' => $request->id,'tab'=> 'job-info']);

    }
    public function jobupdate(Request $request, string $id)
    {

        $input =$request->all();
        //print_r($input);exit;
         $user = User::find($id);
        // print_r($request->file('government_image'));
         if ($image =$request->file('government_image')) {

            $destinationPath = public_Path('images'); 'public/images/';
                $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $profileImage);


            $input['government_image'] = $profileImage;

        }

        $user->update($input);

        return redirect()->route('user-index')
                        ->with('message','User updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function userdelete(Request $request)
    {
       $user = User::find($request->id);
       $user['active_status'] = 0;
       $user['delete_status'] = 1;
       $user->update();
       return redirect()->route('user-index')
       ->with('message','User Deleted Successfully');
    }
    public function phoneunique(Request $request){

        $response = false;

        $user = User::where(['phone' => $request->phone])->first();
        if(!empty($user)){
            $response = true;
            return response()->json($response);
        }
        return response()->json($response);
    }
    public function convert_hrs($value){
        $day = floor($value / 86400);
        $hours = floor(($value -($day*86400)) / 3600);
        $minutes = floor(($value / 60) % 60);
        $seconds = $value % 60;
//"$day:$hours:$minutes:$seconds";
        return $hours.' hours '.$minutes.' minutes ';
    }
    public function profile_photo_upload(Request $request){
      //dd($request->all());
      $user = User::where('id',Auth::user()->id)->first();
      if ($image =$request->file('image')) {

        $destinationPath = public_Path('images');
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);


        $input['image'] = $profileImage;

    }
    $user->update($input);
    return response()->json($user);
    }
}
