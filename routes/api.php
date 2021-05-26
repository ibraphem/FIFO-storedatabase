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

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */

//Route::post('login', 'api\UserController@login');

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'api\UserController@login');
    Route::post('signup', 'api\UserController@signup');
    
});

Route::get('department', 'api\DepartmentController@index');

Route::post('department/store', 'api\DepartmentController@store');

Route::post('department/update/{id}', 'api\DepartmentController@update');

Route::get('department/items/{id}', 'api\DisbursementController@deptDisbursement');

Route::get('department/items/{id}/{from}/{to}', 'api\DisbursementController@filterDisbursement');

Route::get('supplier', 'api\SupplierController@index');

Route::post('supplier/store', 'api\SupplierController@store');

Route::post('supplier/update/{id}', 'api\SupplierController@update');

Route::get('item', 'api\ItemController@index');

Route::post('item/store', 'api\ItemController@store');

Route::post('item/update/{id}', 'api\ItemController@update');

Route::get('store/{company}', 'api\PurchaseController@itemStore');

Route::get('stores/Uniform', 'api\UnistoreController@show');

Route::get('procurement/{company}', 'api\PurchaseController@index');

Route::get('purchase/{purchase_id}', 'api\PurchaseController@PurchaseRecords');

Route::post('purchaseItem/add/{purchase_id}/{company}/{purchase_date}', 'api\PurchaseController@add');

Route::post('purchaseItem/edit', 'api\PurchaseController@edit');

Route::post('purchaseItem/delete', 'api\PurchaseController@destroy');

Route::post('procurement/store/{company}', 'api\PurchaseController@store');

Route::post('procurementDate/update/{purchase_id}/{company}/{procurement_date}', 'api\PurchaseController@update');

Route::get('disbursement/{company}', 'api\DisbursementController@index');

Route::post('disburse/{company}', 'api\DisbursementController@store');

Route::post('disbursementDate/update/{disbursement_id}/{company}/{disbursement_date}', 'api\DisbursementController@update');

Route::get('disbursed/{disbursement_id}', 'api\DisbursementController@show');

Route::post('disbursedItem/delete/{disbursement_id}/{item_id}/{department_id}', 'api\DisbursementController@destroy');

Route::get('store/detail/{id}/{company}', 'api\DisbursementController@itemDetails');

Route::get('stores/detail/{id}/{company}', 'api\UnistoreController@uniformDetails');

Route::get('spenders/{company}', 'api\DisbursementController@spenders');

Route::get('reorder', 'api\PurchaseController@reorder');

Route::get('counter', 'api\PurchaseController@counter');

Route::get('recent', 'api\PurchaseController@recent');

Route::get('report/{company}/{year}/{month}', 'api\DisbursementController@report');

Route::get('show/{company}/{year}/{month}', 'api\UniformerController@show');

Route::get('Procurement/{company}/{from}/{to}', 'api\PurchaseController@PurchaseReport');

Route::get('Disbursement/{company}/{from}/{to}', 'api\DisbursementController@DisbursementReport');

Route::get('item/Procurement/{id}/{company}/{from}/{to}', 'api\PurchaseController@itemReport');

Route::get('item/Disbursement/{id}/{company}/{from}/{to}', 'api\DisbursementController@itemReport');

Route::get('category', 'api\CategoryController@index');

Route::post('category/store', 'api\CategoryController@store');

Route::post('category/update/{id}', 'api\CategoryController@update');

Route::get('store/report/{company}/{asat}', 'api\CategoryController@storereport');

Route::get('uniform', 'api\UniformController@index');

Route::get('uniforms/{company}', 'api\UniformController@create');

Route::post('uniform/store', 'api\UniformController@store');

Route::post('uniform/update/{id}', 'api\UniformController@update');

Route::get('uniformer/{company}', 'api\UniformerController@index');

Route::post('uniformer/store/{company}/{date}', 'api\UniformerController@store');

Route::post('uniformer/update/{date}/{company}', 'api\UniformerController@update');

Route::get('unistore/{uniform_id}', 'api\UnistoreController@index');

Route::post('unistore/store/{uniform_id}/{date}', 'api\UnistoreController@store');

Route::post('unistore/update/{id}/{uniform_id}/{date}', 'api\UnistoreController@update');

Route::post('unistore/delete/{id}/{uniform_id}', 'api\UnistoreController@destroy');

Route::get('test', 'api\UnistoreController@test');

Route::get('test2', 'api\ItemController@test2');

