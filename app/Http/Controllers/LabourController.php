<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Labour;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabourController extends Controller
{
  public function index(Request $request)
  {
      //return view('roles.index');
      $id = Auth::user()->id;
      $users = Labour::latest()->get();

      return view('labour.index',compact('users'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {

      return view('labour.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //dd($request->all());
      $input = $request->all();
     // dd($input);
      if ($image =$request->file('image')) {

          $destinationPath = 'public/images/';
              $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
              $image->move($destinationPath, $profileImage);


          $input['government_image'] = $profileImage;

      }
      //print_r($request->file('image'));exit;

          $user = Labour::create($input);
          $salary['user_id'] = $user->id;
          $salary['salary'] = $user->salary;
         Salary::create($salary);
         // dd($user);
          return redirect()->route('labour-index')->with('message','Labour details created successfully');

  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request)
  {

  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Request $request)
  {
      $id = $request->id;
     $user = Labour::where('id',$id)->first();
      return view('labour.edit',["user"=>$user]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
     //dd($request->all());
      $input =$request->all();
      //dd($input);
      $user = Labour::find($id);
      if ($image =$request->file('image')) {

          $destinationPath = 'public/images/';
              $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
              $image->move($destinationPath, $profileImage);


          $input['government_image'] = $profileImage;

      }
      // print_r($user);
      // exit;
      $user->update($input);
      $salary = Salary::where('user_id',$id)->orderBy('id','desc')->first();
      if($salary->salary != $request->salary){
        $input['user_id'] = $id;
        $input['salary'] = $request->salary;
        Salary::create($input);
      }

      return redirect()->route('labour-index')->with('message','Labour details created successfully');

  }
  public function jobupdate(Request $request, string $id)
  {

      // $input =$request->all();
      // //print_r($input);exit;
      //  $user = User::find($id);
      // // print_r($request->file('government_image'));
      //  if ($image =$request->file('government_image')) {

      //     $destinationPath = 'public/images/';
      //         $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
      //         $image->move($destinationPath, $profileImage);


      //     $input['government_image'] = $profileImage;

      // }

      // $user->update($input);

      // return redirect()->route('user-index')
      //                 ->with('message','User updated successfully');

  }

  /**
   * Remove the specified resource from storage.
   */
  public function labourdelete(Request $request)
  {
     $user = Labour::find($request->id);
     $user->delete();
     return redirect()->route('labour-index')
     ->with('message','Labour Deleted Successfully');
  }
  public function phoneunique(Request $request){

      // $response = false;

      // $user = User::where(['phone' => $request->phone])->first();
      // if(!empty($user)){
      //     $response = true;
      //     return response()->json($response);
      // }
      // return response()->json($response);
  }
  public function convert_hrs($value){
//       $day = floor($value / 86400);
//       $hours = floor(($value -($day*86400)) / 3600);
//       $minutes = floor(($value / 60) % 60);
//       $seconds = $value % 60;
// //"$day:$hours:$minutes:$seconds";
//       return $hours.' hours '.$minutes.' minutes ';
  }
}
