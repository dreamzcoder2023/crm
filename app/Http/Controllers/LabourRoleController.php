<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Labour;
use App\Models\LabourRole;
use Illuminate\Http\Request;

class LabourRoleController extends Controller
{
  public function index(Request $request)
  {

      $users = LabourRole::latest()->get();

      return view('labourrole.index',compact('users'));
  }
  public function create(Request $request){
    return view('labourrole.create');
  }
  public function store(Request $request){
    $labour_role = LabourRole::create($request->all());
    return redirect()->route('labourrole-index')->with('message','Labour Role Created Successfully');
  }
  public function edit(Request $request)
  {
      $id = $request->id;
     $user = LabourRole::where('id',$id)->first();
      return view('labourrole.edit',["user"=>$user]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
     //dd($request->all());
      $input =$request->all();
      //dd($input);
      $user = LabourRole::find($id);
      $user->update($input);
      $salary = Labour::where('labour_role',$user->id)->get();
      //dd($salary);
      foreach($salary as $labour){
        $labour['salary'] = $user->salary;
        $labour->update();
      }

      // print_r($user);
      // exit;

      return redirect()->route('labourrole-index')->with('message','Labour Role details created successfully');

  }
  public function labourdelete(Request $request)
  {
     $user = LabourRole::find($request->id);
     $user->delete();
     return redirect()->route('labourrole-index')
     ->with('message','Labour Role Deleted Successfully');
  }
}
