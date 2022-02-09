<?php
/**
 * @author Jehan Afwazi Ahmad <jehan@ontelstudio.com>.
 *
 * Rest Routes
 * @param $path
 * @param $controller
 * @param null $app
 */

function rest($path, $controller, $app = null)
{
    // global $app;

    $app->get($path, ['uses' => $controller . '@fetch']);
    $app->get($path.'/list', ['uses' => $controller . '@list']);
    $app->get($path . '/create', ['uses' => $controller . '@create']);
    $app->get($path . '/{id}', ['uses' => $controller . '@detail']);
    $app->get($path . '/{id}/edit', ['uses' => $controller . '@edit']);
    $app->post($path, ['uses' => $controller . '@store']);
    $app->post($path . '/{id}/update', ['uses' => $controller . '@update']);
    $app->post($path . '/mark_as/{status}', ['uses' => $controller . '@markAs']);
    $app->delete($path, ['uses' => $controller . '@destroy']);
}