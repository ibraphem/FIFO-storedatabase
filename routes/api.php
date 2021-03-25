<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('department', 'api\DepartmentController@index');

Route::post('department/store', 'api\DepartmentController@store');

Route::post('department/update/{id}', 'api\DepartmentController@update');

Route::get('supplier', 'api\SupplierController@index');

Route::post('supplier/store', 'api\SupplierController@store');

Route::post('supplier/update/{id}', 'api\SupplierController@update');

Route::get('item', 'api\ItemController@index');

Route::post('item/store', 'api\ItemController@store');

Route::post('item/update/{id}', 'api\ItemController@update');

Route::get('store/{company}', 'api\PurchaseController@itemStore');

Route::get('procurement/{company}', 'api\PurchaseController@index');

Route::get('purchase/{purchase_id}', 'api\PurchaseController@PurchaseRecords');

Route::post('purchaseItem/add/{purchase_id}/{company}/{purchase_date}', 'api\PurchaseController@add');

Route::post('purchaseItem/edit', 'api\PurchaseController@edit');

Route::post('purchaseItem/delete', 'api\PurchaseController@destroy');

Route::post('procurement/store/{company}', 'api\PurchaseController@store');

Route::post('procurementDate/update/{purchase_id}/{company}/{procurement_date}', 'api\PurchaseController@update');

Route::get('disbursement/{company}', 'api\DisbursementController@index');

Route::post('disburse/{company}', 'api\DisbursementController@store');

Route::get('disbursed/{disbursement_id}', 'api\DisbursementController@show');

Route::post('disbursedItem/delete/{disbursement_id}/{item_id}/{department_id}', 'api\DisbursementController@destroy');

Route::get('store/detail/{id}/{company}', 'api\DisbursementController@itemDetails');

Route::get('tester', 'api\DisbursementController@test');