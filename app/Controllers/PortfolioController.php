<?php

namespace App\Controllers;

use App\Redirect;
use App\Services\User\UserService;
use App\Services\UserCrypto\ShowUserCrypto;
use App\Validation;
use App\View;

class PortfolioController
{
    public function index(): View
    {
        //OWNED CRYPTO CURRENCIES
        $service = new UserService();
        $userOwnedCrypto = $service->getUserCrypto($_SESSION['auth_id']);

        $cryptoSymbols = [];
        foreach ($userOwnedCrypto as $cryptoSymbol) {
            $cryptoSymbols[] = $cryptoSymbol["crypto_name"];
        }

        if(count($cryptoSymbols) == 0 )
            return View::render('portfolio.twig',[]);

        // ALL NEW INFO ABOUT OWNED CRYPTO CURRENCIES
        $service = new ShowUserCrypto();
        $userCrypto = $service->execute($cryptoSymbols);
        $portfolio = $userCrypto->all();

        //UPDATE CRYPTO_PRICE
        $service = new UserService();
        $service->updatePrice($portfolio);

        //SUM OF OWNED CRYPTO
        $sum = 0;
        foreach ($userOwnedCrypto as $coin){
            $sum += $coin['current_price']*$coin["crypto_count"];
        }

        return View::render('portfolio.twig',   [
            'owned' => $userOwnedCrypto,
            'moneyInCrypto' => $sum,
        ]);
    }

    public function sendCrypto(): Redirect
    {
        $validation = new Validation();
         $validation->sendCrypto($_POST['symbol'],$_POST['amount'],$_POST['password'],$_POST['email']);
        if ($validation->validationFailed()) {
            return new Redirect('/portfolio');
        }
        (new UserService())->sendCrypto($_POST['symbol'],$_POST['amount'],$_POST['email']);
        return new Redirect('/portfolio');
    }

}