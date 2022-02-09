<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Services\Gateway\Master\Bank;


interface BankServiceContract
{
    public function getLogoByName($bankName);
}