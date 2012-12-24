<?php
namespace KarlRoos\Bank;

/**
 * Class for accessing Handelsbanken
 * @author Karl Laurentius Roos <karlroos93@gmail.com>
 */
class Handelsbanken implements BankInterface
{
    /**
     * URL
     * @var string
     */
    protected $url = 'https://m.handelsbanken.se/app//';

    /**
     * Call
     * @param  string $type
     * @param  string $method
     * @param  array  $params
     * @param  array  $additional_headers
     * @return array
     */
    public function call($type, $method, $params = array(), $additional_headers = array())
    {
        $headers = array(
            'User-Agent: Mobilbank/1.2 CFNetwork/548.0.3 Darwin/11.0.0',
            'X-SHB-DEVICE-MODEL: IOS-5.0,1.2,iPhone3.1',
            'X-SHB-DEVICE-CLASS: APP',
            'X-SHB-APP-VERSION: 1.0'
        );

        if ($type == 'POST') {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        foreach ($additional_headers as $header) {
            $headers[] = $header;
        }

        $query_string = '';

        if ($type == 'GET' && count($params) !== 0) {
            $query_string = '?' . http_build_query($params);
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url . $method . $query_string);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($type == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);

            if (count($params) !== 0) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        list($header, $body) = explode("\r\n\r\n", $data, 2);

        return array(
            'header' => $header,
            'xml' => simplexml_load_string($body)
        );
    }

    /**
     * Login
     * @param  string $username
     * @param  string $pin
     * @return void
     */
    public function login($username, $pin)
    {
        extract($this->call('POST', 'login', array(
            'username' => $username,
            'pin' => $pin,
            'deviceid' => 'f8280cf34708c7b5a8bd2ed93dcd3c814800000'
        )));

        $header = explode('Set-Cookie: ', $header);
        $header = explode(';', $header[1]);
        $cookie = $header[0];

        if(isset($xml->authToken)){
            $this->auth = (object) array(
                'token' => (string) $xml->authToken,
                'cookie' => $cookie
            );
        }
    }

    /**
     * Logout
     * @return void
     */
    public function logout()
    {
       $this->call('GET', 'logout', array(
            'authToken' => $this->auth->token
        ), array(
            'Cookie: ' . $this->auth->cookie
        ));
    }

    /**
     * Get accounts
     * @return array
     */
    public function getAccounts()
    {
        extract($this->call('GET', 'accounts', array(
            'authToken' => $this->auth->token
        ), array(
            'Cookie: ' . $this->auth->cookie
        )));

        $accounts = array();

        foreach ($xml->accounts->account as $account) {
            $amount = $account->accountAmount;
            $amount = str_replace(',', '.', $amount);
            $amount = str_replace(' ', '', $amount);

            $balance = $account->accountBalance;
            $balance = str_replace(',', '.', $balance);
            $balance = str_replace(' ', '', $balance);

            $accounts[] = new Account((int) $account->accountId, utf8_decode(utf8_encode($account->accountName)), (float) $amount);
        }

        return $accounts;
    }

    /**
     * Get transactions
     * @param  Account $account
     * @return array
     */
    public function getTransactions(Account $account)
    {
        extract($this->call('GET', 'transactions', array(
            'authToken' => $this->auth->token,
            'type' => 1,
            'account' => $account->getIdentifier(),
            'accountType' => 1
        ), array(
            'Cookie: ' . $this->auth->cookie
        )));

        $transactions = array();

        foreach ($xml->transactions->transaction as $transaction) {
            $amount = $transaction->transactionAmount;
            $amount = str_replace(',', '.', $amount);
            $amount = str_replace(' ', '', $amount);

            $name = utf8_decode(utf8_encode($transaction->transactionDescription));
            $amount = (float) $amount;
            $date = (string) $transaction->transactionDate;

            $transactions[] = new Transaction($name, $amount, $date);
        }

        return $transactions;
    }
}