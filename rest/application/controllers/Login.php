<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;
//esta libreria REST_Controller es lo que hemos añadido en los recursos del curso de chris y esta en las librerias
class Login extends REST_Controller {
  //para optimizar código este va a ser el constructor que haga funciones principales como inicializar la base de datos.
  public function __construct(){
    //todo esto es para el access controll allow y tal
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");
    parent::__construct();//esto es para ejecutar el constructor del padre
    $this->load->database();//para conectarse ala base de datos
  }


  public function index_post(){
    //para recibir toda la info del post no la pillamos por los parametros como un get
    //creamos una variable como el data de abajo y lo de detras.
    $data = $this->post();
    //ahora comprobamos que en la data que nos mandan exista el correo o la contraseña
    if ( !isset($data['correo']) OR !isset($data['contrasena'])) {
      $respuesta = array(
        'error' => TRUE,
        'mensaje'=>'la información enviada no es válida'
      );
      $this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    //si no entra en el if tenemos correo y contraseña en un post
    //ahora construiremos unas condiciones para hacer el query
    $condiciones = array('correo'=>$data['correo'],'contrasena'=>$data['contrasena']);
    $query = $this->db->get_where('login',$condiciones);
    //ahora creamos una variable usuario con el resultado de esa query
    $usuario = $query->row();
    // si no hay match con las condiciones el usuario estara vacio. vamos a comprobarlo
    if ( !isset($usuario)) {
      $respuesta = array(
        'error' => TRUE,
        'mensaje'=>'Usuario y/o contrasena no son validos'
      );
      $this->response($respuesta);
      return;
    }
    //aqui tenemos un correo y contrasena validos y vamos a generar un TOKEN
    //formas de generar un token en php
    //Si necesitamos un token aleatorio, esta genrea un numero aleatorio hexadecimal de 20 numeros.
    $token = bin2hex(openssl_random_pseudo_bytes(20));
    //Si necesitamos siempre el mismo token, la otra es hacer un hash del correo que el correo es unico.
    $token = hash('ripemd160',$data['correo']);
    //ahora guardamos en la base de datos el token. Lo usaremos cada vez que el usuario se loguee y tal
    $this->db->reset_query();//reseteamos el query para volver a empezar. llamada nueva.
    $actualizar_token = array('token'=>$token);//insertamos el token
    $this->db->where( 'id' , $usuario->id );//donde el usuario  tiene el id que nos ha devuelto la consulta
    $hecho = $this->db->update('login', $actualizar_token);//aqui guardo lo que hice. actualizar la tabla login

    $respuesta = array(
          'error'=>FALSE,
          'token'=>$token,//para guardarlo en el localstorage y hacer peticiones
          'id_usuario'=>$usuario->id
        );

    $this->response($respuesta);
  }
}
