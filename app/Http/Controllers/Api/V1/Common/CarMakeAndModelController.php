<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Models\Master\CarMake;
use App\Models\Master\CarModel;
use App\Http\Controllers\Api\V1\BaseController;
use Carbon\Carbon;
use Sk\Geohash\Geohash;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

/**
 * @group Vehicle Management
 *
 * APIs for vehilce management apis. i.e types,car makes,models apis
 */
class CarMakeAndModelController extends BaseController
{
    protected $car_make;
    protected $car_model;

    public function __construct(CarMake $car_make, CarModel $car_model,Database $database)
    {
        $this->car_make = $car_make;
        $this->car_model = $car_model;
        $this->database = $database;

    }

    /**
    * Get All Car makes
    *
    */
    public function getCarMakes()
    { 
        $transport_type = request()->transport_type;

        return $this->respondSuccess($this->car_make->active()->where('transport_type',$transport_type)->where('vehicle_make_for',request()->vehicle_type)->orderBy('name')->get());
    }

   

    /**
    * Get Car models by make id
    * @urlParam make_id  required integer, make_id provided by user
    */
    public function getCarModels($make_id)
    {
        return $this->respondSuccess($this->car_model->where('make_id', $make_id)->active()->orderBy('name')->get());
    }


    /**
     * Test Api
     * 
     * */
    public function testApi(Request $request){


        dd($request->types);
        
        foreach (json_decode($request->types) as $key => $value) {
            
            dd($value);
        }

       $fire_drivers = $this->database->getReference('drivers/699')->getValue();
        
        // dd(is_array($fire_drivers['vehicle_type']));

        if($fire_drivers['vehicle_type']=='kfnfnkrgkrgrgr'){

            dd("hfhfhfhf");
        }
        $if_type_exists = in_array("7e4994ef-0e21-4e7c-bc47-e18c4d2250ff", $fire_drivers['vehicle_type']);

        dd($if_type_exists);


    }
}
