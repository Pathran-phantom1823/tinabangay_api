<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkedAccount;
class LinkedAccountController extends APIController
{
  public $tracingController = 'App\Http\Controllers\TracingController';
  function __construct(){
    $this->model = new LinkedAccount();
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      // $data[$i]['owner_account'] = $this->retrieveAccountDetails($key['owner']);
      $data[$i]['account'] = $this->retrieveAccountDetails($key['account_id'])->only(['id', 'account_type', 'code', 'username', 'information']);
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      if(app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $key['account_id']) !== null && app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $key['account_id'])){
        $data[$i]['assigned_location'] = app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $key['account_id'])->only(['id', 'account_id', 'assigned_code', 'route', 'code', 'locality', 'region', 'country']);
        $data[$i]['address'] = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $key['account_id'])->only(['id', 'account_id', 'code', 'region', 'route', 'locality', 'country']);
      }else{
        $data[$i]['assigned_location'] = app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $key['account_id']);
        $data[$i]['address'] = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $key['account_id']);
      }
     
      // dd($data[$i]['address']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }

  public function retrieveEmployees(Request $request){
    $condition = $request->all();
    $this->retrieveDB($condition);
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      $data[$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($key['account_id']);
      $data[$i]['created_at_human'] = $this->daysDiffDateTime($key['created_at']);
      $data[$i]['assigned_location'] = app('App\Http\Controllers\LocationController')->getAssignedLocation('account_id', $key['account_id']);
      $data[$i]['address'] = app('App\Http\Controllers\LocationController')->getByParamsWithCode('account_id', $key['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    $this->response['size'] = LinkedAccount::where($condition['condition'][0]['column'], '=', $condition['condition'][0]['value'])->count();
    return $this->response();
  }

  public function getLinkedAccount($column, $value){
    $result = LinkedAccount::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function retrieveTracing(Request $request){
    $data = $request->all();

    $radius = env('RADIUS');
    if (!isset($radius)) {
      throw new \Exception('No env variable for "RADIUS"');
    }

    if (isset($data['radius'])) {
      $radius = $data['radius'];
    }

    $this->retrieveDB($data); // store to 
    $data = $this->response['data'];
    $i = 0;
    foreach ($data as $key) {
      $status = app($this->tracingController)->getStatusByAccountId($data[$i]['account_id']);
      $data[$i]['status'] =  $status['status'];
      $data[$i]['status_from'] =  $status['status_from'];
      $data[$i]['status_label'] =  $status['status_label'];
      $data[$i]['account'] = $this->retrieveAccountDetailsOnlyImportant($data[$i]['account_id']);
      $i++;
    }
    $this->response['data'] = $data;
    return $this->response();
  }
}
