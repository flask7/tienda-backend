<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

ini_set('max_execution_time', 300);

class productos_data extends Controller
{
   public function productos_data(Request $request) {

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
			  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=' . $json['categories'][$i]['id'] . '&display=[id,price,name,id_default_image,id_category_default,id_tax_rules_group]&limit=3&output_format=JSON',
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

				for ($x = 0; $x < count($json2['products']); $x ++) { 
					
					$id_producto = $json2["products"][$x]["id"];
					$curl_descuentos = curl_init();

					curl_setopt_array($curl_descuentos, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/specific_prices?display=[reduction,reduction_type,id_customer]&limit=1&filter[id_product]=' . $id_producto . '&output_format=JSON',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Content-Type: text/xml',
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A='
					  ),
					));

					$responsed = curl_exec($curl_descuentos);
					$json_descuentos = json_decode($responsed, true);

					curl_close($curl_descuentos);

					$curl_impuestos = curl_init();

					curl_setopt_array($curl_impuestos, array(
							CURLOPT_URL => 'https://www.wonduu.com/api/tax_rules?filter[id_tax_rules_group]=[' . $json2['products'][$x]['id_tax_rules_group'] . ']&limit=1&filter[id_country]=6&output_format=JSON&display=[id_tax]',
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

					$response_impuestos = curl_exec($curl_impuestos);

					curl_close($curl_impuestos);

					$json_impuestos = json_decode($response_impuestos, true);
					$curl_porcentaje_impuestos = curl_init();

					curl_setopt_array($curl_porcentaje_impuestos, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/taxes?filter[id]=[' . $json_impuestos["tax_rules"][0]["id_tax"] . ']&output_format=JSON&display=[rate]',
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

					$response_porcentaje_impuestos = curl_exec($curl_porcentaje_impuestos);
					$json_porcentaje_impuestos = json_decode($response_porcentaje_impuestos, true);

					curl_close($curl_porcentaje_impuestos);

					$base64 = '';
					$precio_base = floatval($json2["products"][$x]['price']);
					$impuestos = floatval($json_porcentaje_impuestos["taxes"][0]['rate'])/100;
					$porcentaje_impuesto = ($precio_base * $impuestos) + $precio_base;
					$descuento = 0;
					$monto_descuento = 0;

					if (array_key_exists('specific_prices', $json_descuentos)) {
						
						$descuento = floatval($json_descuentos["specific_prices"][0]["reduction"]);

						if($json_descuentos["specific_prices"][0]["reduction_type"] == 'percentage' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

							$monto_descuento = $porcentaje_impuesto * $descuento;

						}else if($json_descuentos["specific_prices"][0]["reduction_type"] == 'amount' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

							$monto_descuento = $descuento;

						}

					}

					$precio = $porcentaje_impuesto - $monto_descuento;
					$json2['products'][$x]["price"] = $precio;

				}

				array_push($data['sub_categorias']['productos'], $json2['products']);

			}

	   }

	}
   	
	curl_close($curl2);

	return [$data];

   }

   public function imagenes_data(Request $request) {

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

   public function imagenes(Request $request) {

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

   public function imagenes_categorias(Request $request) {

   	$imagenes = $request->categorias;
   	$resultado = [];

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
