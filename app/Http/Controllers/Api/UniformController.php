<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Uniform;
use Illuminate\Http\Request;

class UniformController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Uniform::orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($company)
    {
        $result = Uniform::where('company', '=', $company)->get();
        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $uniform = new Uniform();
        $uniform->type = $request->type;
        $uniform->company = $request->company;
        $uniform->save();
        $result = Uniform::orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Uniform  $uniform
     * @return \Illuminate\Http\Response
     */
    public function show(Uniform $uniform)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Uniform  $uniform
     * @return \Illuminate\Http\Response
     */
    public function edit(Uniform $uniform)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Uniform  $uniform
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $uniform = Uniform::find($id);
        $uniform->type = $request->type;
        $uniform->company = $request->company;
        $uniform->save();
        $result = Uniform::orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Uniform  $uniform
     * @return \Illuminate\Http\Response
     */
    public function destroy(Uniform $uniform)
    {
        //
    }
}
