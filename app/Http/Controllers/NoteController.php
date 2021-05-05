<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;
use Carbon\Carbon;

ini_set('max_execution_time', 300);

class NoteController extends Controller
{
	public function registro(Request $request) {

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

			return ["Usuario registrado"];

		}else{

			try {
				    
				$webService = new PrestaShopWebservice('https://test3.wonduu.com', 'WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1', false);
				$blankXml = $webService->get(['url' => 'https://test3.wonduu.com/api/customers?schema=blank']);

			} catch (PrestaShopWebserviceException $ex) {

			  
			    echo 'Other error: ' . $ex->getMessage();

			}

			$customerFields = $blankXml->customer->children();
			$customerFields->active = "1";
			$customerFields->firstname = $request->nombre;
			$customerFields->lastname = $request->apellido;
			$customerFields->email = $request->correo;
			$customerFields->passwd = $request->password;
			$customerFields->birthday = Carbon::parse($request->birthday)->format('Y-m-d');

			$createdXml = $webService->add([

			   'resource' => 'customers',
			   'postXml' => $blankXml->asXML(),

			]);
			
			$newCustomerFields = $createdXml->customer->children();

			$respuesta = [];
			$r1 = $request->nombre . ' ' . $request->apellido;
			$r2 = $newCustomerFields->id;

			array_push($respuesta, $r1, $r2);

			return $respuesta;

		}

	}

}