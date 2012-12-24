<?php
namespace KarlRoos\Bank;

/**
 * Interace for bank wrappers
 * @author Karl Laurentius Roos <karlroos93@gmail.com>
 */
interface BankInterface
{
    /**
     * Login
     * @param  string $username
     * @param  string $password
     * @return void
     */
    public function login($username, $password);

    /**
     * Get accounts
     * @return array
     */
    public function getAccounts();

    /**
     * Get transactions
     * @param  sring $account
     * @return array
     */
    public function getTransactions(Account $account);
}