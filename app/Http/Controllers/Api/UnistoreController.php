<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Uniformer;
use App\Unistore;
use Illuminate\Http\Request;

class UnistoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($uniform_id)
    {
        $result = Unistore::where('uniform_id', '=', $uniform_id)
        ->orderBy('date','DESC')
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
    public function store(Request $request, $uniform_id, $date)
    {
        $unistore = new Unistore();
        $unistore->uniform_id = $uniform_id;
        $unistore->date = $date;
        $unistore->quantity = $request->quantity;
        $unistore->remainder = $request->quantity;
        $unistore->price = $request->price;
        $unistore->save();

        $result = Unistore::where('uniform_id', '=', $uniform_id)
        ->orderBy('date','DESC')
        ->get();
        return $result;



    }

  public function uniformDetails($id, $company)
  {
    $purchase_date = Unistore::orderBy('date', 'DESC')
    ->where('uniform_id', '=', $id)
    ->first('date');

    $disbursement_date = Uniformer::orderBy('date', 'DESC')
    ->where('uniform_id', '=', $id)
    ->first('date');

    $quantity = Unistore::where('uniform_id', '=', $id)
    ->sum('remainder');

    $purchase = Unistore::where('uniform_id', '=', $id)
    ->selectRaw('uniform_id, date as purchase_date, price as purchase_price, quantity as supply_qty')
    ->orderBy('date','DESC')
    ->get();

    $disbursement = Uniformer::where('uniform_id', '=', $id)
    ->groupBy('uniform_id', 'date', 'personnel')
    ->selectRaw('uniform_id, date as disbursement_date, personnel, sum(quantity) as quantity')
    ->orderby('date', 'DESC')
    ->get();



    return array( 'purchase_date' => $purchase_date, 'disbursement_date' => $disbursement_date, 'quantity' => $quantity,
                'purchase' => $purchase, 'disbursement' => $disbursement);
  }

    /**
     * Display the specified resource.
     *
     * @param  \App\Unistore  $unistore
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $result = Unistore::with('uniform:id,type')
        ->groupBy('uniform_id')
        ->selectRaw('uniform_id, sum(price * remainder) as price, sum(remainder) as quantity')
        ->orderBy('uniform_id','DESC')->get(); 

        return $result;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Unistore  $unistore
     * @return \Illuminate\Http\Response
     */
    public function edit(Unistore $unistore)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Unistore  $unistore
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $uniform_id, $date)
    {
        $unistore = Unistore::find($id);
        $unistore->uniform_id = $uniform_id;
        $unistore->date = $date;
        $unistore->quantity = $request->quantity;
        $unistore->remainder = $request->quantity;
        $unistore->price = $request->price;
        $unistore->save();

        $result = Unistore::where('uniform_id', '=', $uniform_id)
        ->orderBy('date','DESC')
        ->get();
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Unistore  $unistore
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $uniform_id)
    {
        Unistore::where('id', $id)->delete();

        $result = Unistore::where('uniform_id', '=', $uniform_id)
        ->orderBy('date','DESC')
        ->get();
        return $result;
    }
}
