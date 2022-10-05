<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Auth extends BD_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model('M_main');
    }

    

    public function login_post()
    {
        $u = $this->post('username'); //L'username est recupéré en variable
        $p = base64_encode($this->post('password')); //Le mot de passe est d'abord encrypté en base64 puis est récupéré en variable
        $q = array('username' => $u); //Pour les conditions 
        $kunci = $this->config->item('thekey');
        $invalidLogin = ['status' => 'Invalid Login']; //La réponse si le login est invalide
        $val = $this->M_main->get_user($q)->row(); //Model pour avoir une data de la BDD basée sur l'username
        if($this->M_main->get_user($q)->num_rows() == 0){$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);}
		$match = $val->password;   //Récupère le mot de passe avec la BDD
        if($p == $match){  //Condition : si le mot de passe est valide
        	$token['id'] = $val->id;  //D'ici
            $token['username'] = $u;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //A ici pour générer le token 
            $output['token'] = JWT::encode($token,$kunci ); //C'est le token de sortie
            $this->set_response($output, REST_Controller::HTTP_OK); //La réponse si tout est bon
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //La réponse s'il y a une erreur
        }
    }

}
