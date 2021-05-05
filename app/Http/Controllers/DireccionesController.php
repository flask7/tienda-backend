<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

ini_set('max_execution_time', 300);

class DireccionesController extends Controller
{
    public function add_direcciones(Request $request){

    	try {
				    
			$webService = new PrestaShopWebservice('https://test3.wonduu.com', 'WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1', false);
			$blankXml = $webService->get(['url' => 'https://test3.wonduu.com/api/addresses?schema=blank']);

		} catch (PrestaShopWebserviceException $ex) {

			  
			echo 'Error: ' . $ex->getMessage();

		}
		
		$addressFields = $blankXml->address->children();
		$addressFields->id_country = 6;
		$addressFields->id_state = $request->estado;
		$addressFields->id_customer = $request->cliente_id;
		$addressFields->city = $request->ciudad;
		$addressFields->postcode = $request->cp;
		$addressFields->address1 = $request->direccion;
		$addressFields->firstname = $request->nombre;
		$addressFields->lastname = $request->apellidos;
		$addressFields->phone = $request->telefono;
		$addressFields->phone_mobile = $request->telefono2;
		$addressFields->dni = $request->identificacion;
		$addressFields->company = $request->empresa;
		$addressFields->alias = '-';

		try {
		
			$createdXml = $webService->add([

			   'resource' => 'addresses',
			   'postXml' => $blankXml->asXML(),

			]);

		} catch (Exception $e) {
			
			echo $e;

		}
		
		$newAddressFields = $createdXml->address->children();
		$respuesta = 'Dirección creada satisfactoriamente';
		$response_id = $addressFields->id;

		return [$respuesta, $response_id];

    }

    public function actualizar_direcciones(Request $request){

    	try {
		    
    		$id = $request->id;
    		$direccion = $request->direccion;

		    $webService = new PrestaShopWebservice('https://test3.wonduu.com', 'WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1', false);

		    $xml = $webService->get([
		        'resource' => 'addresses',
		        'id' => intval($id),
		    ]);

		} catch (PrestaShopWebserviceException $ex) {
		    
		    echo 'Other error: <br />' . $ex->getMessage();
		}

		$addressFields = $xml->address->children();
		$addressFields->id_state = $request->estado;
		$addressFields->city = $request->ciudad;
		$addressFields->postcode = $request->cp;
		$addressFields->address1 = $request->direccion;
		$addressFields->firstname = $request->nombre;
		$addressFields->lastname = $request->apellidos;
		$addressFields->phone = $request->telefono;
		$addressFields->phone_mobile = $request->telefono2;
		$addressFields->dni = $request->identificacion;
		$addressFields->company = $request->empresa;

		$updatedXml = $webService->edit([
		    'resource' => 'addresses',
		    'id' => (int) $addressFields->id,
		    'putXml' => $xml->asXML(),
		]);
		$addressFields = $updatedXml->address->children();

		return ['Dirección actualizada satisfactoriamente'];

    }

    public function eliminar_direcciones(Request $request){
    	
    	try {

    		$id = $request->id;

		    $webService = new PrestaShopWebservice('https://test3.wonduu.com', 'WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1', false);

		    $webService->delete([
		        'resource' => 'addresses',
		        'id' => intval($id),
		    ]);

		    return ['Dirección eliminada satisfactoriamente'];

		} catch (PrestaShopWebserviceException $e) {

		    echo 'Error:' . $e->getMessage();

		}

    }

    public function get_direcciones(Request $request){
    	
    	$id = $request->id;
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/addresses?filter[id_customer]=' . $id . '&display=full&output_format=JSON',
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

}
