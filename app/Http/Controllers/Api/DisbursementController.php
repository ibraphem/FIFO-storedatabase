<?php

namespace App\Http\Controllers\Api;

use App\Disbursement;
use App\Purchase;
use App\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company)
    {
        $result = Disbursement::groupBy('disbursement_id', 'disbursement_date')
        ->selectRaw('disbursement_id, disbursement_date')
        ->where('company', '=', $company)
        ->orderBy('disbursement_date','DESC')->get();

         return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function test()
    {
        $purchase_date = Purchase::orderBy('purchase_date', 'DESC')
        ->where('item_id', '=', 7)
        ->where('company', '=', 'Landover')
        ->first('purchase_date');

        $disbursement_date = Disbursement::orderBy('disbursement_date', 'DESC')
        ->where('item_id', '=', 7)
        ->where('company', '=', 'Landover')
        ->first('disbursement_date');

        $quantity = Purchase::where('item_id', '=', 7)
                    ->where('company', '=', 'Landover')
                    ->sum('supply_qty');

        $purchase = Purchase::orderBy('purchase_date', 'DESC')
        ->with('supplier:id,supplier_name')
        ->where('item_id', '=', 7)
        ->where('company', '=', 'Landover')
        ->get();

      /*  $disbursement = Disbursement::orderBy('disbursement_date', 'DESC')
        ->with('department:id,dept_name')
        ->where('item_id', '=', 7)
        ->where('company', '=', 'Landover')
        ->get(); */

        $disbursement = Disbursement::with('department:id,dept_name')
        ->groupBy('department_id', 'disbursement_id', 'disbursement_date')
        ->selectRaw('disbursement_id, disbursement_date, department_id, sum(disbursed_qty) as quantity')
        ->where('item_id', '=', 7)
        ->orderBy('item_id','DESC')->get(); 

        return array( 'purchase_date' => $purchase_date, 'disbursement_date' => $disbursement_date, 'quantity' => $quantity,
                    'purchase' => $purchase, 'disbursement' => $disbursement);
    }

    public function itemDetails($id, $company) {
        $purchase_date = Purchase::orderBy('purchase_date', 'DESC')
        ->where('item_id', '=', $id)
        ->where('company', '=', $company)
        ->first('purchase_date');

        $disbursement_date = Disbursement::orderBy('disbursement_date', 'DESC')
        ->with('department:id,dept_name')
        ->where('item_id', '=', $id)
        ->where('company', '=', $company)
        ->first('disbursement_date');

        $quantity = Purchase::where('item_id', '=', $id)
        ->where('company', '=', $company)
        ->sum('remainder');

        $purchase = Purchase::orderBy('purchase_date', 'DESC')
        ->with('supplier:id,supplier_name')
        ->where('item_id', '=', $id)
        ->where('company', '=', $company)
        ->get();

        $disbursement = Disbursement::with('department:id,dept_name')
        ->groupBy('department_id', 'disbursement_id', 'disbursement_date')
        ->selectRaw('disbursement_id, disbursement_date, department_id, sum(disbursed_qty) as quantity')
        ->where('item_id', '=', $id)
        ->where('company', '=', $company)
        ->orderBy('disbursement_date','DESC')->get(); 



        return array( 'purchase_date' => $purchase_date, 'disbursement_date' => $disbursement_date, 'quantity' => $quantity,
                    'purchase' => $purchase, 'disbursement' => $disbursement);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $company)
    {

        $disbursement_id = time();
        $disbursement_date = $request->disburseDate;
        $disbursement_items = $request->input('disburseItems');
        $disbursed_id = Str::random(15);
        $item_check = $request->input('itemCheck');

    /*    if (Disbursement::where('disbursement_date', '=', $disbursement_date)
        ->where('company', '=', $company)
        ->exists()) {
         return "59";
        
      } */



        foreach(json_decode($item_check) as $disbursement_item) {
           $instore = Purchase::where('item_id', '=', $disbursement_item->id)
           ->where("company", '=', $company)->sum('remainder');

           if($disbursement_item->quantity > $instore){
               $diff = $disbursement_item->quantity - $instore;
               return array(10, $disbursement_item->item_name, $diff);
               break;
           }
           
     }

     foreach(json_decode($disbursement_items) as $disbursement_item) {
        $first_in = Purchase::orderBy('purchase_date','ASC')
            ->where('item_id', '=', $disbursement_item->id)
            ->where("company", '=', $company)
            ->where("remainder", '>', 0)
            ->first();

        if($first_in->remainder >= $disbursement_item->quantity){
           

            $rem = $first_in->remainder - $disbursement_item->quantity;

            $purchase = Purchase::find($first_in->id);
            $purchase->remainder = $rem;
            $purchase->save();

            $disburse = new Disbursement();
            $disburse->disbursement_id = $disbursement_id;
            $disburse->disbursement_date = $disbursement_date;
            $disburse->item_id = $disbursement_item->id;
            $disburse->disbursed_qty = $disbursement_item->quantity;
            $disburse->department_id = $disbursement_item->dept;
            $disburse->pur_item_id = $first_in->id;
            $disburse->company = $company;
            $disburse->save();

        } else {
            
         
            
            $qty = $disbursement_item->quantity;
           
            
            while($qty > 0) {

                $sec_in = Purchase::orderBy('purchase_date','ASC')
                ->where('item_id', '=', $disbursement_item->id)
                ->where("company", '=', $company)
                ->where("remainder", '>', 0)
                ->first();
       

                if($qty / $sec_in->remainder >= 1) {
                  $rema = $sec_in->remainder;  

            $purchase = Purchase::find($sec_in->id);
            $purchase->remainder = 0;
          
            $disburse = new Disbursement();
            $disburse->disbursement_id = $disbursement_id;
            $disburse->disbursed_id = $disbursed_id;
            $disburse->disbursement_date = $disbursement_date;
            $disburse->item_id = $disbursement_item->id;
            $disburse->disbursed_qty = $rema;
            $disburse->department_id = $disbursement_item->dept;
            $disburse->pur_item_id = $sec_in->id;
            $disburse->company = $company;
            
            $qty = $qty - $rema;

            $purchase->save();
            $disburse->save();
            
                } else {
                    $purchase = Purchase::find($sec_in->id);
                    $purchase->remainder = $sec_in->remainder - $qty;
                  
                    $disburse = new Disbursement();
                    $disburse->disbursement_id = $disbursement_id;
                    $disburse->disbursed_id = $disbursed_id;
                    $disburse->disbursement_date = $disbursement_date;
                    $disburse->item_id = $disbursement_item->id;
                    $disburse->disbursed_qty = $qty;
                    $disburse->department_id = $disbursement_item->dept;
                    $disburse->pur_item_id = $sec_in->id;
                    $disburse->company = $company;
                    
                    $qty = 0;
        
                    $purchase->save();
                    $disburse->save();
                }
            } 

           // return 11;
        }
            
     }

  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Disbursement  $disbursement
     * @return \Illuminate\Http\Response
     */
    public function show($disbursement_id)
    {
        $result = Disbursement::with('item:id,item_name')
        ->with('department:id,dept_name')
        ->groupBy('item_id', 'department_id')
        ->selectRaw('item_id, department_id, sum(disbursed_qty) as quantity')
        ->where('disbursement_id', '=', $disbursement_id)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Disbursement  $disbursement
     * @return \Illuminate\Http\Response
     */
    public function edit(Disbursement $disbursement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Disbursement  $disbursement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Disbursement $disbursement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Disbursement  $disbursement
     * @return \Illuminate\Http\Response
     */
    public function destroy($disbursement_id, $item_id, $department_id)
    {
        $items_to_delete = Disbursement::where('disbursement_id', '=', $disbursement_id)
                            ->where('item_id', '=', $item_id)
                            ->where('department_id', '=', $department_id)
                            ->get();

        foreach($items_to_delete as $item) {
            $purchase = Purchase::find($item->pur_item_id);
            $purchase->remainder = $purchase->remainder + $item->disbursed_qty;
            $purchase->save();

            Disbursement::where('id', $item->id)->delete();
        }

        $result = Disbursement::with('item:id,item_name')
        ->with('department:id,dept_name')
        ->groupBy('item_id', 'department_id')
        ->selectRaw('item_id, department_id, sum(disbursed_qty) as quantity')
        ->where('disbursement_id', '=', $disbursement_id)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }
}
