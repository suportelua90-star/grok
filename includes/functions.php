<?php
$dbPath = __DIR__ . '/../ibo_panel.db';
$db = new SQLiteWrapper($dbPath);

function sanitize($data) {
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES);
    $data = SQLite3::escapeString($data);
    return $data;
}

function formatMacAddress($mac, $doubleDecode = false, $removeSubstr = null) {
    if ($doubleDecode) {
        $mac = base64_decode(base64_decode($mac, true), true);
    } else {
        $mac = base64_decode($mac, true);
    }

    if (!is_string($mac)) $mac = "";

    if ($removeSubstr) {
        $mac = str_replace($removeSubstr, "", $mac);
    }

    $mac = substr($mac, 0, 12);
    return strtoupper(preg_replace('/..(?!$)/', '$0:', $mac));
}

const ALLOWED_CHARACTERS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

/**
 * Seu decoder atual (mantido), só com guardas pra não retornar NULL/false quebrando json_decode.
 */
function getDecodedString($str) {
    if (!is_string($str) || $str === "") return "";

    $k1 = substr($str, -2, 1);
    $k2 = substr($str, -1);

    $encryptKeyPosition  = strpos(ALLOWED_CHARACTERS, $k1);
    $encryptKeyPosition2 = strpos(ALLOWED_CHARACTERS, $k2);

    if ($encryptKeyPosition === false || $encryptKeyPosition2 === false) {
        return "";
    }

    $substring = substr($str, 0, -2);
    $part = substr($substring, 0, $encryptKeyPosition) . substr($substring, $encryptKeyPosition + $encryptKeyPosition2);

    $b64 = base64_decode($part, true);
    if (!is_string($b64) || $b64 === "") return "";

    // Mantém seu comportamento original (utf8_decode)
    return trim(utf8_decode($b64));
}

function getEncodedString($str) {
    $encryptKeyPosition = strpos(ALLOWED_CHARACTERS, substr($str, -2, 1));
    $encryptKeyPosition2 = strpos(ALLOWED_CHARACTERS, substr($str, -1));
    $encodedString = base64_encode(utf8_encode($str));
    $substring = substr($encodedString, 0, $encryptKeyPosition) . substr($encodedString, $encryptKeyPosition + $encryptKeyPosition2);
    return $substring . substr(ALLOWED_CHARACTERS, $encryptKeyPosition, 1) . substr(ALLOWED_CHARACTERS, $encryptKeyPosition2, 1);
}

class Encryption {
    private static $keySets = [
        "IBO_38" => ["FIJo0GopkIRAPjbR", "RbjPARIkpoG0oJIF"],
    ];

    public static function encrypt($i, $key) {
        $encrypted = openssl_encrypt($i, 'AES-128-ECB', ($key), 0);
        $length = strlen($encrypted);
        return array($encrypted, $length);
    }

    public static function run($i, $name) {
        $keys = self::$keySets[$name];
        $key1 = $keys[0];
        $key2 = $keys[1];
        $substring = rand(10, strlen($i) - 20);
        $ep1 = self::encrypt(substr($i, 0, $substring), $key1);
        $ep2 = self::encrypt(substr($i, $substring), $key2);
        $encoded = base64_encode($ep1[0] . $ep2[0] . '!' . $ep1[1]);
        return '{"data":"' . $encoded . '"}';
    }
}

class SQLiteWrapper {
    private $db;

    public function __construct($dbLoc) {
        try {
            $this->db = new SQLite3($dbLoc);
        } catch (Exception $e) {
            $this->db = new SQLite3('/../ibo_panel.db');
        }
        if (!$this->db) {
            die("Error: Unable to open database.");
        }
    }

    public function select($tableName, $columns = "*", $where = "", $orderBy = "", $placeholders = array()) {
        $query = "SELECT $columns FROM $tableName";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        if (!empty($orderBy)) {
            $query .= " ORDER BY $orderBy";
        }

        $stmt = $this->db->prepare($query);

        foreach ($placeholders as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $result = $stmt->execute();

        $data = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function insert($tableName, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    public function update($tableName, $data, $where = "", $placeholders = array()) {
        $setValues = [];
        foreach ($data as $column => $value) {
            $setValues[] = "$column = :$column";
        }
        $setClause = implode(', ', $setValues);
        $query = "UPDATE $tableName SET $setClause";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }

        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        foreach ($placeholders as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function delete($tableName, $where = "", $placeholders = array()) {
        $query = "DELETE FROM $tableName";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }

        $stmt = $this->db->prepare($query);

        foreach ($placeholders as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function getLastInsertId() {
        return $this->db->lastInsertRowID();
    }

    public function close() {
        $this->db->close();
    }
}
