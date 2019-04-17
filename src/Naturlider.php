<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;

use Goutte\Client as GoutteClient;


class Naturldider {
	private $usuario;
	private $pass;
	private $client;

	function __construct(){
		$this->client = new GoutteClient();
		$this->client->setClient(
			new GuzzleClient([
				'timeout'	=> 10.0,
				'cookies' => true,
			])
		);
	}
	
	function setCreedentials($usuario,$pass){
		$this->usuario=$usuario;
		$this->pass=$pass;
	}
	
	function checkCreedentials(){
		if(!isset($this->usuario) || !isset($this->pass)){
			throw new Exception("Need authenticate by Naturlider->login(\$user,\$pass)");
		}
	}

	public function login(){
		$this->checkCreedentials();
		$crawler=$this->client->request('GET', 'http://tienda.naturlider.com/Conectar.aspx');
		$form=$crawler->filter('[name="ctl00$Contenido$btnConectar"]')->form();
		$form->setValues([
			"ctl00\$Contenido\$tbUsuario"=>$this->usuario,
			"ctl00\$Contenido\$tbPassword"=>$this->pass,
		]);
		$this->client->submit($form);
		echo "logeado correctamente\n";
	}
	
	public function getProductoByCodigo(int $code){
		$url="http://tienda.naturlider.com/ajax.aspx/GetProductoByCodigo";
		$data=json_encode(["idProducto"=>str_pad($code, 6, "0", STR_PAD_LEFT)]);
		
		$cookies = CookieJar::fromArray(
			$this->client->getCookieJar()->allRawValues($url),
			parse_url($url, PHP_URL_HOST)
		);
		
		$res=$this->client->getClient()->request("POST",$url,[
			"headers"=>[
				"content-type"=>"application/json"
			],
			"body"=>$data,
			"cookies"=>$cookies,
		]);
		
		return json_decode(json_decode($res->getBody())->d);
	}

}

?>