<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expenses;
use App\Models\MainCategory;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index(Request $request){
        $expenses = [1 => 'Expenses', 2=>'Labour Expenses',3 =>'Vendor Expenses'];
        $images = Expenses::leftjoin('category','category.id','=','expenses.category_id')->where('expenses.main_category_id',$request->main_category_id)->where('expenses.category_id',$request->category_id)->whereNull('expenses.deleted_at')->select('expenses.*','category.name as category_name')->get();
        return view('expenses-image.index',['expenses' => $expenses,'images' => $images]);
        // $maincategory = MainCategory::
    }
    public function fetchmaincategory(Request $request){
        $maincategory = MainCategory::where('expenses_id',$request->expenses_id)->where('status',1)->get();
        return response()->json($maincategory);
    }
    public function fetchcategory(Request $request){
        $maincategory = Category::where('main_category_id',$request->main_category_id)->where(['active_status'=> 1,'delete_status' => 0])->get();
        return response()->json($maincategory);
    }
}
