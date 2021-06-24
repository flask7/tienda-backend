<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubproductosController extends Controller
{ 
    public function sub_productos(Request $request){

    	$categoria = $request->categoria;
	   	$pagina_actual = strval($request->pagina);
	   	$curl_productos = curl_init();

	   	curl_setopt_array($curl_productos, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/categories?display=full&output_format=JSON&filter[id]=' . $categoria,
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

		$response_productos = curl_exec($curl_productos);
		$json_productos = json_decode($response_productos, true);

		if (!array_key_exists('associations', $json_productos['categories'][0])) {
			
			return [];

		}

		if (!array_key_exists('products', $json_productos['categories'][0]['associations'])) {
			
			return [];

		}

		$curl = curl_init();
		$productos_ids = [];

		for ($i = 0; $i < count($json_productos['categories'][0]['associations']['products']); $i++) { 
			
			array_push($productos_ids, $json_productos['categories'][0]['associations']['products'][$i]['id']);

		}

		$productos_imploded = implode('|', $productos_ids);
		$datos = ['id_producto' => [], 'precio' => [], 'nombre' => [], 'imagen' => ['id_imagen' => [], 'base64' => []], 'paginas' => count($productos_ids)/30];

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id]=[' . $productos_imploded . ']&display=[id,price,name,id_default_image,id_tax_rules_group]&output_format=JSON&limit=' . $pagina_actual . ',30',
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

		for ($i = 0; $i < count($json["products"]); $i++) { 

			$base64 = '';

			if (array_key_exists('id', $json["products"][$i])) {

				if (array_key_exists('id_default_image', $json["products"][$i])) {

					if (empty($json["products"][$i]['id_default_image'])) {
						
						$base64 = 'paso';

					} else {

		   				$base64 = $json["products"][$i]['id'] . '/' . $json["products"][$i]['id_default_image'];

					}
					
				} else {

					$base64 = 'paso';

				}

			} else {

				$base64 = 'paso';

			}

			array_push($datos['imagen']['base64'], $base64);
			array_push($datos['imagen']['id_imagen'], $json["products"][$i]['id_default_image']);
			array_push($datos['id_producto'], $json["products"][$i]['id']);
			array_push($datos['precio'], $this->obtener_precios($json, $i, $json["products"][$i]['id']));
			array_push($datos['nombre'], $json["products"][$i]['name']);

		}

		return $datos;

	}

	public function obtener_precios($json, $i, $id)
	{
		
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
				CURLOPT_URL => 'https://www.wonduu.com/api/tax_rules?filter[id_tax_rules_group]=[' . $json['products'][$i]['id_tax_rules_group'] . ']&limit=1&filter[id_country]=6&output_format=JSON&display=[id_tax]',
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
		$precio_base = floatval($json["products"][$i]['price']);
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

		return $precio;

	}

}