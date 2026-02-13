<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Gestão de Tarefas por Times - API",
 *     version="1.0.0",
 *     description="API para gerenciamento de tarefas organizadas por times"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 *
 * @OA\Get(
 *     path="/health",
 *     summary="Health check",
 *     @OA\Response(response=200, description="OK")
 * )
 */
abstract class Controller
{
    //
}
