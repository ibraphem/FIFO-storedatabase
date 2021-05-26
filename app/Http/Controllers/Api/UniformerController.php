<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Uniformer;
use App\Uniform;
use App\Unistore;
use Illuminate\Http\Request;
use DB;

class UniformerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company)
    {
        $result = Uniformer::where('company', '=', $company)
                ->groupBy('uniform_id', 'date', 'personnel')
                ->selectRaw('uniform_id, date, personnel, sum(quantity) as quantity')
                ->orderby('date', 'DESC')
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
    public function test() {
        $instore = Unistore::where('uniform_id', '=', 1)->sum('remainder');

           return $instore;
    }

    public function store(Request $request, $company, $date)
    {
        $instore = Unistore::where('uniform_id', '=', $request->uniform_id)->sum('remainder');

        if($request->quantity > $instore){
            $diff = $request->quantity - $instore;
            return array(10, $diff);
     
        } else {
            $first_in = Unistore::orderBy('date','ASC')
            ->where('uniform_id', '=', $request->uniform_id)
            ->where("remainder", '>', 0)
            ->first();

            if($first_in->remainder >= $request->quantity){
                $rem = $first_in->remainder - $request->quantity;

                $unistore = Unistore::find($first_in->id);
                $unistore->remainder = $rem;
                $unistore->save();

                $uniformer = new Uniformer();
                $uniformer->uniform_id = $request->uniform_id;
                $uniformer->company = $company;
                $uniformer->quantity = $request->quantity;
                $uniformer->date = $date;
                $uniformer->personnel = $request->personnel;
                $uniformer->unistore_id = $first_in->id;
                $uniformer->save();
            } else {
                $qty = $request->quantity;
                while($qty > 0) {
                $sec_in = Unistore::orderBy('date','ASC')
                ->where('uniform_id', '=', $request->uniform_id)
                ->where("remainder", '>', 0)
                ->first();

                if($qty / $sec_in->remainder >= 1) {
                    $rema = $sec_in->remainder;  
  
              $unistore = Unistore::find($sec_in->id);
              $unistore->remainder = 0;
            
              $uniformer = new Uniformer();
              $uniformer->uniform_id = $request->uniform_id;
              $uniformer->company = $company;
              $uniformer->quantity = $rema;
              $uniformer->date = $date;
              $uniformer->personnel = $request->personnel;
              $uniformer->unistore_id = $sec_in->id;
                
              $qty = $qty - $rema;
  
              $uniformer->save();
              $unistore->save();
              
            } else {
                $unistore = Unistore::find($sec_in->id);
                $unistore->remainder = $sec_in->remainder - $qty;
              
                $uniformer = new Uniformer();
              $uniformer->uniform_id = $request->uniform_id;
              $uniformer->company = $company;
              $uniformer->quantity = $qty;
              $uniformer->date = $date;
              $uniformer->personnel = $request->personnel;
              $uniformer->unistore_id = $sec_in->id;

                
                $qty = 0;
    
                $uniformer->save();
                $unistore->save();
                  }
                }
            }
        }

        $result = Uniformer::where('company', '=', $company)
        ->groupBy('uniform_id', 'date', 'personnel')
        ->selectRaw('uniform_id, date, personnel, sum(quantity) as quantity')
        ->orderby('date', 'DESC')
        ->get();

        return $result;t;
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Uniformer  $uniformer
     * @return \Illuminate\Http\Response
     */
    public function show($company, $year, $month)
    {
        $all = Uniform::where('company', '=', $company)->get(['id', 'type', 'price' ]);
       $uniform = Uniformer::groupBy('uniforms.id', 'uniforms.type', 'uniforms.price')
        ->selectRaw('uniforms.id, sum(quantity) as quantity, uniforms.type, uniforms.price, sum(quantity * price) as total')
        ->where('uniforms.company', '=', $company)
        ->where('uniformers.date', '>=', $year . "-" . $month . "-" . "01")
        ->where('uniformers.date', '<=', $year . "-" . $month . "-" . "31")
        ->rightJoin('uniforms','uniforms.id','=','uniformers.uniform_id')
        ->get(); 

        $uniformer_id = Uniformer::groupBy('uniform_id')
        ->where('company', '=', $company)
        ->where('date', '>=', $year . "-" . $month . "-" . "01")
        ->where('date', '<=', $year . "-" . $month . "-" . "31")
        ->get('uniform_id');

       $uniformer = array();
        for($i = 0; $i < count($uniformer_id); $i++) {
         $uni = Uniformer::where('uniform_id', '=', $uniformer_id[$i]->uniform_id)
                        ->where("company", '=', $company)
                        ->where('date', '>=', $year . "-" . $month . "-" . "01")
                        ->where('date', '<=', $year . "-" . $month . "-" . "31")
                        ->get(['personnel', 'quantity']);
                        $uniformer[] =  array('id' => $uniformer_id[$i]->uniform_id, 'uni' => $uni); 
                                                                                 
        } 
   
     //   return $uniformer;
      return array('uniform' => $uniform, 'uniformer' => $uniformer, 'all' => $all);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Uniformer  $uniformer
     * @return \Illuminate\Http\Response
     */
    public function edit(Uniformer $uniformer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Uniformer  $uniformer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $date, $company)
    {
        $uniformer = Uniformer::find($request->id);
        $uniformer->uniform_id = $request->uniform_id;
        $uniformer->quantity = $request->quantity;
        $uniformer->date = $date;
        $uniformer->company = $company;
        $uniformer->personnel = $request->personnel;
        $uniformer->save();
        
        $result = Uniformer::where('company', '=', $company)
        ->orderby('date', 'DESC')
        ->get();

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Uniformer  $uniformer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Uniformer $uniformer)
    {
        //
    }
}
