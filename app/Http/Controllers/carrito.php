<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class carrito extends Controller
{
    public function add_carrito(Request $request) {

		if (!$request->id || !$request->quantity) {

			return ['Error en envío de los datos'];

		} else {

			try {

				$webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
				$xml = $webService->get(['url' => 'https://www.wonduu.com/api/carts?schema=blank']);
				$id = $request->id_customer;
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL =>'https://www.wonduu.com/api/carts?filter[id_customer]=' . $id . '&display=full&output_format=JSON',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'GET',
				  CURLOPT_HTTPHEADER => array(
				    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
				    ),
				));

				$json = json_decode(curl_exec($curl), true);

				curl_close($curl);

				$curl_productos = curl_init();

				curl_setopt_array($curl_productos, array(
				  CURLOPT_URL =>'https://www.wonduu.com/api/products?filter[id]=' . $request->id . '&display=[id_default_combination]&output_format=JSON',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'GET',
				  CURLOPT_HTTPHEADER => array(
				    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
				    ),
				));

				$json_productos = json_decode(curl_exec($curl_productos), true);

				curl_close($curl_productos);

				if (array_key_exists("carts", $json)) {

					$id_carrito = count($json['carts']) - 1;
	        		$id2 = $request->id;
					$curl2 = curl_init();

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/combinations?filter[id_product]=' . $id2 . '&display=full&output_format=JSON',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
					    ),
					));

					$json2 = json_decode(curl_exec($curl2), true);

					curl_close($curl2);

					$combinations = [];
					$v = null;
					$cantidad_comb = null;

					if ($request->opciones) {

						if (count($request->opciones) > 0) {

							$resultados = $this->validar_combinacion($v, $cantidad_comb, $json2, $combinations, $request, $json);
							$v = $resultados[0];
							$cantidad_comb = $resultados[1];
							$combinations = $resultados[2];

							if (floatval($cantidad_comb) < floatval($request->quantity)) {
											
								return ['Cantidad del producto ' . $request->nombre . ' excedida, disponibles: ' . $cantidad_comb];

							}

						} else {

							if (array_key_exists('combinations', $json2)) {
								
								for ($i = 0; $i < count($json2["combinations"]); $i++) { 
							
									$resultados = $this->validar_combinacion_2($request, $json2, $i, $json, $json_productos["products"][0]["id_default_combination"]);

									$v = $resultados[0];
									//$cantidad_comb = $resultados[1];

									if ($resultados != null) {
										
										break;

									}

								}

								if (count($resultados) > 1) {
										
									return [$resultados[0]];

								}

								$v = $resultados[0];

							} else {

								$v = $json_productos["products"][0]["id_default_combination"];

							}

						}

					} else {

						if (array_key_exists('combinations', $json2)) {

							for ($i = 0; $i < count($json2["combinations"]); $i++) { 
								
								$resultados = $this->validar_combinacion_2($request, $json2, $i, $json, $json_productos["products"][0]["id_default_combination"]);

								$v = $resultados[0];
								//$cantidad_comb = $resultados[1];

								if ($resultados != null) {
									
									break;

								}

							}

							if (count($resultados) > 1) {
									
								return [$resultados[0]];

							}

							$v = $resultados[0];

						} else {

							if (array_key_exists('id_default_combination', $json_productos['products'][0])) {

								$v = $json_productos["products"][0]["id_default_combination"];

							} else {

								$v = '0';

							}

						}

					}

					$new_row = $webService->get([

					   'resource' => 'carts',
					   'id' => $json["carts"][$id_carrito]["id"]

					]);

					$cart = [];

					if (array_key_exists('associations', $json['carts'][$id_carrito])) {

						$cart = $json['carts'][$id_carrito]['associations']['cart_rows'];

					}

					$aux = [];
					$aux['id_product'] = $request->id;
					$aux['id_address_delivery'] = $request->direccion;
					$aux['id_product_attribute'] = $v;
					$aux['quantity'] = $request->quantity;
					$cart[] = $aux;

					for ($y = 0; $y < count($cart); $y++){
						
						$new_row->cart->associations->cart_rows->cart_row[$y]->id_product = $cart[$y]['id_product'];
						$new_row->cart->associations->cart_rows->cart_row[$y]->id_address_delivery = $cart[$y]['id_address_delivery'];
						$new_row->cart->associations->cart_rows->cart_row[$y]->id_product_attribute = $cart[$y]['id_product_attribute'];
						$new_row->cart->associations->cart_rows->cart_row[$y]->quantity = $cart[$y]['quantity'];
			
					}
					
				    $updatedXml = $webService->edit([
					    'resource' => 'carts',
				    	'id' => $json["carts"][$id_carrito]["id"],
					    'putXml' => $new_row->asXML()
					]);

				    return ['Producto añadido satisfactoriamente', $updatedXml];

				} else {

					$curl2 = curl_init();

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/combinations?filter[id_product]=' . $request->id . '&display=full&output_format=JSON',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
					    ),
					));

					$response3 = curl_exec($curl2);
					$json2 = json_decode($response3, true);
					$id_combinacion = null;

					curl_close($curl2);

					for ($i = 0; $i < count($json2["combinations"]); $i++) { 

						$resultados = $this->validar_combinacion_2($request, $json2, $i, $json, $json_productos["products"][0]["id_default_combination"]);

						if ($resultados != null) {
							
							if (count($resultados) > 1) {
							
								return [$resultados[0]];

							}

							$id_combinacion = $resultados[0];
								
							break;

						}

					}

					$xml->cart->id_customer = $request->id_customer;
			        $xml->cart->id_address_delivery = $request->direccion;
			        $xml->cart->id_address_invoice = $request->direccion;
			        $xml->cart->id_currency = 1;
			        $xml->cart->id_lang = 1;
			        $xml->cart->id_carrier = 28;
			        $xml->cart->associations->cart_rows->cart_row->id_product = $request->id;
			        $xml->cart->associations->cart_rows->cart_row->quantity = $request->quantity;
					$xml->cart->associations->cart_rows->cart_row->id_product_attribute = $id_combinacion;

		        	$createdXml = $webService->add([

					   'resource' => 'carts',
					   'postXml' => $xml->asXML(),

					]);

			        $newCartsFields = $createdXml->cart->children();
					$respuesta = 'Producto añadido satisfactoriamente';
					$newCartsFields = $newCartsFields->id;

					return [$respuesta, $newCartsFields];

				}

			} catch (PrestaShopWebserviceException $ex) {
				  
				return [$ex->getMessage()];

			}

		}

    }

    public function validar_combinacion($v, $cantidad_comb, $json2, $combinations, $request, $json)
    {
   
    	for ($i = 0; $i < count($json2["combinations"]); $i++) { 

			$opts = $json2["combinations"][$i]["associations"]["product_option_values"];

			if (count($opts) == count($request->opciones)) {

				for ($y = 0; $y < count($opts); $y++) { 
					
					array_push($combinations, $opts[$y]["id"]);

					if ($combinations === $request->opciones) {
					 	
						$v = $json2["combinations"][$i]["id"];
						$cantidad_comb = $json2["combinations"][$i]["quantity"];

						break;

					} 

					if (count($combinations) == count($request->opciones)) {

						$combinations = [];

					}

				}

			}

		}

		return [$v, $cantidad_comb, $combinations];

    }

    public function validar_combinacion_2($request, $json2, $i, $json, $id_combinacion_defecto)
    {

    	if ($request->opciones) {

			if (count($request->opciones) > 0) {

				for ($a = 0; $a < count($request->opciones); $a++) { 
									
					$coincidencias = 0;

					for ($x = 0; $x < count($json2["combinations"][$i]["associations"]["product_option_values"]); $x++) { 

						if ($json2["combinations"][$i]["associations"]["product_option_values"][$x]["id"] == $request->opciones[$a]) {

							$coincidencias++;

						}

						if ($coincidencias == (count($json2["combinations"][$i]["associations"]["product_option_values"][$x]) - 1)) {

							if (floatval($json2["combinations"][$i]["quantity"]) < floatval($request->quantity)) {
								
								return ['Cantidad del producto ' . $request->nombre . ' excedida, disponibles: ' . $json2["combinations"][$i]["quantity"], 0];

							} else {

								return [$json2["combinations"][$i]["id"]];

							}

							break;

						}

					}

				}

			} else {

				$valor = $this->validar_combinacion_3($i, $json, $json2, $request, $id_combinacion_defecto);

				return $valor;

			}

		} else {

			$valor = $this->validar_combinacion_3($i, $json, $json2, $request, $id_combinacion_defecto);

			return $valor; 

		}

    }

    public function validar_combinacion_3($i, $json, $json2, $request, $id_combinacion_defecto)
    {	
			
		if (strval($id_combinacion_defecto) == strval($json2["combinations"][$i]["id"])) {
			
			if (floatval($json2["combinations"][$i]["quantity"]) < floatval($request->quantity)) {
					
				return ['Cantidad del producto ' . $request->nombre . ' excedida, disponibles: ' . $json2["combinations"][$i]["quantity"], 0];

			} else {

				return [$json2["combinations"][$i]["id"]];

			}
			
		}

    }

    public function eliminar_carrito(Request $request) {

    	$webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
		$id = $request->id_customer;
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL =>'https://www.wonduu.com/api/carts?filter[id_customer]=' . $id . '&display=full&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
		    ),
		));

		$json = json_decode(curl_exec($curl), true);
		$id_carro = count($json["carts"]) - 1;

		curl_close($curl);

		$new_row = $webService->get([

		   'resource' => 'carts',
		   'id' => $json["carts"][$id_carro]["id"]

		]);

		$cart = $json['carts'][$id_carro]['associations']['cart_rows'];
		$contador = 0;

		for ($y = 0; $y < count($cart); $y++) {
			
			if ($y === intval($request->id) && $contador < 1) {
				
				$contador++;
				unset($new_row->cart->associations->cart_rows->cart_row[$y]);
				break;
	
			} else {

				$new_row->cart->associations->cart_rows->cart_row[$y]->id_product = $cart[$y]['id_product'];
				$new_row->cart->associations->cart_rows->cart_row[$y]->id_address_delivery = $cart[$y]['id_address_delivery'];
				$new_row->cart->associations->cart_rows->cart_row[$y]->id_product_attribute = $cart[$y]['id_product_attribute'];
				$new_row->cart->associations->cart_rows->cart_row[$y]->quantity = $cart[$y]['quantity'];

			}

			
		}
		
	    $updatedXml = $webService->edit([
		    'resource' => 'carts',
	    	'id' => $json["carts"][$id_carro]["id"],
		    'putXml' => $new_row->asXML()
		]);

		return ['Producto eliminado satisfactoriamente'];

    }

    public function get_carrito(Request $request) {

		$id = $request->id;
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.wonduu.com/api/carts?filter[id_customer]=' . $id . '&display=full&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
		    ),
		));

		$response = json_decode(curl_exec($curl), true);
		$ids = [];

		curl_close($curl);

		if ($response != null) {

			if (array_key_exists("carts", $response)) {

				$id_carrito = count($response['carts']) - 1;

				if (array_key_exists('associations', $response['carts'][$id_carrito])) {
					
					if (array_key_exists('cart_rows', $response['carts'][$id_carrito]['associations'])) {
						
						for ($i = 0; $i < count($response["carts"][$id_carrito]['associations']['cart_rows']); $i++) { 
 
							array_push($ids, $response['carts'][$id_carrito]['associations']['cart_rows'][$i]['id_product']);

						}

					} else {

						return [];

					}
					
				} else {

					return [];

				}
				
				sort($ids);
 
				if (count($ids) > 0) {
					
					$curl2 = curl_init();
					$filtro = implode('|', $ids);

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id]=[' . $filtro . ']&display=full&output_format=JSON',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
					    ),
					));

					$response2 = json_decode(curl_exec($curl2), true);

					curl_close($curl2);

					$opciones_nombres = [];

					if (array_key_exists('products', $response2)) {

						for ($x = 0; $x < count($response2["products"]); $x++) { 
							
							if (array_key_exists('associations', $response2["products"][$x])) {

								if (array_key_exists('product_option_values', $response2["products"][$x]['associations'])) {
									
									for ($i = 0; $i < count($response2["products"][$x]["associations"]["product_option_values"]); $i++) { 
									
										array_push($opciones_nombres, $response2["products"][$x]["associations"]["product_option_values"][$i]["id"]);

									}
										
								}

							}

						}

						$nuevos_precios = $this->calcular_impuestos($response2);
						$response2 = $nuevos_precios;

						$opciones_nombres_imploded = implode('|', $opciones_nombres);
						$curlx = curl_init();

						curl_setopt_array($curlx, array(
						  CURLOPT_URL => 'https://www.wonduu.com/api/product_option_values?filter[id]=[' . $opciones_nombres_imploded . ']&display=full&output_format=JSON',
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

						$responsex = curl_exec($curlx);
						$jsonx = json_decode($responsex, true);

						curl_close($curlx);

						$valor_atributos = [];

						if (array_key_exists("product_option_values", $jsonx)) {
						
							for ($i = 0; $i < count($jsonx["product_option_values"]); $i++) { 
							
								array_push($valor_atributos, $jsonx['product_option_values'][$i]['id_attribute_group']);

							}

						}

						$valor_atributos_imploded = implode('|', $valor_atributos);
						$curl3 = curl_init();

						curl_setopt_array($curl3, array(
						  CURLOPT_URL => 'https://www.wonduu.com/api/product_options?display=full&output_format=JSON&filter[id]=[' . $valor_atributos_imploded . ']',
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => '',
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 0,
						  CURLOPT_FOLLOWLOCATION => true,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => 'GET',
						  CURLOPT_HTTPHEADER => array(
						    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
						    ),
						));

						$response3 = json_decode(curl_exec($curl3), true);

						curl_close($curl3);

						return [$response, $response2, $response3, $jsonx];

					} else {

						return [];

					}

				} else {

					return [];

				}

			} else {

				return [];

			}
			
		} else {

			return [];

		}

    }

    public function modificar_producto_carrito(Request $request)
    {
    	
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL =>'https://www.wonduu.com/api/carts?filter[id_customer]=' . $request->id_customer . '&display=full&output_format=JSON',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
		    ),
		));

		$json = json_decode(curl_exec($curl), true);

		curl_close($curl);

		$cart = [];
		$id_carrito = count($json['carts']) - 1;
		$webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
    	
    	$new_row = $webService->get([

		   'resource' => 'carts',
		   'id' => $json['carts'][$id_carrito]['id']

		]);

		$curl_combinacion = curl_init();

		curl_setopt_array($curl_combinacion, array(
		  CURLOPT_URL =>'https://www.wonduu.com/api/combinations?display=full&output_format=JSON&filter[id_product]=' . $request->id,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A',
		    ),
		));

		$json_combinacion = json_decode(curl_exec($curl_combinacion), true);
		
		curl_close($curl_combinacion);

		if (array_key_exists('associations', $json['carts'][$id_carrito])) {

			$cart = $json['carts'][$id_carrito]['associations']['cart_rows'];

		}

		$nueva_combinacion = null;
		$opciones_array = [];

		if ($request->opciones) {

			if (count($request->opciones) > 0) {

				for ($i = 0; $i < count($request->opciones); $i++) { 
					
					array_push($opciones_array, $request->opciones[$i]);

				}
				
			}

		}

		if (array_key_exists('combinations', $json_combinacion)) {

			for ($i = 0; $i < count($json_combinacion["combinations"]); $i++) { 
				
				$array_combinacion = [];

				for ($y = 0; $y < count($json_combinacion["combinations"][$i]["associations"]["product_option_values"]); $y++) { 
					
					array_push($array_combinacion, $json_combinacion["combinations"][$i]["associations"]["product_option_values"][$y]["id"]);

					if ($y === count($json_combinacion["combinations"][$i]["associations"]["product_option_values"]) - 1) {

						sort($opciones_array);
						sort($array_combinacion);

						if ($opciones_array === $array_combinacion) {

							$nueva_combinacion = $json_combinacion["combinations"][$i]["id"];

							if (floatval($json_combinacion["combinations"][$i]["quantity"]) < floatval($request->quantity)) {
								
								return ["La cantidad del producto ha sido excedida, existencia: " . $json_combinacion["combinations"][$i]["quantity"]];

							}

							break;

						}

					}

				}

			}

		} else {

			$curl_cantidad = curl_init();

			curl_setopt_array($curl_cantidad, array(
			  CURLOPT_URL => 'https://www.wonduu.com/api/products?display=[name,quantity,id_default_combination]&filter[id]=' . $request->id . '&output_format=JSON',
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

			$responsep = curl_exec($curl_cantidad);
			$json_cantidad = json_decode($responsep, true);

			curl_close($curl_cantidad);

			if (floatval($json_cantidad["products"][0]["quantity"]) < floatval($request->quantity)) {
								
				return ["La cantidad del producto ha sido excedida, existencia: " . $json_cantidad["products"][0]["quantity"]];

			}

			$nueva_combinacion = $json_cantidad["products"][0]["id_default_combination"];
			
		}

		for ($y = 0; $y < count($cart); $y++){
			
			if ($request->indice == $y) {
				
				if ($request->opciones) {

					if (count($request->opciones) > 0) {

						$new_row->cart->associations->cart_rows->cart_row[$y]->id_product_attribute = $nueva_combinacion;
						
					}

				}
				
				$new_row->cart->associations->cart_rows->cart_row[$y]->quantity = $request->quantity;

			} else {

				$new_row->cart->associations->cart_rows->cart_row[$y]->id_product_attribute = $cart[$y]['id_product_attribute'];
				$new_row->cart->associations->cart_rows->cart_row[$y]->quantity = $cart[$y]['quantity'];

			}

			$new_row->cart->associations->cart_rows->cart_row[$y]->id_product = $cart[$y]['id_product'];
			$new_row->cart->associations->cart_rows->cart_row[$y]->id_address_delivery = $cart[$y]['id_address_delivery'];
	

		}
		
	    $updatedXml = $webService->edit([
		    'resource' => 'carts',
	    	'id' => $json['carts'][$id_carrito]['id'],
		    'putXml' => $new_row->asXML()
		]);

	    return ['Modificación exitosa'];

    }

    public function calcular_impuestos($json)
    {

		for ($i = 0; $i < count($json["products"]); $i++) { 

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

			$impuestos = 1;

			if (array_key_exists("id_tax_rules_group", $json['products'][$i])) {
				
				$curl_impuestos = curl_init();

				curl_setopt_array($curl_impuestos, array(
						CURLOPT_URL => 'https://www.wonduu.com/api/tax_rules?filter[id_tax_rules_group]=' . $json['products'][$i]['id_tax_rules_group'] . '&limit=1&filter[id_country]=6&output_format=JSON&display=[id_tax]',
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
				  CURLOPT_URL => 'https://www.wonduu.com/api/taxes?filter[id]=' . $json_impuestos["tax_rules"][0]["id_tax"] . '&output_format=JSON&display=[rate]',
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

				$impuestos = floatval($json_porcentaje_impuestos["taxes"][0]['rate'])/100;
			}

			$precio_base = floatval($json["products"][$i]['price']);
			$porcentaje_impuesto = ($precio_base * $impuestos) + $precio_base;
			$descuento = 0;
			$monto_descuento = 0;

			if ($json_descuentos != null) {
				
				if (array_key_exists("specific_prices", $json_descuentos)) {

					if (array_key_exists('specific_prices', $json_descuentos)) {
						
						$descuento = floatval($json_descuentos["specific_prices"][0]["reduction"]);

						if ($json_descuentos["specific_prices"][0]["reduction_type"] == 'percentage' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

							$monto_descuento = $porcentaje_impuesto * $descuento;
									
						} else if ($json_descuentos["specific_prices"][0]["reduction_type"] == 'amount' && $json_descuentos["specific_prices"][0]["id_customer"] == '0') {

							$monto_descuento = $descuento;

						}

					}

				} 

			}

			$precio = $porcentaje_impuesto - $monto_descuento;
			$json["products"][$i]["price"] = $precio;

		}

		return $json;

    }

}

/*$id = $request->id;
$id_customer = $request->id_customer;

try {

    $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);

} catch (PrestaShopWebserviceException $e) {

    echo 'Error:' . $e->getMessage();

}

$webService->delete([
        'resource' => 'carts',
        'id' => intval($id),
        'id_customer' => intval($id_customer),
    ]);

return ['Producto eliminado satisfactoriamente'];*/