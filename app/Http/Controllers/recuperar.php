<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\recuperarv;
use Illuminate\Support\Facades\Mail;

use PrestaShopWebservice;

class recuperar extends Controller
{

    public function recuperar(Request $request) {

    	$correo = $request->correo;
    	$codigo = rand(1000, 9999);

    	Mail::to($correo)->send(new recuperarv($codigo));

    	return [strval($codigo), $correo];

    }

    public function recuperar2(Request $request) {
    	
    	try {
		    
		    $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);
		 
		    $xml = $webService->get([
		        'resource' => 'customers',
		        'email' => $request->correo,
		    ]);

		} catch (PrestaShopWebserviceException $ex) {
		  
		   return ['Other error: <br />' . $ex->getMessage()];
		}

		$customerFields = $xml->customer->children();
		$customerFields->passwd = $request->password;

		$updatedXml = $webService->edit([
		    'resource' => 'customers',
		    'id' => (int) $customerFields->id,
		    'putXml' => $xml->asXML(),
		]);

		$customerFields = $updatedXml->customer->children();

		return ['Cambio de clave exitoso'];
    	
    }

}
