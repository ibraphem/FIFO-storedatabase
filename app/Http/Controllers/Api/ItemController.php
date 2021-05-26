<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Item;
use Illuminate\Http\Request;
use DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Item::orderBy('item_name','ASC')->get();
        return $result;
    }

    public function test2()
    {
      /*  $result = Item::with('purchase')
      //  ->groupBy('item_id')
       // ->selectRaw('item_id, sum(purchase_price * remainder) as price, sum(remainder) as quantity')
      //  ->select('item_id')
     //   ->where('company', '=', 'Landover')
       // ->orderBy('item_id','DESC')->get(); 
       ->get(); */

     $result = Item::query()
    ->with(array('purchase' => function($query) {
        $query->select('item_id', DB::raw('sum(purchase_price * remainder) as price'), DB::raw('sum(remainder) as quantity'))
        ->where('company', '=', 'Landover')
        ->groupBy('item_id');
    }))
    ->with('category')
  
    ->get();

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
        $item = new Item();
        $item->item_name = $request->item_name;
        $item->category_id = $request->category_id;
        $item->reorder = $request->reorder;
        $item->save();
        $result = Item::orderBy('item_name','ASC')->get();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);
        $item->item_name = $request->item_name;
        $item->category_id = $request->category_id;
        $item->reorder = $request->reorder;
        $item->save();
        $result = Item::orderBy('item_name','ASC')->get();
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        //
    }
}
