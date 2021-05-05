<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PrestaShopWebservice;

ini_set('max_execution_time', 60);

class PerfilController extends Controller
{
    public function get_perfil(Request $request){
    	
    	$id = $request->id;
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/customers?filter[id]=' . $id . '&display=full&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic V05CR1lBWEpWRExTQjVTS1dXNjhURkNRWEJEN0ZRWjE6',
		    ),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		return $response;

    }

    public function actualizar_perfil(Request $request){

    	try {

		    $webService = new PrestaShopWebservice('https://test3.wonduu.com', 'WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1', false);

		    $xml = $webService->get([
		        'resource' => 'customers',
		        'id' => intval($request->id),
		    ]);

		} catch (PrestaShopWebserviceException $ex) {
		    
		    echo 'Other error: <br />' . $ex->getMessage();
		}

		$customersFields = $xml->customer->children();
		$customersFields->email = $request->correo;
		$customersFields->firstname = $request->nombre;
		$customersFields->lastname = $request->apellidos;
		$customersFields->birthday = Carbon::parse($request->fecha)->format('Y-m-d');;
		//$customersFields->passwd = $request->password;

		$updatedXml = $webService->edit([
		    'resource' => 'customers',
		    'id' => (int) $customersFields->id,
		    'putXml' => $xml->asXML(),
		]);

		$customersFields = $updatedXml->customer->children();

		return ['Perfil actualizado satisfactoriamente'];

    }

}
