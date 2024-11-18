<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use App\Models\ClientDetails;
use App\Models\ProjectDetails;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB as FacadesDB;

class ClientDetailsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //return view('roles.index');
        $clients = ClientDetails::where('active_status',1)->where('delete_status',0)->orderBy('id','desc')->get();
        $project = ProjectDetails::where('active_status',1)->where('delete_status',0)->pluck('client_id')->toArray();
        $wallet = Wallet::where('active_status',1)->where('delete_status',0)->pluck('client_id')->toArray();
        return view('client.index',compact('clients','project','wallet'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $cat = ClientDetails::where(['active_status' => 1, 
                    'delete_status' => 0, 
                    'email' => $request->email])->first();
        if(!empty($cat)){
            return redirect()->route('client-index')
            ->with('msg','Client email id already existed');
        }
        else{
            ClientDetails::create($request->all());
            return redirect()->route('client-index')
            ->with('message','Client Details Stored Successfully');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $from_date = $request->from_date;
       $to_date = $request->to_date;
       $from = (isset($request->from_date) && $request->from_date != 'undefined') ? ($request->from_date.' '.'00:00:00') : '';
       $to_date1 = (isset($request->to_date) && $request->to_date != 'undefined') ? ($request->to_date.' '.'23:59:59') : '';
        $projects = ProjectDetails::where(['client_id' => $request->id,'active_status' => 1, 'delete_status' => 0]);
        if($from != ''){
            $projects = $projects->whereDate('start_date','>=',$from);
        }
        if($to_date1 != ''){
            $projects = $projects->whereDate('end_date','<=',$to_date1);
        }
        $projects = $projects->get();
       // dd($projects);
      $sum = $projects->sum('advance_amt');
      $total = $projects->sum('total_amt');
      $remaining = $projects->sum('profit');
        //->get();
        return view('client.show',['client_id'=>$request->id,'projects' =>$projects,'from_date' => $from_date,'to_date' => $to_date,'sum' => $sum, 'total' => $total,'remaining' => $remaining]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = ClientDetails::where('id',$id)->first();
        return view('client.edit',["client"=>$client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cat = ClientDetails::where(['active_status' => 1, 
                    'delete_status' => 0, 
                    'email' => $request->email])->where('id',"!=",$id)->first();
        if(!empty($cat)){
            return redirect()->route('client-index')
            ->with('msg','Client email id already existed');
        }
        else{
        $input =$request->all();
        $client = ClientDetails::find($id);
        $client->update($input);
    
        return redirect()->route('client-index')
                        ->with('message','Client Details Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function clientdelete(Request $request)
    {
       $client = ClientDetails::find($request->id);
       $client['active_status'] = 0;
       $client['delete_status'] = 1;
       $client->update();
       return redirect()->route('client-index')
       ->with('message','Client Details Deleted Successfully');
    }
}
