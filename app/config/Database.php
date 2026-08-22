<?php
class Database {
    private static ?PDO $instance = null;
    public static function connect(): PDO {
        if(self::$instance) return self::$instance;
        $dsn=sprintf('pgsql:host=%s;port=%s;dbname=%s',env('DB_HOST','127.0.0.1'),env('DB_PORT','5432'),env('DB_NAME'));
        try {
            self::$instance=new PDO($dsn,env('DB_USER'),env('DB_PASS'),[
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES=>false,
            ]);
        } catch(PDOException $e){ error_log('[DB] '.$e->getMessage()); http_response_code(500); die('DB Error'); }
        return self::$instance;
    }
    public static function query(string $sql, array $p=[]): PDOStatement { $s=self::connect()->prepare($sql); $s->execute($p); return $s; }
    public static function row(string $sql, array $p=[]): ?array { return self::query($sql,$p)->fetch()?:null; }
    public static function rows(string $sql, array $p=[]): array { return self::query($sql,$p)->fetchAll(); }
    public static function lastInsertId(): string { return self::connect()->lastInsertId(); }
}
