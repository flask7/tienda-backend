<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class productos extends Controller
{
    public function productos(Request $request) {

    	$id = $request->id;
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/categories?filter[id]=' . $id . '&display=full&output_format=JSON',
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

		$response = curl_exec($curl);

		curl_close($curl);
		
		$json = json_decode($response, true);

		return $json;

    }
        
    public function productos_info(Request $request) {

    	$id = $request->id;
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id]=' . $id . '&display=full&limit=1&output_format=JSON',
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

		$response = curl_exec($curl);
		$json = json_decode($response, true);
		
		curl_close($curl);
		
		$json_migas_pan = json_decode($response, true);
		$curl_migas_pan_2 = curl_init();
		$id_categoria = $json['products'][0]['id_category_default'];
		$ruta = null;

		curl_setopt_array($curl_migas_pan_2, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/categories?filter[id]=' . $id_categoria . '&display=[id_parent,name]&output_format=JSON',
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

		$response_migas_pan_2 = curl_exec($curl_migas_pan_2);

		curl_close($curl_migas_pan_2);
		
		$json_migas_pan_2 = json_decode($response_migas_pan_2, true);
		$id_categoria = $json_migas_pan_2['categories'][0]['id_parent'];
		$ruta = $json_migas_pan_2['categories'][0]['name'];

		while (true) {
			
			$curl_migas_pan_3 = curl_init();

			curl_setopt_array($curl_migas_pan_3, array(
			  CURLOPT_URL => 'https://www.wonduu.com/api/categories?filter[id]=' . $id_categoria . '&display=[id_parent,name]&output_format=JSON',
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

			$response_migas_pan_3 = curl_exec($curl_migas_pan_3);

			curl_close($curl_migas_pan_3);
			
			$json_migas_pan_3 = json_decode($response_migas_pan_3, true);
			$id_categoria = $json_migas_pan_3['categories'][0]['id_parent'];

			if (intval($id_categoria) == 1) {

				break;

			}

			$ruta .= ' > ' . $json_migas_pan_3['categories'][0]['name'];

		}

		$curl_descuentos = curl_init();

		curl_setopt_array($curl_descuentos, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/specific_prices?display=[reduction,reduction_type,id_customer]&limit=1&filter[id_product]=' . $id . '&output_format=JSON',
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
				CURLOPT_URL => 'https://www.wonduu.com/api/tax_rules?filter[id_tax_rules_group]=[' . $json['products'][0]['id_tax_rules_group'] . ']&limit=1&filter[id_country]=6&output_format=JSON&display=[id_tax]',
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

		curl_close($curl_porcentaje_impuestos);

		$json_porcentaje_impuestos = json_decode($response_porcentaje_impuestos, true);
		$curl2 = curl_init();

		curl_setopt_array($curl2, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/images/products/' . $json['products'][0]['id'] . '?limit=1&display=full&output_format=JSON',
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

		curl_close($curl2);

		$img = [];

		for ($i = 1; $i < count($json2[""]); $i++) { 

			$imagen = strval($id) . '/' . $json2[""][$i]['id'];

			array_push($img, $imagen);

		}

		if (array_key_exists('product_option_values', $json['products'][0]['associations'])) {

			$opciones = [];
			$curl3 = curl_init();
			$valor_opciones = [];
			$valor_atributos = [];

			for ($i = 0; $i < count($json['products'][0]['associations']['product_option_values']); $i++) { 
			
				array_push($valor_opciones, $json['products'][0]['associations']['product_option_values'][$i]['id']);

			}

			$valor_opciones_imploded = implode('|', $valor_opciones);

			curl_setopt_array($curl3, array(
				  CURLOPT_URL => 'https://www.wonduu.com/api/product_option_values?filter[id]=[' . $valor_opciones_imploded . ']&display=full&output_format=JSON',
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

			$response3 = curl_exec($curl3);
			$json3 = json_decode($response3, true);

			curl_close($curl3);
			array_push($opciones, $json3);

			$curl4 = curl_init();
			$nombre_opciones = [];

			for ($i = 0; $i < count($json3["product_option_values"]); $i++) { 
				
				array_push($valor_atributos, $json3['product_option_values'][$i]['id_attribute_group']);

			}

			$valor_atributos_imploded = implode('|', $valor_atributos);

			curl_setopt_array($curl4, array(
			  CURLOPT_URL => 'https://www.wonduu.com/api/product_options?filter[id]=[' . $valor_atributos_imploded . ']&display=[id,name]&output_format=JSON',
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

			$response4 = curl_exec($curl4);
			$json4 = json_decode($response4, true);

			array_push($nombre_opciones, $json4);
			curl_close($curl4);

			$arrays = [];

			if (array_key_exists("specific_prices", $json_descuentos)) {
				
				$precio_base = floatval($json["products"][0]['price']);
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
				$json['products'][0]['price'] = $precio;

			} 

			$arrays =  [$json, $img, $opciones, $nombre_opciones, $json_porcentaje_impuestos, $ruta];

			return $arrays;

		} else {

			if (array_key_exists("specific_prices", $json_descuentos)) {

				$precio_base = floatval($json["products"][0]['price']);
				$impuestos = floatval($json_porcentaje_impuestos["taxes"][0]['rate'])/100;
				$porcentaje_impuesto = ($precio_base * $impuestos) + $precio_base;
				$descuento = 0;
				$monto_descuento = 0;

				if (array_key_exists('specific_prices', $json_descuentos)) {
					
					$descuento = floatval($json_descuentos["specific_prices"][0]["reduction"]);

					if ($json_descuentos["specific_prices"][0]["reduction_type"] == 'percentage') {

						$monto_descuento = $porcentaje_impuesto * $descuento;

					} else if($json_descuentos["specific_prices"][0]["reduction_type"] == 'amount') {

						$monto_descuento = $descuento;

					}

				}

				$precio = $porcentaje_impuesto - $monto_descuento;
				$json['products'][0]['price'] = $precio;

			} 

			$arrays = [$json, $img, $json_porcentaje_impuestos, $ruta];

			return $arrays;

		}
    }
}
