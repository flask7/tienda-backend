<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class MensajesController extends Controller
{
    public function mensajes_productos(Request $request){

    	$datos_mensajes = ['cliente' => [], 'mensaje' => []];
    	$id = $request->id;
    	$url = 'https://www.wonduu.com/api/messages?filter[id]=' . $id . '&display=full&output_format=JSON';
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A=',
		    ),
		));

		$response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		if (array_key_exists('messages', $response)) {
		
			for ($i = 0; $i < count($response["messages"]); $i++) { 

				$cliente_id = $response["messages"][$i]["id_customer"];

				if ($cliente_id != "0" || $cliente_id != 0) {
					
					$url2 = 'https://www.wonduu.com/api/customers?filter[id]=' . $cliente_id . '&display=[firstname,lastname]&output_format=JSON';
				
					$curl2 = curl_init();

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => $url2,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A=',
					    ),
					));

					$response2 = json_decode(curl_exec($curl2), true);
					$nombre = $response2["customers"][0]["firstname"] . ' ' . $response2["customers"][0]["lastname"];

					array_push($datos_mensajes["cliente"], $nombre);
					array_push($datos_mensajes["mensaje"], $response["messages"][$i]["message"]);
					curl_close($curl2);

				}else{

					return ['Este producto no tiene comentarios'];

				}

			}

			return $datos_mensajes;

		}else{

			return ['Este producto no tiene comentarios'];

		}

    }

    public function mensajes_clientes(Request $request){

    	$datos_mensajes = ['vendedor' => [], 'mensaje' => []];
    	$id = $request->id;
    	$url = 'https://www.wonduu.com/api/customer_messages?filter[id]=' . strval($id) . '&display=full&output_format=JSON';
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A=',
		    ),
		));

		$response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		if (array_key_exists('customer_messages', $response)) {
		
			for ($i = 0; $i < count($response["customer_messages"]); $i++) { 

				$vendedor_id = $response["customer_messages"][$i]["id_employee"];

				if ($vendedor_id != "0" || $vendedor_id != 0) {
					
					$url2 = 'https://www.wonduu.com/api/employees?filter[id]=' . $vendedor_id . '&display=[firstname,lastname]&output_format=JSON';
				
					$curl2 = curl_init();

					curl_setopt_array($curl2, array(
					  CURLOPT_URL => $url2,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'GET',
					  CURLOPT_HTTPHEADER => array(
					    'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A=',
					    ),
					));

					$response2 = json_decode(curl_exec($curl2), true);
					$nombre = $response2["employees"][0]["firstname"] . ' ' . $response2["employees"][0]["lastname"];

					array_push($datos_mensajes["vendedor"], $nombre);
					array_push($datos_mensajes["mensaje"], $response["customer_messages"][$i]["message"]);
					curl_close($curl2);

				}else{

					return ['Aún no tienes comentarios'];

				}

			}

			return $datos_mensajes;

		}else{

			return ['Aún no tienes comentarios'];

		}

    }
}

