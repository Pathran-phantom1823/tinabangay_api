<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rides;
class RidesController extends APIController
{
  function __construct(){
    $this->model = new Rides();
  }
}
