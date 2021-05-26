<?php

namespace App\Http\Controllers\Api;

use App\Disbursement;
use App\Purchase;
use App\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use DateTime;

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
       $result = Disbursement::with('department:id,dept_name')
        ->with('item:id,item_name')
        ->groupBy('department_id', 'item_id')
        ->selectRaw('item_id, department_id, sum(disbursed_qty * price) as total, sum(disbursed_qty) as quantity')
        ->where("department_id", '=', 2)
        ->orderBy('item_id')
        ->where('company', '=', 'Landover')
        ->get(); 

   /*     $result = Disbursement::selectRaw('sum(disbursed_qty * price) as grandTotal')
        ->where('company', '=', 'Landover')
        ->where('disbursement_date', '>=', '2021-02-01')
        ->where('disbursement_date', '<=', '2021-02-31')
        ->get(); */

        return $result;
     
    }

    public function spenders($company) 
    {
        $month_ini = new DateTime("first day of last month");
        $month_end = new DateTime("last day of last month");
   
           $result = DB::table('disbursements')
           ->leftJoin('departments', 'disbursements.department_id', '=', 'departments.id')
                       ->groupBy('dept_name')
                       ->selectRaw('dept_name, sum(disbursed_qty * price) as price')
                       ->where('disbursement_date', '>=', $month_ini->format('Y-m-d'))
                       ->where('disbursement_date', '<=', $month_end->format('Y-m-d'))
                       ->where('company', '=', $company)
                       ->orderBy("price", 'DESC')
                       ->take(5)
                       ->get(); 
   
           return $result;
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
            $disburse->price = $first_in->purchase_price;
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
            $disburse->disbursement_date = $disbursement_date;
            $disburse->item_id = $disbursement_item->id;
            $disburse->disbursed_qty = $rema;
            $disburse->department_id = $disbursement_item->dept;
            $disburse->pur_item_id = $sec_in->id;
            $disburse->price = $sec_in->purchase_price;
            $disburse->company = $company;
            
            $qty = $qty - $rema;

            $purchase->save();
            $disburse->save();
            
                } else {
                    $purchase = Purchase::find($sec_in->id);
                    $purchase->remainder = $sec_in->remainder - $qty;
                  
                    $disburse = new Disbursement();
                    $disburse->disbursement_id = $disbursement_id;
                    $disburse->disbursement_date = $disbursement_date;
                    $disburse->item_id = $disbursement_item->id;
                    $disburse->disbursed_qty = $qty;
                    $disburse->department_id = $disbursement_item->dept;
                    $disburse->pur_item_id = $sec_in->id;
                    $disburse->price = $sec_in->purchase_price;
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

    public function report($company, $year, $month)
    {
        $depts = Disbursement::groupBy('department_id')
        ->where('company', '=', $company)
        ->where('disbursement_date', '>=', $year . "-" . $month . "-" . "01")
        ->where('disbursement_date', '<=', $year . "-" . $month . "-" . "31")
        ->get('department_id');

        $unit = array();
       for($i = 0; $i < count($depts); $i++) {
        $unit[] = Disbursement::with('department:id,dept_name')
        ->with('item:id,item_name')
        ->groupBy('department_id', 'item_id')
        ->selectRaw('item_id, department_id, sum(disbursed_qty * price) as total, sum(disbursed_qty) as quantity')
        ->where("department_id", '=', $depts[$i]->department_id)
        ->where('company', '=', $company)
        ->where('disbursement_date', '>=', $year . "-" . $month . "-" . "01")
        ->where('disbursement_date', '<=', $year . "-" . $month . "-" . "31")
        ->get();
       }

       $grandTotal = Disbursement::selectRaw('sum(disbursed_qty * price) as grandTotal')
        ->where('company', '=', $company)
        ->where('disbursement_date', '>=', $year . "-" . $month . "-" . "01")
        ->where('disbursement_date', '<=', $year . "-" . $month . "-" . "31")
        ->get();

        $result = array('unit' => $unit, 'grandTotal' => $grandTotal);

        return $result;
    }


    public function DisbursementReport($company, $from, $to) {
        $disbursement = Disbursement::with('department:id,dept_name')
        ->with('item:id,item_name')
        ->groupBy('department_id', 'item_id', 'disbursement_date')
        ->selectRaw('item_id, disbursement_date, department_id, sum(disbursed_qty * price) as total, sum(disbursed_qty) as quantity')
        ->where('company', '=', $company)
        ->where('disbursement_date', '>=', $from)
        ->where('disbursement_date', '<=', $to)
        ->get();
        

        return $disbursement;
    }

    public function itemReport($id, $company, $from, $to) {
        $disbursement = Disbursement::with('department:id,dept_name')
        ->with('item:id,item_name')
        ->groupBy('department_id', 'item_id', 'disbursement_date')
        ->selectRaw('item_id, disbursement_date, department_id, sum(disbursed_qty * price) as total, sum(disbursed_qty) as quantity')
        ->where('company', '=', $company)
        ->where('item_id', '=', $id)
        ->where('disbursement_date', '>=', $from)
        ->where('disbursement_date', '<=', $to)
        ->orderBy('disbursement_date','DESC')
        ->get();
        

        return $disbursement;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Disbursement  $disbursement
     * @return \Illuminate\Http\Response
     */
    public function show($disbursement_id)
    {
     /*   $result = Disbursement::with('item:id,item_name')
        ->with('department:id,dept_name')
        ->groupBy('item_id', 'department_id')
        ->selectRaw('item_id, department_id, sum(disbursed_qty) as quantity')
        ->where('disbursement_id', '=', $disbursement_id)
        ->orderBy('item_id','DESC')->get(); */

        $result = DB::table('disbursements')
        ->leftJoin('departments', 'disbursements.department_id', '=', 'departments.id')
        ->leftJoin('items', 'disbursements.item_id', '=', 'items.id')
        ->groupBy('disbursements.item_id','department_id','item_name', 'dept_name', 'disbursement_id')
        ->select('disbursements.item_id','disbursements.department_id','items.item_name', 'departments.dept_name', DB::raw('sum(disbursements.disbursed_qty) as quantity'))
        ->where('disbursement_id', '=', $disbursement_id)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }

    public function deptDisbursement($id)
    {
        $result = Disbursement::with('item:id,item_name')
        ->groupBy('item_id', 'department_id', 'disbursement_id', 'disbursement_date')
        ->selectRaw('item_id, disbursement_date, disbursement_id, sum(disbursed_qty) as quantity')
        ->where('department_id', '=', $id)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }

    public function filterDisbursement($id, $from, $to)
    {
        $result = Disbursement::with('item:id,item_name')
        ->groupBy('item_id', 'department_id', 'disbursement_id', 'disbursement_date')
        ->selectRaw('item_id, disbursement_date, disbursement_id, sum(disbursed_qty) as quantity')
        ->where('department_id', '=', $id)
        ->where('disbursement_date', '>=', $from)
        ->where('disbursement_date', '<=', $to)
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
    public function update($disbursement_id, $company, $disbursement_date)
    {
        Disbursement::where('disbursement_id', '=', $disbursement_id)->update(['disbursement_date' => $disbursement_date]);

        $result = Disbursement::groupBy('disbursement_id', 'disbursement_date')
        ->selectRaw('disbursement_id, disbursement_date')
        ->where('company', '=', $company)
        ->orderBy('disbursement_date','DESC')->get();

         return $result;
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

        $result = DB::table('disbursements')
        ->leftJoin('departments', 'disbursements.department_id', '=', 'departments.id')
        ->leftJoin('items', 'disbursements.item_id', '=', 'items.id')
        ->groupBy('disbursements.item_id','department_id','item_name', 'dept_name', 'disbursement_id')
        ->select('disbursements.item_id','disbursements.department_id','items.item_name', 'departments.dept_name', DB::raw('sum(disbursements.disbursed_qty) as quantity'))
        ->where('disbursement_id', '=', $disbursement_id)
        ->orderBy('item_id','DESC')->get(); 

        return $result;
    }
}
