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

	   	if (array_key_exists('categories', $json)) {

	   		$categorias = [];

	   		for ($i = 0; $i < count($json['categories']); $i++) {

	   			array_push($categorias, $json['categories'][$i]['id']);
	   			array_push($data['sub_categorias']['id'], $json['categories'][$i]['id']);
				array_push($data['sub_categorias']['nombre'], $json['categories'][$i]['name']);

	   		}

	   		$categorias_imploded = implode('|', $categorias);
			$curl2 = curl_init();

	   		curl_setopt_array($curl2, array(
			  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id_category_default]=[' . $categorias_imploded . ']&display=[id,price,name,id_default_image,id_category_default,id_tax_rules_group]&output_format=JSON&filter[available_for_order]=1',
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

			if (array_key_exists('products', $json2)) {

				$ids_productos = [];

				for ($x = 0; $x < count($json2['products']); $x ++) {

					array_push($ids_productos, $json2["products"][$x]["id"]);

				}
					
				$ids_imploded = implode('|', $ids_productos);
				$curl_descuentos = curl_init();

				curl_setopt_array($curl_descuentos, array(
				  CURLOPT_URL => 'https://www.wonduu.com/api/specific_prices?display=[id_product,reduction,reduction_type,id_customer]&filter[id_product]=[' . $ids_imploded . ']&output_format=JSON',
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

				$responsed = curl_exec($curl_descuentos);
				$json_descuentos = json_decode($responsed, true);

				curl_close($curl_descuentos);

				$ids_impuestos = [];

				for ($x = 0; $x < count($json2['products']); $x++) { 
					
					array_push($ids_impuestos, $json2["products"][$x]["id_tax_rules_group"]);

				}

				$impuestos_imploded = implode('|', $ids_impuestos);
				$curl_impuestos = curl_init();

				curl_setopt_array($curl_impuestos, array(
						CURLOPT_URL => 'https://www.wonduu.com/api/tax_rules?filter[id_tax_rules_group]=[' . $impuestos_imploded . ']&filter[id_country]=6&output_format=JSON&display=[id_tax,id_tax_rules_group]',
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
				$json_impuestos = json_decode($response_impuestos, true);

				curl_close($curl_impuestos);

				$porcentajes_impuestos = [];

				for ($x = 0; $x < count($json_impuestos['tax_rules']); $x++) { 
					
					array_push($porcentajes_impuestos, $json_impuestos["tax_rules"][$x]["id_tax"]);

				}

				$impuestos_imploded = implode('|', $porcentajes_impuestos);
				$curl_porcentaje_impuestos = curl_init();

				curl_setopt_array($curl_porcentaje_impuestos, array(
				  CURLOPT_URL => 'https://www.wonduu.com/api/taxes?filter[id]=[' . $impuestos_imploded . ']&output_format=JSON&display=[id,rate]',
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

				for ($x = 0; $x < count($json2["products"]); $x++) { 
								
					$impuestos = 0.00;
					$base64 = '';
					$precio_base = floatval($json2["products"][$x]['price']);
					$id_impuesto = 0;

					for ($y = 0; $y < count($json_impuestos["tax_rules"]); $y++) { 

						if ($json_impuestos["tax_rules"][$y]["id_tax_rules_group"] == $json2["products"][$x]["id_tax_rules_group"]) {

							$id_impuesto = $json_impuestos["tax_rules"][$y]["id_tax"];

							break;

						}

					}

					for ($a = 0; $a < count($json_porcentaje_impuestos["taxes"]); $a++) {

						if ($json_porcentaje_impuestos["taxes"][$a]["id"] == $id_impuesto) {

							$impuestos = floatval($json_porcentaje_impuestos["taxes"][$a]['rate'])/100;

							break;

						}

					}

					$porcentaje_impuesto = ($precio_base * $impuestos) + $precio_base;
					$descuento = 0;
					$monto_descuento = 0;

					if ($json_descuentos != null) {
						
						if (array_key_exists('specific_prices', $json_descuentos)) {

							for ($z = 0; $z < count($json_descuentos["specific_prices"]); $z++) {
									
								if ($json_descuentos["specific_prices"][$z]["id_product"] == $json2["products"][$x]["id"]) {

									$descuento = floatval($json_descuentos["specific_prices"][$z]["reduction"]);

									if ($json_descuentos["specific_prices"][$z]["reduction_type"] == 'percentage' && $json_descuentos["specific_prices"][$z]["id_customer"] == '0') {

										$monto_descuento = $porcentaje_impuesto * $descuento;

									} else if ($json_descuentos["specific_prices"][$z]["reduction_type"] == 'amount' && $json_descuentos["specific_prices"][$z]["id_customer"] == '0') {

										$monto_descuento = $descuento;

									}
										
								}

								$precio = $porcentaje_impuesto - $monto_descuento;
								$json2["products"][$x]["price"] = strval($precio);

							}
						}

					}

				}

				for ($x = 0; $x < count($json['categories']); $x++) { 
				
					$contador = 0;
					$array_productos = ["products" => []];

					for ($y = 0; $y < count($json2["products"]); $y++) { 
					
						if ($json['categories'][$x]['id'] == $json2["products"][$y]["id_category_default"]) {

							array_push($array_productos['products'], $json2['products'][$y]);
							$contador++;

						}

						if ($contador == 3) {

							array_push($data['sub_categorias']['productos'], $array_productos);
							$contador = 0;
							break;

						}

					}

				}

			}

		}

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

	return [base64_encode(@file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $producto . '/' . $json[""][0]['id'] . '/large_default'))];

   }

   public function imagenes(Request $request) {

		$imagenes = $request->imagenes;
		$resultado = [];
		$size = '/small_default';

		if (!empty($request->size)) {
			
			$size = '/large_default';

		}

		for ($i = 0; $i < count($imagenes); $i++) { 

			if ($imagenes[$i] != 'pasa') {

				try {

					$imagen = @file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/products/' . $imagenes[$i] . $size);
					$base64 = base64_encode($imagen);

					array_push($resultado, $base64);

				} catch (Exception $ex) {

					array_push($resultado, 'pasa');

				}

			} else {

				array_push($resultado, 'pasa');

			}

		}

		return $resultado;

   }

   public function imagenes_categorias(Request $request) {

   	$imagenes = $request->categorias;
   	$resultado = [];

   	for ($i = 0; $i < count($imagenes) ; $i++) { 

   		if ($imagenes[$i] != 'pasa') {

   			$base64 = file_get_contents('https://4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP@www.wonduu.com/api/images/categories/' . $imagenes[$i] . '?display=full');
   			$imagen = base64_encode($base64);

   			array_push($resultado, $imagen);

   		} else {

   			array_push($resultado, 'pasa');

   		}

   	}

		return $resultado;

   }

}
