<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;
use Ssheduardo\Redsys\Facades\Redsys;

class FacturacionController extends Controller
{
    public function orden_pago(Request $request){

      if ($request->id_carrito) {

        $curlx = curl_init();

        curl_setopt_array($curlx, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/carts?filter[id]=' . $request->id_carrito . '&display=full&output_format=JSON',
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

        $response = curl_exec($curlx);

        curl_close($curlx);

        $json_carts = json_decode($response, true);
        $carts_strings = null;
        $id_carrito = count($json_carts['carts']) - 1;

        if (array_key_exists('associations', $json_carts['carts'][$id_carrito])) {

          $cart = $json_carts['carts'][$id_carrito]['associations']['cart_rows'];

        }

        for ($y = 0; $y < count($cart); $y++) {

          $combinacion = null;

          if (array_key_exists('id_product_attribute', $cart[$y])) {
              
            $combinacion = $cart[$y]['id_product_attribute'];

          } else {

            $combinacion = "0";

          }

          $carts_strings .= '<order_row>
                                <product_id>' . $cart[$y]['id_product'] . '</product_id>
                                <quantity>' . $cart[$y]['quantity'] . '</quantity>
                                <product_attribute_id>' . $combinacion . '</product_attribute_id>
                              </order_row>';
      
        }

        $lang = '1';
        $c_rate = '1';
        $c_state = '75';
        $carrier = '28';
        $currency = '1';
        $modulo = 'ps_wirepayment';
        $total = strval($json_carts['carts'][$id_carrito]['order_total']);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/orders',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_POSTFIELDS => '<prestashop xmlns:xlink="http://www.w3.org/1999/xlink">
            <order>
              <id_address_delivery>'. $request->id_direccion .'</id_address_delivery>
              <id_address_invoice>'. $request->id_direccion .'</id_address_invoice>
              <id_cart>'. $request->id_carrito .'</id_cart>
              <id_currency>'. $currency .'</id_currency>
              <id_lang>'. $lang .'</id_lang>
              <id_customer>'.  $request->id_cliente .'</id_customer>
              <id_carrier>'. $carrier .'</id_carrier>
              <current_state>'. $c_state .'</current_state>
              <module>'. $modulo .'</module>
              <payment>'. $request->pago .'</payment>
              <total_paid>'. $total .'</total_paid>
              <conversion_rate>'. $c_rate .'</conversion_rate>
              <total_paid_tax_incl>'. $total .'</total_paid_tax_incl>
              <total_paid_tax_excl>'. $total .'</total_paid_tax_excl>
              <total_paid_real>'. $total .'</total_paid_real>
              <total_products>'. $total .'</total_products>
              <total_products_wt>'. $total .'</total_products_wt>
              <associations>
                <order_rows>' . $carts_strings . '</order_rows>
              </associations>
            </order>
          </prestashop>',
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/xml',
            'Authorization: Basic NEU1SURCVFJTREZQR0tFSU5UOFQxNlk1Rk1NVDNDU1A'
          ),
        ));

        $responsex = curl_exec($curl);

        curl_close($curl);

        try {
            
            $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
            $xml = $webService->get(['url' => 'https://www.wonduu.com/api/order_histories?schema=blank']);
            $xml_carts = $webService->get(['url' => 'https://www.wonduu.com/api/carts?schema=blank']);

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
        $id_orden = count($json['orders']) - 1;

        if (array_key_exists('orders', $json)) {
          
          $orden_id = $json['orders'][$id_orden]['id'];

          if ($orden_id) {

              $xml->order_history->id_order = $orden_id;
              $xml->order_history->id_order_state = '3';

              $createdXml = $webService->add([

                  'resource' => 'order_histories',
                  'postXml' => $xml->asXML(),

              ]);

              $xml_carts->cart->id_customer = $request->id_cliente;
              $xml_carts->cart->id_address_delivery = $request->id_direccion;
              $xml_carts->cart->id_address_invoice = $request->id_direccion;
              $xml_carts->cart->id_currency = 1;
              $xml_carts->cart->id_lang = 1;

              $createdXml = $webService->add([

                  'resource' => 'carts',
                  'postXml' => $xml_carts->asXML(),

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

    public function historial_pedidos(Request $request) {

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

    public function index(Request $request)
      {
          try{

              $key = config('redsys.key');

              Redsys::setAmount(rand($request->monto));
              Redsys::setOrder(time());
              Redsys::setMerchantcode('346311483'); //Reemplazar por el código que proporciona el banco
              Redsys::setCurrency('1');
              Redsys::setTransactiontype('0');
              Redsys::setTerminal('1');
              Redsys::setMethod('T'); //Solo pago con tarjeta, no mostramos iupay
              Redsys::setNotification(config('redsys.url_notification')); //Url de notificacion
              Redsys::setUrlOk(config('redsys.url_ok')); //Url OK
              Redsys::setUrlKo(config('redsys.url_ko')); //Url KO
              Redsys::setVersion('HMAC_SHA256_V1');
              Redsys::setTradeName('Wonduu');
              Redsys::setTitular('Grupo K2');
              Redsys::setProductDescription('Compras por aplicación');
              Redsys::setEnviroment('live'); //Entorno test

              $signature = Redsys::generateMerchantSignature($key);
              Redsys::setMerchantSignature($signature);

              $form = Redsys::createForm();
              $parameters = Redsys::getMerchantParameters($request->input('Ds_MerchantParameters'));
              $DsResponse = $parameters["Ds_Response"];
              $DsResponse += 0;

              if (Redsys::check($key, $request->input()) && $DsResponse <= 99) {

                  // lo que quieras que haya si es positiva la confirmación de redsys

                $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
                $id = $request->id;
                $xml = $webService->get([

                   'resource' => 'orders',
                   'id' => intval($id),

                ]);

                $xml->orders->children()->order->current_state = '2';
                $updatedXml = $webService->edit([
                  'resource' => 'orders',
                  'id' => $id,
                  'putXml' => $xml->asXML()
                ]);

                return ['Pago exitoso'];

              } else {

                return ['Error al procesar el pago'];
                  //lo que quieras que haga si no es positivo

              }
          }

          catch(Exception $e){

              echo $e->getMessage();

          }

          return $form;
      }

      public function pedidos_info(Request $request)
      {
          
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/orders?filter[id]=' . strval($request->id) . '&display=full&output_format=JSON',
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

        curl_close($curl);

        $json = json_decode($response, true);
        $direccion = $json["orders"][0]["id_address_delivery"];
        $id_estado = $json["orders"][0]["current_state"];
        $curl2 = curl_init();

        curl_setopt_array($curl2, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/addresses?filter[id]=' . $direccion . '&display=full&output_format=JSON',
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

        $response2 = curl_exec($curl2);

        curl_close($curl2);

        $json2 = json_decode($response2, true);
        $curl3 = curl_init();

        curl_setopt_array($curl3, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/order_states?filter[id]=' . $id_estado . '&display=full&output_format=JSON',
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

        $response3 = curl_exec($curl3);

        curl_close($curl3);

        $json3 = json_decode($response3, true);
        $total_envio = 4.10 * count($json["orders"][0]["associations"]["order_rows"]);

        return [$json, $json2, $json3, $total_envio];

      }

    public function repetir_pedido(Request $request)
    {

      $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
      $xml = $webService->get(['url' => 'https://www.wonduu.com/api/orders?schema=blank']);
     
      $id_carrito = $request->id;
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.wonduu.com/api/carts?filter[id]=' . $id_carrito . '&display=full&output_format=JSON',
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
        
      for ($y = 0; $y < count($response["carts"][0]["associations"]["cart_rows"]); $y++) { 
        
        $curl2 = curl_init();

        curl_setopt_array($curl2, array(
          CURLOPT_URL => 'https://www.wonduu.com/api/combinations?filter[id_product]=' . strval($response["carts"][0]["associations"]["cart_rows"][$y]["id_product"]) . '&display=full&output_format=JSON',
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

        for ($x = 0; $x < count($json2["combinations"]); $x++) { 

          if (floatval($json2["combinations"][$x]["quantity"]) < floatval($response["carts"][0]["associations"]["cart_rows"][$y]["quantity"])) {
              
            $curl_nombre = curl_init();

            curl_setopt_array($curl_nombre, array(
              CURLOPT_URL => 'https://www.wonduu.com/api/products?filter[id]=' . strval($response["carts"][0]["associations"]["cart_rows"][$y]["id_product"]) . '&display=[name]&output_format=JSON',
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

            $response_nombre = curl_exec($curl_nombre);
            $json_nombre = json_decode($response_nombre, true);

            curl_close($curl_nombre);

            return ['Cantidad del producto ' . $json_nombre["products"][0]["name"] . ' excedida, disponibles: ' . $json2["combinations"][$x]["quantity"]];

          } else {

            $xml->order->id_customer = $response["carts"][0]["id_customer"];
            $xml->order->id_address_delivery = $response["carts"][0]["id_address_delivery"];
            $xml->order->id_address_invoice = $response["carts"][0]["id_address_invoice"];
            $xml->order->id_currency = 1;
            $xml->order->id_lang = 1;

            for ($z = 0; $z < count($response["carts"][0]["associations"]["cart_rows"]); $z++) { 
              
              $xml->order->associations->order_row->cart_row[$z]->id_product = $response["carts"][0]["associations"]["cart_rows"][$z]["id_product"];
              $xml->order->associations->order_row->cart_row[$z]->quantity = $response["carts"][0]["associations"]["cart_rows"][$z]["quantity"];
              $xml->order->associations->order_row->cart_row[$z]->id_product_attribute = $json2["combinations"][$x]["id"];

            }

          }

        }

      }

      $createdXml = $webService->add([

         'resource' => 'orders',
         'postXml' => $xml->asXML(),

      ]);

      $newOrderFields = $createdXml->cart->children();
      $respuesta = 'Pedido efectuado satisfactoriamente';
      $newOrderFields = $newCartsFields->id;

      return [$respuesta, $newOrderFields];

    }

    public function total_orden(Request $request)
    {
      
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.wonduu.com/api/carts?filter[id_customer]=' . $request->id . '&display=full&output_format=JSON',
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

      $response = curl_exec($curl);
      $json = json_decode($response, true);
      $indice = count($json["carts"]) - 1;
      $envio = count($json["carts"][$indice]["associations"]["cart_rows"]) * 4.10;
      $total = floatval($envio) + floatval($json["carts"][$indice]["order_total"]);

      return [$total];

    }

}