<?php


require_once __DIR__ . '/../models/ReservaModel.php';

class ReservaController
{

    private $model;

    public function __construct()
    {
        $this->model = new ReservaModel();
    }

    public function criar()
    {
        $this->model->criar($_POST);
        header("Location: /?msg=criado");
    }

    public function listar()
    {
        return $this->model->listar();
    }

    public function cancelar()
    {
        $this->model->cancelar($_GET['id']);
        header("Location: /?msg=cancelado");
    }
}
