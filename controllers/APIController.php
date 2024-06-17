<?php

namespace Controllers;

use Model\Cita;
use Model\CitaServicio;
use Model\Servicio;

class APIController{
    public static function index(){
        //echo "desde api/index";
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar(){

        //Almacena la Cita y devuelve el ID
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();

        $id = $resultado['id'];

        //Almacena la Cita y el Servicio

        //Almacena los Servicios con ID de la Cita
        $idServicios = explode(",", $_POST['servicios']);  //picar el string servicios y lo conevertimos en un arreglo
        foreach($idServicios as $idServicio ){
            $args = [
                'citaId' => $id,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();

        }

        //Retornamos una respuesta
   
        echo json_encode(['resultado' => $resultado]);
    }

    public static function eliminar(){
        //echo "eliminando cita...";
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = $_POST['id'];
            $cita = Cita::find( $id );
            $cita->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
            //debuguear($cita);
        }
    }
}

