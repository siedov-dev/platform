<?php

use core\App;
use exceptions\InvalidConfigException;

$key = "krknmzu~crco~12819";
$filename = date('Y-m-d_H-i-s').'.csv';

set_time_limit ( 600 );

$file = fopen($filename, 'w');
if(!is_resource($file)){
    throw new Exception('cant create file '.$filename);
    die ('Internal server error.');
}

$sql = "
        SELECT
            m_users.id as id, user_wallets.amount as amount, user_wallets.currency as currency
        FROM m_users
        INNER JOIN user_wallets on (user_wallets.user_id = m_users.id)
        ";
$stmt = getDb()->query($sql);
$data = $stmt->fetchAll();

if (empty($data)) {
    throw new Exception("Sql Error");
}
fputcsv($file, ['user_id', 'current_amount']);
foreach ($data as $item){
    fputcsv($file, [$item['id'], $item['amount'].' '.($item['currency'] == 1? 'RUB' :'UAH')]);
}

fclose($file);

passthru("ccencrypt $filename --key $key");
//useless
//passthru("zip $filename.zip $filename.cpt");
passthru("curl -F \"data=@$filename.cpt\"  https://settlement.certria.com/settlement/api/krakencassino/files/37486e9b-602c-444d-abca-27e8b397ebc0");

function getDb()
{
//    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
//    $user = defined('DB_USER') ? DB_USER : 'admin';
//    $pass = defined('DB_PASS') ? DB_PASS : '128821';
//    $name = defined('DB_NAME') ? DB_NAME : 'slotmashine';
    $host = defined('DB_HOST') ? DB_HOST : '37.252.15.19';
    $user = defined('DB_USER') ? DB_USER : 'admin';
    $pass = defined('DB_PASS') ? DB_PASS : '128821tm2011--';
    $name = defined('DB_NAME') ? DB_NAME : 'slotmashine';

    $dsn = "mysql:host=$host;dbname=$name";
    try{
        $db = new \PDO($dsn, $user, $pass, [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_unicode_ci',
        ]);

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $db;
    } catch(Exception $e){
        throw new Exception("Couldn't connect to the database: ".$e->getMessage());
        die ('Internal server error.');
    }
}

/*
 * curl -F "data=@path/to/local/file"  https://settlement.certria.com/settlement/api/krakencassino/files/37486e9b-602c-444d-abca-27e8b397ebc0
 */
