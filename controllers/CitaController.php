<?php

namespace Controllers;

use MVC\Router;

class CitaController {
    public static function index(Router $router) {

        
            //session_start();
            isSession();

            isAuth();  //Funcion que verifica si el usuario esta autenticado, sino lo manda al login


        $router->render('cita/index', [
            'nombre' => $_SESSION['nombre'],
            'id' => $_SESSION['id']
        ]);
    }

}