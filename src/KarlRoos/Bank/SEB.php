<?php
namespace KarlRoos\Bank;

/**
 * Class for accessing SEB
 * @author Karl Laurentius Roos <karlroos93@gmail.com>
 */
class SEB implements BankInterface
{
    /**
     * User agent
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.101 Safari/537.11';

    /**
     * Cookies
     * @var array
     */
    protected $cookies = [];

    /**
     * Format amount
     * @param  string $amount
     * @return float
     */
    protected function formatAmount($amount)
    {
        $amount = trim($amount);
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '.', $amount);

        if (substr($amount, 0, 1) == '-') {
            return - (float) substr($amount, 1);
        }
    }

    /**
     * Request
     * @param  string $url
     * @param  array $params
     * @return string
     */
    public function request($url, $params)
    {
        extract(array_merge([
            'header' => true,
            'content' => true,
            'post' => false,
            'data' => false,
            'headers' => [],
        ], $params));

        $headers[] = 'User-Agent: ' . $this->userAgent;

        if (!empty($this->cookies)) {
            $headers[] = 'Cookie: ' . implode('; ', $this->cookies);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($header) {
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
        }

        if ($content) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        }

        if ($post) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';

            curl_setopt($curl, CURLOPT_POST, 1);

            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($curl);

        if ($header) {
            list($headers, $content) = preg_split('/<!DOCTYPE[^>]+>/', $result);
            $headers = explode("\n", trim($headers));
            $content = trim($content);

            foreach($headers as $header) {
                if (strrpos($header, ':') == false) {
                    continue;
                }

                list($key, $val) = explode(': ', $header);

                if ($key == 'Set-Cookie') {
                    $val = explode(';', $val);
                    $this->cookies[] = $val[0];
                }
            }

            return ['header' => $headers, 'content' => $content];
        }

        return $result;
    }

    /**
     * Login
     * @param  string $username
     * @param  string $password
     * @return bool
     */
    public function login($username, $password)
    {
        $output = $this->request('https://m.seb.se/cgi-bin/pts3/mps/1000/mps1001bm.aspx', [
            'header' => true,
            'content' => true,
            'post' => true,
            'data' => 'A3=4&A1=' . $username . '&A2=' . $password
        ]);

        if (!empty($this->cookies)) {
            return true;
        }

        return false;
    }

    /**
     * Get accounts
     * @return array
     */
    public function getAccounts()
    {
        $output = $this->request('https://m.seb.se/cgi-bin/pts3/mps/1100/mps1101.aspx?X1=digipassAppl1', [
            'content' => true
        ]);

        preg_match('/nodivider\">(.*<\/tr>.*)<\/tr>/s', $output['content'], $matches);
        $rows = explode('<td class="name" colspan="3">', $matches[1]);
        unset($rows[0]);

        foreach ($rows as $row) {
            preg_match('/href="([^"]+)/', $row, $href);
            preg_match('/>([^<]+)/', $row, $name);
            preg_match('/<td class="numeric">([^<]+)</', $row, $amount);

            $identifier = str_replace('&amp;', '&', $href[1]);
            $name = $name[1];
            $amount = $this->formatAmount($amount[1]);

            $accounts[] = new Account($identifier, $name, $amount);
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
        $output = $this->request('https://m.seb.se' . $account->getIdentifier(), [
            'content' => true
        ]);

        $transactions = [];

        preg_match('/<\/thead>(.*)<\/table>/s', $output['content'], $table);
        preg_match_all('/<tr[^>]+>(.*)<\/tr>/s', $table[1], $rows);
        $rows = explode('</tr>', $rows[0][0]);

        foreach ($rows as $row) {
            $row = trim($row);

            if (empty($row)) {
                continue;
            }

            preg_match('/<td>(.*)<br/s', $row, $date);
            preg_match('/class="name">([^<]+)/s', $row, $name);
            preg_match('/class="value">([^<]+)/s', $row, $amount);

            $date = trim($date[1]);
            $name = $name[1];
            $amount = $this->formatAmount($amount[1]);

            $name = explode('/', $name);

            if (count($name) == 2) {
                $date = '20' . $name[1];
                $name = $name[0];
            }
            else {
                $name = $name[0];
                $date = '20' . substr($date, 0, 2) . '-' . substr($date, 2, 2) . '-' . substr($date, 4, 2);
            }

            $transactions[] = new Transaction($name, $amount, $date);
        }

        return $transactions;
    }
}