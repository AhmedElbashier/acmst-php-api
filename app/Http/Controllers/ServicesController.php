<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services;

class ServicesController extends Controller
{
    const MODEL = "App\Services";

    public function all()
    {
        $services = Services::all();
        return response()->json($services);
    }
    
    public function add(Request $request)
    {
        $services = Services::create($request->all());

        return response()->json($services, 201);
    }

    public function remove($id)
    {
        Services::findOrFail($id)->delete();
        return response()->json('Deleted Successfully', 200);
    }   
    
    public function put(Request $request, $id)
    {
        $services = Services::findOrFail($id);
        $data = array();
        $data = $request->all();
        $data["name"]= $request["name"];
        $data["type"]= $request["type"];
        $data["department"]= $request["department"];
        $data["price"]= $request["price"];

        $services->update($data);


    }
}