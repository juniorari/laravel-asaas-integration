<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $data['cpf'] = formatOnlyNumber( $data['cpf']);
        $data['phone'] = formatOnlyNumber( $data['phone']);
        $data['postal_code'] = formatOnlyNumber( $data['postal_code']);

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'cpf', 'max:14', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'postal_code'    => 'required|string|min:8|max:8',
            'address'        => 'required|string|max:255',
            'address_number' => 'required|string|max:10',
            'phone'          => 'required|string|min:10|max:20',

        ], [
            'cpf.unique' => 'O CPF já existe cadastrado'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $data['cpf'] = formatOnlyNumber( $data['cpf']);
        $data['phone'] = formatOnlyNumber( $data['phone']);
        $data['postal_code'] = formatOnlyNumber( $data['postal_code']);
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => formatOnlyNumber($data['cpf']),
            'password' => Hash::make($data['password']),
            'postal_code'    => $data['postal_code'],
            'address'        => $data['address'],
            'address_number' => $data['address_number'],
            'phone'          => $data['phone'],
        ]);
    }

}
