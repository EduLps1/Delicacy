<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/MenuModel.php';
require_once __DIR__ . '/../views/JsonView.php';

final class MenuController
{
    private MenuModel $model;

    public function __construct(?MenuModel $model = null)
    {
        $this->model = $model ?? new MenuModel();
    }

    public function menu(): void
    {
        JsonView::send([
            'categories' => $this->model->getCategories(),
            'categoryLabels' => $this->model->getCategoryLabels(),
            'dishes' => $this->model->getDishes(),
        ]);
    }

    public function health(): void
    {
        JsonView::send([
            'status' => 'ok',
            'service' => 'savour-stream-php',
        ]);
    }
}