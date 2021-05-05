<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class Login extends Controller
{
    public function login(Request $request){

    	$correo = $request->correo;
    	$password = $request->password;
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/customers?filter[email]=' . $correo . '&display=full&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: text/xml',
		    'Authorization: Basic V05CR1lBWEpWRExTQjVTS1dXNjhURkNRWEJEN0ZRWjE6:'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		
		$json = json_decode($response, true);

		if (array_key_exists('customers', $json)) {

			if (password_verify($password, $json['customers'][0]['passwd']) == true) {

		  		session_start();

		  		$respuesta = [];
		  		$r1 = $json['customers'][0]['firstname'] . ' ' . $json['customers'][0]['lastname'];
		  		$r2 = $json['customers'][0]['id'];

		  		array_push($respuesta, $r1, $r2);
		  		
		  		return $respuesta;

		    } else {

				return ["Error de autenticación"];

		    }

		}else{

			return ["Usuario no registrado"];

		}

    }

}