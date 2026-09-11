<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * ============================================================
 * HOMECONTROLLER -- exemplo minimo de um Controller.
 * ============================================================
 *
 * Este e o Controller mais simples possivel, so para voce ver a
 * estrutura basica. Sinta-se livre para APAGAR este arquivo (e a
 * rota '/' correspondente no public/index.php) assim que criar
 * o Controller de verdade da sua aplicacao.
 *
 * Todo Controller segue este molde:
 *   1) "extends Controller" (ganha view(), redirect(), input())
 *   2) Cada metodo publico corresponde a UMA rota cadastrada
 *   3) O metodo busca dados (via Model, se precisar) e chama
 *      $this->view('pasta/arquivo', ['variavel' => $dado])
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index');
    }
}
