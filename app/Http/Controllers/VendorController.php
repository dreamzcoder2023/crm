<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
  public function index(Request $request)
  {

      $users = Vendor::latest()->get();

      return view('vendor_details.index',compact('users'));
  }
  public function create(Request $request){
    return view('vendor_details.create');
  }
  public function store(Request $request){
    $vendor = Vendor::create($request->all());
    return redirect()->route('vendor-index')->with('message','Vendor Details Created Successfully');
  }
  public function edit(Request $request)
  {
      $id = $request->id;
     $user = Vendor::where('id',$id)->first();
      return view('vendor_details.edit',["user"=>$user]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
     //dd($request->all());
      $input =$request->all();
      //dd($input);
      $user = Vendor::find($id);
      $user->update($input);

      // print_r($user);
      // exit;

      return redirect()->route('vendor-index')->with('message','Vendor details created successfully');

  }
  public function labourdelete(Request $request)
  {
     $user = Vendor::find($request->id);
     $user->delete();
     return redirect()->route('vendor-index')
     ->with('message','Vendor Deleted Successfully');
  }
}
