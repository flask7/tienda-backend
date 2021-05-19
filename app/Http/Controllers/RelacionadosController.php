<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RelacionadosController extends Controller
{
    public function relacionados(Request $request){

    	$categoria = $request->categoria;
    	$producto = $request->id;

    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=' . $categoria . '&display=[id,price,name,id_default_image]&limit=4&output_format=JSON',
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

		$datos = [];

		for ($i = 0; $i < count($json["products"]); $i++) { 

			if ($json["products"][$i]['id'] != $producto) {
				
				$base64 = '';

				if (array_key_exists('id_default_image', $json["products"][$i]) && array_key_exists('id', $json["products"][$i])) {

					$base64 = base64_encode(file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $json["products"][$i]['id'] . '/' . $json["products"][$i]['id_default_image'] . '?display=full'));

					array_push($datos, ['id' => $json["products"][$i]['id'], 'precio' => $json["products"][$i]['price'], 'nombre' => $json["products"][$i]['name'], 'imagen' =>  $base64]);

				}else{

					$base64 = 'paso';

				}

			}else{

				continue;

			}

		}

		return $datos;

    }
}
