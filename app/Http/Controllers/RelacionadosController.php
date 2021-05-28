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
		  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=' . $categoria . '&display=full&limit=4&output_format=JSON',
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

				if (array_key_exists('id_default_image', $json["products"][$i]) && array_key_exists('id', $json["products"][$i])) {

					$id_producto = $json["products"][$i]["id"];
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
					$json_porcentaje_impuestos = json_decode($response_porcentaje_impuestos, true);

					curl_close($curl_porcentaje_impuestos);

					$base64 = '';
					$precio_base = floatval($json["products"][$i]['price']);
					$impuestos = floatval($json_porcentaje_impuestos["taxes"][0]['rate'])/100;
					$porcentaje_impuesto = ($precio_base * $impuestos) + $precio_base;
					$descuento = 0;
					$monto_descuento = 0;

					if ($json_descuentos !== null) {
						
						if (array_key_exists("specific_prices", $json_descuentos)) {

							if (array_key_exists('specific_prices', $json_descuentos)) {
								
								$descuento = floatval($json_descuentos["specific_prices"][0]["reduction"]);

								if($json_descuentos["specific_prices"][0]["reduction_type"] == 'percentage' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

									$monto_descuento = $porcentaje_impuesto * $descuento;
											
								}else if($json_descuentos["specific_prices"][0]["reduction_type"] == 'amount' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

									$monto_descuento = $descuento;

								}

							}

						} 

					}

					$precio = $porcentaje_impuesto - $monto_descuento;
					$base64 = base64_encode(file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $id_producto . '/' . $json["products"][$i]['id_default_image'] . '?display=full'));

					array_push($datos, ['id' => $id_producto, 'precio' => $precio, 'nombre' => $json["products"][$i]['name'], 'imagen' =>  $base64]);

				}else{

					$base64 = 'paso';

				}

			}

		}

		return $datos;

    }
}
