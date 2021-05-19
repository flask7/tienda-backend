<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class FacturacionController extends Controller
{
    public function orden_pago(Request $request){

      if ($request->id_carrito) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/orders',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => '<?xml version="1.0" encoding="UTF-8"?>
            <prestashop xmlns:xlink="http://www.w3.org/1999/xlink">
                <order>
                    <id_address_delivery>' . strval($request->id_direccion) . '</id_address_delivery>
                    <id_address_invoice>' . strval($request->id_direccion) . '</id_address_invoice>
                    <id_cart>' . strval($request->id_carrito) . '</id_cart>
                    <id_currency>1</id_currency>
                    <id_lang>1</id_lang>
                    <id_customer>' . strval($request->id_cliente) . '</id_customer>
                    <id_carrier>3</id_carrier>
                    <current_state>' . strval($request->id_estado) . '</current_state>
                    <module>' . 'ps_wirepayment' . '</module>
                    <payment>' . strval($request->pago) . '</payment>
                    <total_paid>' . strval($request->total) . '</total_paid>
                    <conversion_rate>1</conversion_rate>
                    <total_paid_tax_incl>' . strval($request->total) . '</total_paid_tax_incl>
                    <total_paid_tax_excl>' . strval($request->total) . '</total_paid_tax_excl>
                    <total_paid_real>' . strval($request->total) . '</total_paid_real>
                    <total_products>' . strval($request->total) . '</total_products>
                    <total_products_wt>' . strval($request->total) . '</total_products_wt>
                    <associations>
                        <order_rows nodeType="order_row" virtualEntity="true">
                            <order_row>
                                <product_id>' . strval($request->product_id) . '</product_id>
                                <product_quantity>' . strval($request->cantidad) . '</product_quantity>
                            </order_row>
                        </order_rows>
                    </associations>
                </order>
            </prestashop>',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml',
            'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        try {
            
            $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
            $xml = $webService->get(['url' => 'https://www.wonduu.com/api/order_histories?schema=blank']);

        } catch (PrestaShopWebserviceException $ex) {

              
            echo 'Other error: ' . $ex->getMessage();

        }
 
        $curl2 = curl_init();

        curl_setopt_array($curl2, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/orders?filter[id_cart]=' . strval($request->id_carrito) . '&display=[id]&output_format=JSON',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml',
            'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A'
          ),
        ));

        $response2 = curl_exec($curl2);

        curl_close($curl2);

        $json = json_decode($response2, true);

        if (array_key_exists('orders', $json)) {
          
          $orden_id = $json['orders'][0]['id'];

          if ($orden_id) {

              $xml->order_history->id_order = $orden_id;
              $xml->order_history->id_order_state = '3';

              $createdXml = $webService->add([

                  'resource' => 'order_histories',
                  'postXml' => $xml->asXML(),

              ]);

              return ['Pedido realizado satisfactoriamente'];

          }else{

              return ['Error creando el pedido'];

          }

        }else{

          return ['Error al crear el pedido'];

        }
        
      }else{

        return [$request->id_carrito];

      }

    }

    public function historial_pedidos(Request $request){

      $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/orders?filter[id_customer]=' . strval($request->id) . '&display=[id,reference,payment,total_paid]&output_format=JSON',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml',
            'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $json = json_decode($response, true);

        return $json;

    }

}