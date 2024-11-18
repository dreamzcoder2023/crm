<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expenses;



class CategoryController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categorys = Category::orderBy('id','desc')->where(['active_status' => 1, 'delete_status' =>0])->latest()->get();
        $categorynot = Expenses::pluck('category_id')->toArray();
        //dd($categorynot);
        return view('category.index',compact('categorys','categorynot'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $cat = Category::where(['active_status' => 1, 
                    'delete_status' => 0, 
                    'name' => $request->name])->first();
        if(!empty($cat)){
            return redirect()->route('category-index')
            ->with('msg','Category already Created');
        }
    else{
        $category = Category::create($request->all());
        return redirect()->route('category-index')
        ->with('message','Category Created Successfully');
    }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::where('id',$id)->first();
        return view('category.edit',["category"=>$category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $cat = Category::where(['active_status' => 1, 
                    'delete_status' => 0, 
                    'name' => $request->name])->first();
        if(!empty($cat)){
            return redirect()->route('category-index')
            ->with('msg','Category already Created');
        }
        else{
            $category = Category::find($id);
            $category->name = $request->input('name');
            $category->save();
            return redirect()->route('category-index')
            ->with('message','Category Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function categorydelete(Request $request)
    {
        $category = Category::find($request->id);
        $category['active_status'] = 0;
        $category['delete_status'] = 1;
        $category->update();
        return redirect()->route('category-index')
        ->with('message','Category Deleted Successfully');
    }
}
