<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PrestaShopWebservice;

class BuscadorController extends Controller
{
    public function buscador(Request $request){

    	$busqueda = $request->busqueda;

    	$webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);

    	$buscar = [
		    'resource' => 'products',
		    'filter[name]' => '[' . $busqueda . ']%',
		    'display'  => '[name,id,id_category_default]',
		    'limit' => '3'
		];
 
    	$xml = $webService->get($buscar);

    	return [$xml];

    }

    public function buscador_estados(Request $request){

        //$busqueda = $request->busqueda;

        $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);

        $buscar = [
            'resource' => 'states',
            'filter[id_country]' => 6,
            'display'  => '[id,name]'
        ];
 
        $xml = $webService->get($buscar);

        return [$xml];

    }

    public function buscador_estado(Request $request){

        $busqueda = $request->id;

        $webService = new PrestaShopWebservice('https://www.wonduu.com', '4E5IDBTRSDFPGKEINT8T16Y5FMMT3CSP', false);

        $buscar = [
            'resource' => 'states',
            'filter[id]' => intval($busqueda),
            'display'  => '[id,name]'
        ];
 
        $xml = $webService->get($buscar);

        return [$xml];

    }

}
