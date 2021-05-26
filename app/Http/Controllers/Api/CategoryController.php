<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Category::orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->save();
        $result = Category::orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->name = $request->name;
        $category->save();
        $result = Category::orderBy('id','DESC')->get();
        return $result;
    }

    public function storereport($company,  $asat)
    {
        $store = Category::query()
        ->with(array('itemPurchase' => function($query) use($company, $asat) {
            $query->leftJoin('items as pItems', 'purchases.item_id', '=', 'pItems.id')->selectRaw('purchases.item_id, pItems.item_name as item_name, sum(purchases.purchase_price * purchases.remainder) as price, 
            sum(purchases.remainder) as quantity')
            ->where('company', '=', $company)
            ->where('purchase_date', '<=', $asat)
            ->groupBy('item_id', 'items.category_id', 'pItems.item_name');
        }))
        ->get();

        $grandTotal = DB::table('purchases')
        ->where('remainder', '>', 0)
        ->where('purchase_date', '<=', $asat)
        ->where('company', '=', $company)
        ->selectRaw('sum(remainder * purchase_price) as grandTotal')
        ->get();

        $result = array('store' => $store, 'grandTotal' => $grandTotal);

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //
    }
}
