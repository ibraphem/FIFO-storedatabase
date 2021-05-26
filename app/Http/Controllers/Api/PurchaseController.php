<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Purchase;
use App\Item;
use App\Supplier;
use App\Uniform;
use App\Department;
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
        ->selectRaw('purchase_id, purchase_date, sum(purchase_price * supply_qty) as total')
        ->where('company', '=', $company)
        ->orderBy('purchase_date','DESC')->get();

         return $result;
    }

    public function recent() 
    {
        return Purchase::with('item:id,item_name')
                        ->with('supplier:id,supplier_name')
                        ->orderBy('purchase_date', 'DESC')
                        ->take(10)
                        ->get();
    }

    public function counter() 
    {
        $items = Item::count();
        $suppliers = Supplier::count();
        $uniforms = Uniform::count();
        $departments = Department::count();

        $result = array("items" => $items, "suppliers" => $suppliers, "uniforms" => $uniforms, "departments" => $departments);

        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function itemStore($company)
    {


        $result = Purchase::with('item:id,item_name')
        ->groupBy('item_id')
        ->selectRaw('item_id, sum(purchase_price * remainder) as price, sum(remainder) as quantity')
        ->where('company', '=', $company)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }

    public function reorder()
    {

  /*      $result = Purchase::with('item:id,item_name,reorder')
        ->groupBy('item_id', 'company', 'reorder')
        ->selectRaw('item_id, company, sum(remainder) as quantity, reorder')
        ->havingRaw('quantity <= reorder')
        ->orderBy('item_id','DESC')->get(); */

        $result = DB::table('purchases')->groupBy('item_id', 'company', 'item_name', 'reorder')
            ->join('items', 'purchases.item_id', '=', 'items.id')
            ->selectRaw('item_id, company, sum(remainder) as quantity, item_name, reorder')
            ->havingRaw('quantity <= reorder')
            ->get(); 

       return $result;
    }

    public function PurchaseRecords($purchase_id) {

        $result = Purchase::where('purchase_id', '=', $purchase_id)->get();
        return $result;
    }

    public function PurchaseReport($company, $from, $to) {
        $purchase = Purchase::orderBy('purchase_date', 'DESC')
        ->with('supplier:id,supplier_name')
        ->with('item:id,item_name')
        ->where('company', '=', $company)
        ->where('purchase_date', '>=', $from)
        ->where('purchase_date', '<=', $to)
        ->get();

        $grandTotal = Purchase::selectRaw('sum(supply_qty * purchase_price) as grandTotal')
        ->where('company', '=', $company)
        ->where('purchase_date', '>=', $from)
        ->where('purchase_date', '<=', $to)
        ->get();

        $result = array('purchase' => $purchase, 'grandTotal' => $grandTotal);

        return $result;
    }

    public function itemReport($id, $company, $from, $to) {
        $purchase = Purchase::orderBy('purchase_date', 'DESC')
        ->with('supplier:id,supplier_name')
        ->with('item:id,item_name')
        ->where('company', '=', $company)
        ->where('item_id', '=', $id)
        ->where('purchase_date', '>=', $from)
        ->where('purchase_date', '<=', $to)
        ->get();

        $grandTotal = Purchase::selectRaw('sum(supply_qty * purchase_price) as grandTotal')
        ->where('company', '=', $company)
        ->where('item_id', '=', $id)
        ->where('purchase_date', '>=', $from)
        ->where('purchase_date', '<=', $to)
        ->get();

        $result = array('item' => $purchase, 'grandTotal' => $grandTotal);

        return $result;
    }

    public function test() {
        $result = Purchase::with('item:id,item_name')->with('category')
    //    ->groupBy('item_id')
     //   ->selectRaw('item_id, sum(purchase_price * remainder) as price, sum(remainder) as quantity')
        ->where('company', '=', "Landover")
        ->orderBy('item_id','DESC')->get(); 

        return $result; 

    /*    $result = DB::table('purchases')
        ->leftJoin('items', 'purchases.item_id', '=', 'items.id')
        ->groupBy('category_id','item_id')
        ->select('category_id','item_id',  DB::raw('sum(purchase_price * remainder) as price'))
       ->where('company', '=', 'Landover')
        ->orderBy('item_id','DESC')->get(); */

        return $result; 
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
      ->selectRaw('purchase_id, purchase_date, sum(purchase_price * supply_qty) as total')
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
