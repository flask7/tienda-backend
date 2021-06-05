<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class carrito extends Controller
{
    public function add_carrito(Request $request){

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
				$id_carrito = count($json['carts']) - 1;

				curl_close($curl);

				if (array_key_exists("carts", $json)) {

			        if ($request->opciones) {
			        
			        	if (count($request->opciones) > 0) {

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

							if (floatval($cantidad_comb) < floatval($request->quantity)) {
													
								return ['Cantidad del producto ' . $request->nombre . ' excedida, disponibles: ' . $cantidad_comb];

							}

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

						    return ['Producto añadido satisfactoriamente'];

				        }

			        }

				} else {

			       	if ($request->opciones) {
			        
			        	if (count($request->opciones) > 0) {

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

							curl_close($curl2);

							for ($i = 0; $i < count($json2["combinations"]); $i++) { 

								if (count($json2["combinations"][$i]["associations"]["product_option_values"]) == count($request->opciones)) {
									
									for ($a = 0; $a < count($request->opciones); $a++) { 
									
										$coincidencias = 0;

										for ($x = 0; $x < count($json2["combinations"][$i]["associations"]["product_option_values"]); $x++) { 

											if ($json2["combinations"][$i]["associations"]["product_option_values"][$x]["id"] == $request->opciones[$a]) {

												$coincidencias++;

											}

											if ($coincidencias == (count($json2["combinations"][$i]["associations"]["product_option_values"][$x]) - 1)) {

												if (floatval($json2["combinations"][$i]["quantity"]) < floatval($request->quantity)) {
													
													return ['Cantidad del producto ' . $request->nombre . ' excedida, disponibles: ' . $json2["combinations"][$i]["quantity"]];

												} else {

													$xml->cart->id_customer = $request->id_customer;
											        $xml->cart->id_address_delivery = $request->direccion;
											        $xml->cart->id_address_invoice = $request->direccion;
											        $xml->cart->id_currency = 1;
											        $xml->cart->id_lang = 1;
											        $xml->cart->associations->cart_rows->cart_row->id_product = $request->id;
											        $xml->cart->associations->cart_rows->cart_row->quantity = $request->quantity;
													$xml->cart->associations->cart_rows->cart_row->id_product_attribute = $json2["combinations"][$i]["id"];

												}

												break;

											}

										}

									}

								}

							}

			        	}

			        	$createdXml = $webService->add([

						   'resource' => 'carts',
						   'postXml' => $xml->asXML(),

						]);

				        $newCartsFields = $createdXml->cart->children();
						$respuesta = 'Producto añadido satisfactoriamente';
						$newCartsFields = $newCartsFields->id;

						return [$respuesta, $newCartsFields];

					}

				}

			} catch (PrestaShopWebserviceException $ex) {
				  
				echo 'Error: ' . $ex->getMessage();

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
			
			if ($y == intval($request->id) && $contador < 1) {
				
				$contador++;
				unset($new_row->cart->associations->cart_rows->cart_row[$y]);
	
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

    }

    public function get_carrito(Request $request){

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
		$id_carrito = count($response['carts']) - 1;

		curl_close($curl);

		if ($response != null) {

			if (array_key_exists("carts", $response)) {

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
					$filtro = $ids[0] . ',' . ($ids[count($ids) - 1]);

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id]=[' . strval($filtro) . ']&display=[id,name]&output_format=JSON',
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

					return [$response, $response2];

				} else {

					return [];

				}

			}else{

				return [];

			}
			
		}else{

			return [];

		}

    }

} 