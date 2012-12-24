Bank
====

Wrappers for accessing Swedish banks. Currently supports Handelsbanken and SEB.

Usage
====

Include KarlRoos/bank in your composer.json file for your project and install.

Create an instance of the bank you'd like to use like this:

    $seb = new \KarlRoos\Bank\SEB;

Login by calling the `login()` method:

    $seb->login($username, $password);

Fetch accounts by calling the `getAccounts()` method:

    $accounts = $seb->getAccounts();

This method returns an array with `\KarlRoos\Bank\Transaction` objects. To fetch transactions for a specific account you provide the Transaction object the `getTransactions()`:

    $transactions = $seb->getTransactions($accounts[0]);