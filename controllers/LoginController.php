<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController{
    public static function login(Router $router){

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();
            //debuguear($auth);

            if(empty($alertas)){
                //Comprobar que exista el usuario
                $usuario = Usuario::where('email', $auth->email);
                if($usuario){
                    //Verificar el password
                    if ($usuario->comprobarPasswordAndVerificado($auth->password)){
                        //Autenticar el usuario
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //Redireccionamiento

                        if($usuario->admin === "1"){
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        }else{
                            header('Location: /cita');
                        }

                        //debuguear($_SESSION);
                    }

                }else{
                    Usuario::setAlerta('error','Usuario no encontrado');
                }
            }

        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]);                              //va a la carpeta view que ya esta definida en el archivo Router.php
    }                                   // a traves de la funcion render que esta definida en ese archivo
                                        //y solo colocamos la carpeta en este caso auth y el archivo login.php sin la extension
    public static function logout(){

        //session_start();
        isSession();
        //debuguear($_SESSION);        
        $_SESSION = [];

        header('Location: /');

        //debuguear($_SESSION);

    }

    public static function olvide(Router $router){

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)){
                $usuario = Usuario::where('email', $auth->email);
                if($usuario && $usuario->confirmado === '1'){
                    //Generar un Token
                    $usuario->crearToken();
                    $usuario->guardar();

                    //Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito', 'Revisa tu correo') ;
                    
                    //debuguear($usuario);
                    //debuguear('Si Existe y esta confirmado');
                }else{
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                    
                    //debuguear('No Existe o no confirmado');
                }
            }

           // debuguear($auth);
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router){

        $alertas = [];

        $error = false;

        $token = s($_GET['token']);
        //Buscar usuario por su token
        $usuario = Usuario::where('token',$token);

        if(empty($usuario)){
            Usuario::setAlerta('error','Token No Valido');
            $error = true;
        }
        //debuguear($usuario);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //Leer el nuevo password y guardarlo

            $password = new Usuario($_POST);
            $alertas = $password ->validarPassword();

            if(empty($alertas)){
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
                debuguear($usuario);
            }
            //debuguear($password);
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error'=> $error
        ]);
    }

    public static function crear(Router $router){

        //$usuario = new Usuario($_POST);  //aqui estoy tomando los datos del formulario y los envio al objeto usuario
        $usuario = new Usuario;

        //Alertas vacias
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //echo "Enviaste el formulario";
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Revisar que alerta este vacio
            if(empty($alertas)){
                //Verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();
                //$usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                } else {
                    //Hashear el password
                    $usuario->hashPassword();
                    

                    //Generar un Token unico
                    $usuario->crearToken();

                    //Enviar el Email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);

                    $email->enviarConfirmacion();

                    //Crear el usuario
                    $resultado = $usuario->guardar();
                    if($resultado){
                        header('Location: /mensaje');
                    }

                    //debuguear($usuario);
                }
            }
            
            //debuguear($alertas);
            //debuguear($usuario);
        }

        $router->render('auth/crear-cuenta',[
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router){
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router){

        $alertas = [];

        $token = s($_GET['token']);

        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            //Mostrar mensaje de error
            Usuario::setAlerta('error', 'Token No Valido');
        }else{
            //Modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;
            //debuguear($usuario);
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }

        //debuguear($usuario);

        //Obtener alertas
        $alertas = Usuario::getAlertas();

        //Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

}