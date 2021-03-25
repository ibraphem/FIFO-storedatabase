<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Purchase;
use Illuminate\Http\Request;
use DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company)
    {
      /*   $result = DB::select('SELECT sum(purchase_price) as price, sum(supply_qty) as quantity, purchase_id, 
         purchase_date FROM purchases GROUP BY purchase_id, purchase_date'); */

      $result = Purchase::groupBy('purchase_id', 'purchase_date')
        ->selectRaw('purchase_id, purchase_date')
        ->where('company', '=', $company)
        ->orderBy('purchase_date','DESC')->get();

         return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function itemStore($company)
    {
      /* $result = DB::select('SELECT sum(purchase_price) as landover_price, item_id 
        FROM purchases
        Where company = "landover"
        GROUP BY item_id'); */

 

        $result = Purchase::with('item:id,item_name')
        ->groupBy('item_id')
        ->selectRaw('item_id, sum(purchase_price * remainder) as price, sum(remainder) as quantity')
        ->where('company', '=', $company)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }

    public function PurchaseRecords($purchase_id) {

        $result = Purchase::where('purchase_id', '=', $purchase_id)->get();
        return $result;
    }

    public function test() {
        $result = Purchase::orderBy('purchase_date','ASC')
        ->where('item_id', '=', 3)
        ->first();

        return $result->purchase_date;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $company)
    {
        $purchase_id = time();
        $purchase_date = $request->purchaseDate;
       $purchase_items = $request->input('purchaseItems');

       if (Purchase::where('purchase_date', '=', $purchase_date)
       ->where('company', '=', $company)
       ->exists()) {
        return "59";
     } else {
        foreach(json_decode($purchase_items) as $purchase_item) {
            $purchase = new Purchase();
            $purchase->purchase_id = $purchase_id;
            $purchase->purchase_date = $purchase_date;
            $purchase->item_id = $purchase_item->id;
            $purchase->supplier_id = $purchase_item->supplier;
            $purchase->supply_qty = $purchase_item->quantity;
            $purchase->remainder = $purchase_item->quantity;
            $purchase->purchase_price = $purchase_item->price;
            $purchase->company = $purchase_item->company;
            $purchase->save();
     }
          
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        
        $purchase = Purchase::find($request->id);
        $purchase->purchase_id = $request->purchase_id;
        $purchase->purchase_date = $request->purchase_date;
        $purchase->item_id = $request->item_id;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->supply_qty = $request->supply_qty;
        $purchase->remainder = $request->remainder;
        $purchase->purchase_price = $request->purchase_price;
        $purchase->company = $request->company;
        $purchase->save();

        $result = Purchase::where('purchase_id', '=', $request->purchase_id)->get();
        return $result;

    }

    public function add(Request $request, $purchase_id, $company, $purchase_date)
    {
        $purchase = new Purchase();
        $purchase->purchase_id = $purchase_id;
        $purchase->item_id = $request->item_id;
        $purchase->purchase_date = $purchase_date;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->supply_qty = $request->supply_qty;
        $purchase->remainder = $request->supply_qty;
        $purchase->purchase_price = $request->purchase_price;
        $purchase->company = $company;
        $purchase->save();

        $result = Purchase::where('purchase_id', '=', $request->purchase_id)->get();
        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update( $purchase_id, $company, $procurement_date)
    {
        if (Purchase::where('purchase_date', '=', $procurement_date)
        ->where('company', '=', $company)
        ->exists()) {
         return "59";
      } else {
          Purchase::where('purchase_id', '=', $purchase_id)->update(['purchase_date' => $procurement_date]);
      }

      $result = Purchase::groupBy('purchase_id', 'purchase_date')
      ->selectRaw('purchase_id, purchase_date, sum(purchase_price * supply_qty) as price')
      ->where('company', '=', $company)
      ->orderBy('purchase_date','DESC')->get();

       return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Purchase::where('id', $request->id)->delete();

        $result = Purchase::where('purchase_id', '=', $request->purchase_id)->get();
        return $result;
    }
}
