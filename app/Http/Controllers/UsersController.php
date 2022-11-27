<?php

namespace App\Http\Controllers;

use App\Role;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{

    const MODEL = "App\users";

    public function all()
    {
        $m = self::MODEL;
        $users = array();
        foreach ($m::all() as $user) {
            $data = Role::find($user['role']);
            if ($data)
                $role = array_map(function ($value) {
                    return ($value ? 'true' : 'false');
                }, $data->toArray());
            $role['id'] = $data['id'];
            $role['created_at'] = $data['created_at'];
            $role['updated_at'] = $data['updated_at'];
            $user['role'] = $role;
            $users[] = $user;
        }
        return $this->respond(Response::HTTP_OK, $users);
    }

    public function get($id)
    {
        $m = self::MODEL;
        $user = $m::find($id);
        if (is_null($user)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $role = Role::find($user['role']);
        $user['role'] = $role;
        return $this->respond(Response::HTTP_OK, $user);
    }

    public function add(Request $request)
    {

        $m = self::MODEL;
        $this->validate($request, $m::$rules);
        $password = Hash::make($request->input('password'));
        $request->merge(['password' => $password]);
        return $this->respond(Response::HTTP_CREATED, $m::create($request->all()));
    }

    public function put(Request $request, $id)
    {
        $user = $request->all();
        $m = self::MODEL;
        $this->validate($request, $m::$rules);
        $model = $m::find($id);
        $role = Role::find($model->role);
        $role['acceptance'] = $user['role']['acceptance'] == 'true' ? 1 : 0;
        $role['approval'] = $user['role']['approval'] == 'true' ? 1 : 0;
        $role['registration'] = $user['role']['registration'] == 'true' ? 1 : 0;
        $role['scolarship'] = $user['role']['scolarship'] == 'true' ? 1 : 0;
        $role['installment'] = $user['role']['installment'] == 'true' ? 1 : 0;
        $role['exams'] = $user['role']['exams'] == 'true' ? 1 : 0;
        $role['settings'] = $user['role']['settings'] == 'true' ? 1 : 0;
        $role->update();
        $model['role'] = $role['id'];
        $model['name'] = $user['name'];
        $model['username'] = $user['username'];
        $password = Hash::make($request->input('password'));
        $model['password'] =  $password;
        $model->update();
        return $this->respond(Response::HTTP_OK, $model);
    }

    public function remove($id)
    {
        $m = self::MODEL;
        if (is_null($m::find($id))) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $m::destroy($id);
        return $this->respond(Response::HTTP_NO_CONTENT);
    }

    public function signRole(Request $request, $id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if (is_null($m::find($id)) || is_null($request->input('role')) || is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $model->role = $request->input('role');
        $model->update();
        return $this->respond(Response::HTTP_OK, $model);
    }

    protected function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }
}
