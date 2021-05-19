<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubproductosController extends Controller
{ 
    public function sub_productos(Request $request){

    	$categoria = $request->categoria;
	   	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=' . $categoria . '&display=[id,price,name,id_default_image]&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A'
		  ),
		));

		$response = curl_exec($curl);
		$json = json_decode($response, true);

		curl_close($curl);

		$datos = ['id_producto' => [], 'precio' => [], 'nombre' => [], 'imagen' => ['id_imagen' => [], 'base64' => []]];

		for ($i = 0; $i < count($json["products"]); $i++) { 

			$base64 = '';

			if (array_key_exists('id_default_image', $json["products"][$i]) && array_key_exists('id', $json["products"][$i])) {

				$base64 = base64_encode(file_get_contents('https://WNBGYAXJVDLSB5SKWW68TFCQXBD7FQZ1@www.wonduu.com/api/images/products/' . $json["products"][$i]['id'] . '/' . $json["products"][$i]['id_default_image'] . '?display=full'));

			}else{

				$base64 = 'paso';

			}

			array_push($datos['imagen']['base64'], $base64);
			array_push($datos['imagen']['id_imagen'], $json["products"][$i]['id_default_image']);
			array_push($datos['id_producto'], $json["products"][$i]['id']);
			array_push($datos['precio'], $json["products"][$i]['price']);
			array_push($datos['nombre'], $json["products"][$i]['name']);

		}

		return $datos;

	}

}