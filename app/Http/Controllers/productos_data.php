<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

ini_set('max_execution_time', 300);

class productos_data extends Controller
{
   public function productos_data(Request $request){

   	$categoria = $request->categoria;
   	$curl = curl_init();
   	$data = ['sub_categorias' => ['nombre' => [], 'id' => [], 'productos' => []]];

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://www.wonduu.com/api/categories?filter[id_parent]=' . $categoria . '&display=[id,name]&output_format=JSON',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_HTTPHEADER => array(
	    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A='
	  ),
	));

	$response = curl_exec($curl);
	$json = json_decode($response, true);

	curl_close($curl);

   	$curl2 = curl_init();

   	if (array_key_exists('categories', $json)) {

		for ($i = 0; $i < count($json['categories']); $i++) { 

	   		curl_setopt_array($curl2, array(
			  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=' . $json['categories'][$i]['id'] . '&display=[id,price,name,id_default_image,id_category_default]&limit=3&output_format=JSON',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A='
			  ),
			));

			$response2 = curl_exec($curl2);
			$json2 = json_decode($response2, true);
			
			array_push($data['sub_categorias']['id'], $json['categories'][$i]['id']);
			array_push($data['sub_categorias']['nombre'], $json['categories'][$i]['name']);

			if (array_key_exists('products', $json2)) {

				array_push($data['sub_categorias']['productos'], $json2['products']);

			}

	   	}

	}
   	
	curl_close($curl2);

	return [$data];

   }

   public function imagenes_data(Request $request){

   	$producto = $request->producto;
   	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://www.wonduu.com/api/images/products/' . $producto . '?limit=1&display=full&output_format=JSON',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_HTTPHEADER => array(
	    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A='
	  ),
	));

	$response = curl_exec($curl);
	$json = json_decode($response, true);

	curl_close($curl);

	return [base64_encode(file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $producto . '/' . $json[""][1]['id'] . '?display=full'))];

   }

   public function imagenes(Request $request){

   	$imagenes = $request->imagenes;
   	$resultado = [];

   	for ($i = 0; $i < count($imagenes) ; $i++) { 

   		if ($imagenes[$i] !== 'pasa') {

   			$imagen = base64_encode(file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $imagenes[$i] . '?display=full'));

   			array_push($resultado, $imagen);

   		}else{

   			array_push($resultado, 'pasa');

   		}

   	}

	return $resultado;

   }

   public function imagenes_categorias(Request $request){

   	$imagenes = $request->categorias;
   	$resultado = [];

   	//return $imagenes;

   	for ($i = 0; $i < count($imagenes) ; $i++) { 

   		if ($imagenes[$i] !== 'pasa') {

   			$imagen = base64_encode(file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/categories/' . $imagenes[$i] . '?display=full'));

   			array_push($resultado, $imagen);

   		}else{

   			array_push($resultado, 'pasa');

   		}

   	}

	return $resultado;

   }

}
