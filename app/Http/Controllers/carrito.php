<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class carrito extends Controller
{
    public function add_carrito(Request $request){

    	//return [$request->quantity];

		if (!$request->id || !$request->quantity) {

			return ['Error en envío de los datos'];

		}else{

			try {
			    
				$webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
				$xml = $webService->get(['url' => 'https://www.wonduu.com/api/carts?schema=blank']);

			} catch (PrestaShopWebserviceException $ex) {

				  
				echo 'Error: ' . $ex->getMessage();

			}

			$xml->cart->id_customer = $request->id_customer;
	        $xml->cart->id_address_delivery = $request->direccion;
	        $xml->cart->id_address_invoice = $request->direccion;
	        $xml->cart->id_currency = 1;
	        $xml->cart->id_lang = 1;
	        $xml->cart->associations->cart_rows->cart_row->id_product = $request->id;
	        $xml->cart->associations->cart_rows->cart_row->quantity = $request->quantity;

	        if ($request->opciones) {
	        
	        	if (count($request->opciones) > 0) {

		        	for ($i = 0; $i < count($request->opciones); $i++) { 

		        		$xml->cart->associations->cart_rows->cart_row->id_customization = $request->opciones[$i];

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

    public function eliminar_carrito(Request $request){
    	
    	$id = $request->id;
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

		return ['Producto eliminado satisfactoriamente'];

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

		curl_close($curl);

		if ($response != null) {

			if (array_key_exists("carts", $response)) {

				for ($i = 0; $i < count($response["carts"]); $i++) { 
					
					if (array_key_exists('associations', $response['carts'][$i])) {
						
						array_push($ids, $response['carts'][$i]['associations']['cart_rows'][0]['id_product']);
						
					}

				}

				sort($ids);

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

			}else{

				return [];

			}
			
		}else{

			return [];

		}

    }

}
