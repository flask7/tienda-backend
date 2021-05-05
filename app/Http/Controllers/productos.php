<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class productos extends Controller
{
    public function productos(Request $request){

    	$id = $request->id;
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/categories?filter[id]=' . $id . '&display=full&output_format=JSON',
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

		return $json;

    }
        
    public function productos_info(Request $request){

    	$id = $request->id;
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/products?filter[id]=' . $id . '&display=full&output_format=JSON',
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

		$curl2 = curl_init();

		curl_setopt_array($curl2, array(
		  CURLOPT_URL => 'https://test3.wonduu.com/api/images/products/' . $json['products'][0]['id'] . '?limit=1&display=full&output_format=JSON',
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

		$response2 = curl_exec($curl2);
		$json2 = json_decode($response2, true);
		$img = [];

		for ($i = 1; $i < count($json2[""]); $i++) { 

			$imagen = base64_encode(file_get_contents('https://WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1@test3.wonduu.com/api/images/products/' . $id . '/' . $json2[""][$i]['id'] . '?display=full'));

			array_push($img, $imagen);

		}

		if(array_key_exists('product_option_values', $json['products'][0]['associations'])){

			$opciones = [];
			$curl3 = curl_init();

			for ($i = 0; $i < count($json['products'][0]['associations']['product_option_values']); $i++) { 
			
				curl_setopt_array($curl3, array(
				  CURLOPT_URL => 'https://test3.wonduu.com/api/product_option_values?filter[id]=' . $json['products'][0]['associations']['product_option_values'][$i]['id'] . '&display=full&output_format=JSON',
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

				$response3 = curl_exec($curl3);
				$json3 = json_decode($response3, true);

				array_push($opciones, $json3);

			}

			curl_close($curl3);

			$curl4 = curl_init();
			$nombre_opciones = [];

			for ($i = 0; $i < count($opciones); $i++) { 
				
				curl_setopt_array($curl4, array(
				  CURLOPT_URL => 'https://test3.wonduu.com/api/product_options?filter[id]=' . $opciones[$i]['product_option_values'][0]['id_attribute_group'] . '&display=[id,name]&output_format=JSON',
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

				$response4 = curl_exec($curl4);
				$json4 = json_decode($response4, true);

				array_push($nombre_opciones, $json4);

			}

			curl_close($curl4);

			return [$json, $img, $opciones, $nombre_opciones];

		}else{

			return [$json, $img];

		}

    }
}
